"use strict";


FeedBox.Module.Item = new Nerve.Module({
    Model: Backbone.Model.extend({
        defaults: {
            folded: true
        },
        initialize: function() {
            this.settings = FeedBox.Session.get('setting-collection');
        },
        parse: function (response, options) {
            var sources = FeedBox.Session.get('source-collection');
            if (sources) {
                if (response.source_id) {
                    response.source = sources.get(response.source_id);
                }
            }
            return response;
        },
        read: function(read) {
            var sources = FeedBox.Session.get('source-collection'),
                source = sources.get(this.get('source_id')),
                unread = source.get('unread');

            if (read) {
                this.save({ 'read': moment().format('YYYY-MM-DD HH:mm:ss') });
                unread--;
            } else {
                this.save({ 'read': null });
                unread++;
            }

            if (unread >= 0) {
                source.set('unread', unread);
            }

            return this;
        },
        timeHumanize: function () {
            var date = moment(this.get('pubdate'));
            if (date.isSame(moment(), 'day')) {
                return date.format(this.settings.getByKey('format.time', 'HH:mm:ss'));
            } else {
                return date.format(this.settings.getByKey('format.datetime', 'YYYY-MM-DD HH:mm'));
            }
        }
    }),
    Views: {
        Item: FeedBox.Views.ListItem.extend({
            events: {
                'click .foldable': 'showDetails',
                'click .read': 'itemRead',
                'click .open-entry-link': 'openLink',
                'click .starred': 'itemStarred'
            },
            render: function () {
                // Call parent contructor
                FeedBox.Views.ListItem.prototype.render.call(this);

                if (this.model) {
                    var read = this.model.get('read');
                    if (read == undefined || read == null) {
                        this.$el.removeClass('entry-read');
                    } else {
                        this.$el.addClass('entry-read');
                    }

                    var folded = this.model.get('folded');

                    if (folded) {
                        this.$el.removeClass('entry-unfolded');

                        // Read last scroll position and scroll back if necessary
                        var scrollPosition = this.model.get('scrollPosition');
                        if (scrollPosition) {
                            var element = Backbone.$('#' + this.$el.attr('id')),
                                position = element.position();

                            if (position && position.top && position.top < 0) {
                                var contentEl = Backbone.$('#content');
                                contentEl.scrollTop(scrollPosition);
                            }
                        }
                    } else {
                        this.$el.addClass('entry-unfolded');
                    }

                    if (this.model.get('source')) {
                        var color = this.model.get('source').color();
                        if (color) {
                            this.$el.attr('style', 'border-left-color: ' + color);
                        }
                    }
                }

                return this;
            },
            showDetails: function (e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                if (this.model) {
                    var folded = this.model.get('folded'),
                        read = this.model.get('read');
                    if (folded) {
                        if (read == undefined || read == null) {
                            this.model.read(true);
                        }
                        this.model.set({
                            folded: false,
                            scrollPosition: Backbone.$('#content').scrollTop()
                        });
                    } else {
                        this.model.set('folded', true);
                    }
                }
            },
            itemRead: function (e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                if (this.model.get('read')) {
                    this.model.read(false);
                } else {
                    this.model.read(true);
                }
            },
            itemStarred: function (e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                if (this.model.get('starred') == 1) {
                    this.model.save({ 'starred': 0 });
                } else {
                    this.model.save({ 'starred': 1 });
                }
            },
            openLink: function (e) {
                if (e) {
                    e.stopPropagation();
                }

                if (!this.model.get('read')) {
                    this.model.read(true);
                }
            }
        })
    },
    initialize: function (App) {
        App.Session.set('item-collection', new App.Module.Item.Collection());
    }
});

FeedBox.Module.Item.Collection = Backbone.Collection.extend({
    model: FeedBox.Module.Item.Model,
    url: BASE_URL + '/items',
    total: function (total) {
        if (total !== undefined) {
            this.totalCount = total;
        }

        return this.totalCount || 0;
    },
    markItemRead: function (options) {
        options = options || {};

        var models = this.map(function (model) {
                var read = model.get('read');
                if (read === undefined || read === null) {
                    return model.id;
                }
                return undefined;
            }),
            params = _.extend({
                url: BASE_URL + '/read',
                data: JSON.stringify(models),
                dataType: 'json',
                type: 'PUT'
            }, options);

        return Backbone.ajax(params);
    },
    fetchNext: function (options) {
        options = options || {};

        var data = FeedBox.Session.get('item-collection-data'),
            params = _.extend({ data: data }, options);

        if (options.reset) {
            data.page = 1;
        } else {
            params.remove = false;
            data.page += 1;
        }

        params.success = function (models, textStatus, jqXHR) {
            FeedBox.Session.set('item-collection-data', data);
            if (options.success) {
                options.success(models, textStatus, jqXHR);
            }
        }

        if (options.error) {
            params.error = function (models, textStatus, jqXHR) {
                options.error(models, textStatus, jqXHR);
            }
        }

        return this.fetch(params);
    },
    endReached: function () {
        return (this.length >= this.totalCount);
    }
});

FeedBox.Module.Item.Views.List = FeedBox.Views.List.extend({
    el: '#content',
    options: {
        prefix: 'item-',
        bottom: 20,
        emptyTemplate: '#tpl-empty',
        item: {
            attributes: {
                'class': 'entry'
            },
            tagName: 'article',
            template: '#tpl-item',
            View: FeedBox.Module.Item.Views.Item
        }
    },
    events: {
        'scroll': 'addMore'
    },
    initialize: function () {
        // Call parent contructor
        FeedBox.Views.List.prototype.initialize.call(this);

        if (this.collection) {
            this.collection.on('sync', this.retrieveItemCount, this);
        }
    },
    render: function () {
        // Call parent contructor
        FeedBox.Views.List.prototype.render.call(this);

        this.isLoading = false;

        return this;
    },
    remove: function () {
        // Call parent contructor
        FeedBox.Views.List.prototype.remove.call(this);

        if (this.collection) {
            this.collection.off('sync', this.retrieveItemCount, this);
        }

        return this;
    },
    retrieveItemCount: function (collection, response, options) {
        if (options) {
            var xhr = undefined;
            if (options.hasOwnProperty('getResponseHeader')) {
                xhr = options;
            } else if (options.xhr) {
                xhr = options.xhr;
            }

            if (xhr) {
                var total = xhr.getResponseHeader('X-Items-Total');
                if (collection && collection.total) {
                    collection.total(total);
                }
            }
        }
    },
    addMore: function (e) {
        var triggerPoint = 100; // 100px from the bottom
        if (!this.isLoading) {
            if(this.el.scrollTop + this.el.clientHeight + triggerPoint > this.el.scrollHeight) {
                this.isLoading = true;
                if (!this.collection.endReached()) {
                    var that = this;
                    this.collection.fetchNext({
                        success: function() {
                            that.isLoading = false;
                        }
                    });
                }
            }
        }
    }
});
"use strict";

App.Module.Item = {
    Model: Backbone.Model.extend({
        defaults: {
            folded: true
        },
        parse: function (response, options) {
            var sources = App.Session.get('source-collection');
            if (sources) {
                if (response.source_id) {
                    response.source = sources.get(response.source_id);
                }
            }
            return response;
        },
        timeHumanize: function() {
            var date = moment(this.get('pubdate'));
            if (date.isSame(moment(), 'day')) {
                return date.format('HH:mm:ss');
            } else {
                return date.format('YYYY-MM-DD HH:mm');
            }
        }
    }),
    Views: {
        Item: App.Views.ListItem.extend({
            events: {
                'click .foldable': 'showDetails',
                'click .read': 'itemRead',
                'click .open-entry-link': 'openLink',
                'click .starred': 'itemStarred'
            },
            render: function() {
                // Call parent contructor
                App.Views.ListItem.prototype.render.call(this);

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
                    } else {
                        this.$el.addClass('entry-unfolded');
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
                            this.model.save({
                                'folded': false,
                                'read': moment().format('YYYY-MM-DD HH:mm:ss')
                            });
                        } else {
                            this.model.set('folded', false);
                        }
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
                    this.model.save({ 'read': null });
                } else {
                    this.model.save({ 'read': moment().format('YYYY-MM-DD HH:mm:ss') });
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
            openLink: function(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                var win=window.open(this.model.get('uri'), '_blank');
                win.focus();

                if (this.model) {
                    this.model.save({ 'read': moment().format('YYYY-MM-DD HH:mm:ss') });
                }
            }
        })
    },
    initialize: function (App) {
        App.Session.set('item-collection', new App.Module.Item.Collection());
    }
};

App.Module.Item.Views.List = App.Views.List.extend({
    el: '#content-list',
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
            View: App.Module.Item.Views.Item
        }
    },
    events: {
        'scroll': 'addMore'
    },
    initialize: function() {
        // Call parent contructor
        App.Views.List.prototype.initialize.call(this);

        if (this.collection) {
            this.collection.on('sync', this.retrieveItemCount, this);
        }
    },
    render: function() {
        // Call parent contructor
        App.Views.List.prototype.render.call(this);

        this.updateHeight();

        this.isLoading = false;

        return this;
    },
    remove: function() {
        // Call parent contructor
        App.Views.List.prototype.remove.call(this);

        if (this.collection) {
            this.collection.off('sync', this.retrieveItemCount, this);
        }

        return this;
    },
    updateHeight: function() {
        var position = this.$el.position(),
            height = $(window).height() - this.options.bottom;

        if (position) {
            height -= position.top;
        }
        this.$el.height(height);
    },
    retrieveItemCount: function(collection, response, options) {
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
    addMore: function(e) {
        var triggerPoint = 100; // 100px from the bottom
        if (!this.isLoading) {
            if(this.el.scrollTop + this.el.clientHeight + triggerPoint > this.el.scrollHeight) {
                this.isLoading = true;
                if (!this.collection.endReached()) {
                    var that = this;
                    this.collection.fetchNext().done(function() {
                        that.isLoading = false;
                    });
                }
            }
        }
    }
});

App.Module.Item.Collection = Backbone.Collection.extend({
    model: App.Module.Item.Model,
    url: BASE_URL + '/items',
    total: function(total) {
        if (total !== undefined) {
            this.totalCount = total;
        }

        return this.totalCount || 0;
    },
    markItemRead: function() {
        var models = this.map(function(model) {
            var read = model.get('read');
            if (read === undefined || read === null) {
                return model.id;
            }
            return undefined;
        });

        return Backbone.ajax({
            url: BASE_URL + '/read',
            data:  JSON.stringify(models),
            dataType: 'json',
            type: 'PUT'
        });
    },
    removeItemRead: function() {
        var models = this.filter(function(model) {
            var read = model.get('read');
            return read !== undefined && read !== null;
        });

        this.remove(models);
    },
    fetchNext: function(reset, success, error) {
        var data = App.Session.get('item-collection-data'),
            options = {
                async: false,
                data: data,
                success: function(data, textStatus, jqXHR) {
                    App.Session.set('item-collection-data', data);
                    if (success) {
                        success(data, textStatus, jqXHR);
                    }
                },
                error: function(data, textStatus, jqXHR) {
                    if (error) {
                        error(data, textStatus, jqXHR);
                    }
                }
            };

        if (reset) {
            options.reset = true;
            data.page = 1;
        } else {
            options.remove = true;
            data.page += 1;
        }

        return this.fetch(options);
    },
    endReached: function() {
        return (this.length >= this.totalCount);
    }

});
App.Module.Item.initialize(App);
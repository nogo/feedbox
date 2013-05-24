"use strict";

App.Module.Item = {
    Model: Backbone.Model.extend({
        defaults: {
            folded: true
        },
        urlRoot: 'api.php/items',
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

        this.itemTotal = 0;

        if (this.collection) {
            this.collection.on('sync', this.retrieveItemCount, this);
        }
    },
    render: function() {
        // Call parent contructor
        App.Views.List.prototype.render.call(this);

        var position = this.$el.position(),
            height = $(window).height() - this.options.bottom;

        if (position) {
            height -= position.top;
        }
        this.$el.height(height);

        this.isLoading = false;

        return this;
    },
    retrieveItemCount: function(collection, xhr, options) {
        if(options && options.hasOwnProperty('getResponseHeader')) {
            options = {
                xhr: options
            };
        }
        if(options && options.xhr && options.xhr.getResponseHeader('X-Items-Total')) {
            this.itemTotal = options.xhr.getResponseHeader('X-Items-Total');
            collection.total(this.itemTotal);
        }
    },
    addMore: function(e) {
        var triggerPoint = 100; // 100px from the bottom
        if (!this.isLoading) {
            if(this.el.scrollTop + this.el.clientHeight + triggerPoint > this.el.scrollHeight) {
                this.isLoading = true;
                if (this.collection.length < this.itemTotal) {
                    var that = this,
                        data =  App.Session.get('item-collection-data');

                    data.page += 1;
                    this.collection.fetch({
                        remove: false,
                        async: false,
                        data: data,
                        success: function(models, textStatus, jqXHR) {
                            App.Session.set('item-collection-data', data);
                            that.isLoading = false;
                        },
                        error: function() {

                        }
                    });
                }
            }
        }
    }
});

App.Module.Item.Collection = Backbone.Collection.extend({
    model: App.Module.Item.Model,
    url: 'api.php/items',
    total: function(total) {
        if (total !== undefined) {
            this.totalCount = total;
        }

        return this.totalCount || 0;
    }

});
App.Module.Item.initialize(App);
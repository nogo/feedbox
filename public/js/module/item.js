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
                return date.format('YYYY-MM-DD');
            }
        }
    }),
    Views: {
        Item: App.Views.ListItem.extend({
            events: {
                'click .foldable': 'showDetails',
                'click .read': 'itemRead',
                'click .open-entry-link': 'openLink'
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

App.Module.Item.Views.List = Backbone.View.extend({
    initialize: function () {
        this.items = App.Session.get('item-collection');
        this.items.fetch();
    },
    render: function () {
        this.itemList = new App.Views.List({
            el: '#content',
            prefix: 'item-',
            collection: this.items,
            item: {
                attributes: {
                    'class': 'entry'
                },
                tagName: 'article',
                template: '#tpl-item',
                View: App.Module.Item.Views.Item
            }
        });
        this.itemList.render();

        return this;
    },
    remove: function () {
        if (this.itemList) {
            this.itemList.remove();
        }
    }
});

App.Module.Item.Collection = Backbone.Collection.extend({
    model: App.Module.Item.Model,
    url: 'api.php/items'
});
App.Module.Item.initialize(App);
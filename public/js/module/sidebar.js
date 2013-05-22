"use strict";

App.Module.Sidebar = {
    Views: {
        Main: Backbone.View.extend({
            el: '#sidebar',
            events: {
                'click .toggable': 'toggable',
                'click .mark-as-read': 'markAsRead',
                'click .reload': 'reload'
            },
            initialize: function() {
                this.sources = App.Session.get('source-collection');
                this.items = App.Session.get('item-collection');

                if (this.sources) {
                    this.sources.fetch({ async: false });
                }
            },
            render: function() {
                // Source view
                var sourceList = new App.Views.List({
                    prefix: 'source-',
                    tagName: 'ul',
                    collection: this.sources,
                    attributes: {
                        'class': 'nav nav-list'
                    },
                    item: {
                        tagName: 'li',
                        template: '#tpl-sidebar-source-item',
                        View: App.Views.ListItem
                    }
                });
                this.$('#sources').html(sourceList.render().el);

                return this;
            },
            toggable: function(e) {
                if (e) {
                    e.preventDefault();
                }

                var component = $(e.currentTarget),
                    target = this.$(component.data('target'));

                if (target) {
                    target.toggle();
                    var position = target.position(),
                        height = $(window).height() - position.top - 40;
                    target.height(height);
                }
            },
            markAsRead: function(e) {
                if (e) {
                    e.preventDefault();
                }

                var marked = []
                this.items.each(function(model) {
                    if (!model.get('read')) {
                        model.save({ 'read': moment().format('YYYY-MM-DD HH:mm:ss') });
                        marked.push(model);
                    }
                });

                var data =  App.Session.get('item-collection-data');
                data.page = 1;
                this.items.fetch({
                    remove: false,
                    async: false,
                    data: data,
                    success: function(models, textStatus, jqXHR) {
                        App.Session.set('item-collection-data', data);
                    },
                    error: function() {

                    }
                });

                this.items.remove(marked);
            },
            reload: function(e) {
                if (e) {
                    e.preventDefault();
                }

                var data =  App.Session.get('item-collection-data');
                data.page = 1;
                this.items.fetch({
                    remove: true,
                    async: false,
                    data: data,
                    success: function(models, textStatus, jqXHR) {
                        App.Session.set('item-collection-data', data);
                        var view = App.Session.get('content-view');
                        view.el.scrollTop = 0;
                    },
                    error: function() {

                    }
                });
            }
        }),
        Top: Backbone.View.extend({
            el: '#sidebar-top',
            events: {
                'click .toggable': 'toggable'
            },
            render: function() {
                return this;
            },
            toggable: function(e) {
                if (e) {
                    e.preventDefault();
                }

                var component = $(e.currentTarget),
                    target = $(component.data('target'));

                if (target) {
                    target.toggle();
                }
            }
        })
    },
    initialize: function(App) {
        var main = new this.Views.Main();
        main.render();
        App.Session.set('sidebar', main);

        var top = new this.Views.Top();
        top.render();
        App.Session.set('sidebar-top', top);

    }
};
App.Module.Sidebar.initialize(App);
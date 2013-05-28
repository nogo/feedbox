"use strict";

App.Module.Sidebar = {
    Views: {
        Top: Backbone.View.extend({
            el: '#sidebar-top',
            events: {
                'click .toggable': 'toggable',
                'click .mark-as-read': 'markAsRead',
                'click .reload': 'reload'
            },
            initialize: function() {
                this.items = App.Session.get('item-collection');
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
            },
            markAsRead: function(e) {
                if (e) {
                    e.preventDefault();
                }

                var that = this;

                // save models read state
                this.items.markItemRead({
                    success: function(models, textStatus, jqXHR) {
                        that.items.fetchNext({
                            reset: true,
                            success: function(models, textStatus, jqXHR) {
                                var view = App.Session.get('content-view');
                                view.el.scrollTop = 0;
                            }
                        });
                    }
                });
            },
            reload: function(e) {
                if (e) {
                    e.preventDefault();
                }

                this.items.fetchNext({
                    reset: true,
                    success: function(models, textStatus, jqXHR) {
                        var view = App.Session.get('content-view');
                        view.el.scrollTop = 0;
                    }
                });
            }
        })
    },
    initialize: function(App) {
        this.sources = App.Session.get('source-collection');

        if (this.sources) {
            this.sources.fetch({ async: false });
        }

        var top = new this.Views.Top();
        top.render();
        App.Session.set('sidebar-top', top);

        var main = new this.Views.Main();
        main.render();
        App.Session.set('sidebar', main);

    }
};

App.Module.Sidebar.Views.Main = App.Module.Sidebar.Views.Top.extend({
    el: '#sidebar',
    initialize: function() {
        this.sources = App.Session.get('source-collection');
        this.items = App.Session.get('item-collection');
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
    }
});

App.Module.Sidebar.initialize(App);
"use strict";

App.Module.Sidebar = {
    Views: {
        Main: Backbone.View.extend({
            el: '#sidebar',
            events: {
                'click .toggable': 'toggable'
            },
            initialize: function() {
                this.sources = App.Session.get('source-collection');
                this.sources.fetch({ async: false });
            },
            render: function() {
                // Source view
                var sourceList = new App.Views.List({
                    prefix: 'source-',
                    tagName: 'ul',
                    collection: this.sources,
                    attributes: {
                        'class': 'nav nav-list tree'
                    },
                    item: {
                        tagName: 'li',
                        template: '#tpl-sidebar-source-item',
                        View: App.Views.ListItem
                    }
                });
                this.$('#sources').html(sourceList.render().el);

                //App.router.route('')

                return this;
            },
            toggable: function(e) {
                if (e) {
                    console.log(e.currentTarget);
                    e.preventDefault();
                }

                var component = $(e.currentTarget),
                    toggle = component.data('toggle');

                if (toggle) {
                    this.$(toggle).toggle();
                }
            }
        })
    },
    initialize: function(App) {
        var main = new this.Views.Main();
        main.render();
        App.Session.set('sidebar', main);

    }
};
App.Module.Sidebar.initialize(App);
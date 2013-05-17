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
        })
    },
    initialize: function(App) {
        var main = new this.Views.Main();
        main.render();
        App.Session.set('sidebar', main);

    }
};
App.Module.Sidebar.initialize(App);
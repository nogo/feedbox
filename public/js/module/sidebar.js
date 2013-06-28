"use strict";

App.Module.Sidebar = {
    Views: {
        Main: Backbone.View.extend({
            el: '#sidebar',
            initialize: function() {
                this.tags = App.Session.get('tag-collection');
            },
            render: function() {
                var elements = [];
                var tagList = new App.Views.List({
                    prefix: 'tag-',
                    tagName: 'ul',
                    collection: this.tags,
                    attributes: {
                        'class': 'nav nav-list'
                    },
                    item: {
                        tagName: 'li',
                        template: '#tpl-sidebar-tag-item',
                        View: App.Module.Tag.Views.SidebarItem
                    }
                });
                elements.push(tagList.render().el);

                var sources = App.Session.get('tagless-source-collection', function() {
                    var sourceCollection = App.Session.get('source-collection');
                    return new App.Module.Source.Collection(
                        sourceCollection.filter(function(model) {
                            var tagId = model.get('tag_id');
                            return tagId === null || tagId === '';
                        })
                    );
                });
                if (sources.length > 0) {
                    var sourceList = new App.Views.List({
                        prefix: 'source-',
                        tagName: 'ul',
                        collection: sources,
                        attributes: {
                            'class': 'nav nav-list'
                        },
                        item: {
                            tagName: 'li',
                            template: '#tpl-sidebar-source-item',
                            View: App.Views.ListItem
                        }
                    });
                    elements.push(sourceList.render().el);
                }

                this.$('#sidebar-sources').html(elements);

                return this;
            }
        })
    },
    initialize: function(App) {
        var hooks = App.Session.get('hooks');
        if (hooks) {
            hooks.add({
                scope: 'after-login',
                callback: function() {
                    var tags = App.Session.get('tag-collection'),
                        sources = App.Session.get('source-collection');

                    if (tags) {
                        tags.fetch({ async: false });
                    }

                    if (sources) {
                        sources.fetch({ async: false });
                    }

                    var main = new App.Module.Sidebar.Views.Main();
                    main.render();
                    App.Session.set('sidebar', main);
                }
            })
        }
    }
};

App.Module.Sidebar.initialize(App);
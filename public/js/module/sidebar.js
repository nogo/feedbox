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
        this.tags = App.Session.get('tag-collection');
        this.sources = App.Session.get('source-collection');

        if (this.tags) {
            this.tags.fetch({ async: false });
        }

        if (this.sources) {
            this.sources.fetch({ async: false });
        }

        var main = new this.Views.Main();
        main.render();
        App.Session.set('sidebar', main);
    }
};

App.Module.Sidebar.initialize(App);
"use strict";

App.Module.Tag = {
    Model: Backbone.Model.extend({
        defaults: {
            folded: true,
            unread: 0
        },
        sources: function() {
            if (!this.sourceCollection) {
                this.sourceCollection = new App.Module.Source.Collection();

                var ids = this.get('sources'),
                    sources = App.Session.get('source-collection');
                if (ids && sources) {
                    for (var i=0; i<ids.length; i++) {
                        this.sourceCollection.add(sources.get(ids[i]));
                    }
                }
            }
            return this.sourceCollection;
        }
    }),
    Views: {
        Item: App.Views.ListItem.extend({
            events: {
                'click .delete': 'deleteItem',
                'click .editable': 'click',
                'focus .editable': 'focus',
                'blur .editable': 'save',
                'keyup .editable': 'keyup'
            },
            deleteItem: function (e) {
                if (e) {
                    e.preventDefault();
                }

                if (this.model) {
                    var sources =  this.model.sources();
                    if (sources) {
                        var tagless = App.Session.get('tagless-source-collection');
                        sources.each(function(model) {
                            model.save({ tag_id: null });
                            tagless.add(model);
                        });
                    }
                    this.model.destroy();
                }
            },
            click: function(e) {
                if (e) {
                    e.stopPropagation();
                }
            },
            focus:  function(e) {
                if (e) {
                    var item = $(e.currentTarget),
                        key = item.data('editorModelKey');

                    // load data from model
                    item.text(this.model.get(key));
                }
                return this;
            },
            keyup:  function(e) {
                if (e) {
                    var item = $(e.currentTarget);
                    if (e.keyCode) {
                        // ESC cancel
                        if (e.keyCode == 27) {
                            item.data('editorCancel', 'true');
                            item.trigger('blur');
                        } else if (e.altKey && e.ctrlKey && e.keyCode == 83 ) { // ALT + CTRL + s
                            item.removeData('editorCancel');
                            item.trigger('blur');
                        }
                    }
                }
                return this;
            },
            save: function(e) {
                if (e) {
                    e.stopPropagation();
                }

                var item = $(e.currentTarget),
                    key = '',
                    data = {};

                if (item) {
                    key = item.data('editorModelKey');

                    if (item.data('editorCancel')) {
                        item.removeData('editorCancel');
                        item.html(this.model.get(key));
                        App.notify('Update canceled.', 'error');
                    } else {
                        data[key] = item.text();
                        this.model.save(data, {
                            success: function(model) {
                                App.notify('Update successful.', 'success');
                            },
                            error: function(model, response) {
                                App.notify(response.status + ": " + response.statusText, "error");
                            }
                        });

                    }
                }
                return this;
            }
        }),
        SidebarItem: App.Views.ListItem.extend({
            events: {
                'click .toggable': 'toggleSource'
            },
            render: function() {
                // Call parent contructor
                App.Views.ListItem.prototype.render.call(this);

                var sourceList = new App.Views.List({
                    prefix: 'source-',
                    tagName: 'ul',
                    collection: this.model.sources(),
                    attributes: {
                        'class': 'nav nav-list tag-sources'
                    },
                    item: {
                        tagName: 'li',
                        template: '#tpl-sidebar-source-item',
                        View: App.Views.ListItem
                    }
                });
                this.$el.append(sourceList.render().el);

                if (this.model.get('folded')) {
                    this.$el.removeClass('tag-unfolded');
                } else {
                    this.$el.addClass('tag-unfolded');
                }

                return this;
            },
            toggleSource: function(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                if (this.model) {
                    if (this.model.get('folded')) {
                        this.model.set('folded', false);
                    } else {
                        this.model.set('folded', true);
                    }
                }

            }
        })

    },
    initialize: function(App) {
        App.Session.set('tag-collection', new App.Module.Tag.Collection());
    }
};

App.Module.Tag.Collection = Backbone.Collection.extend({
    model: App.Module.Tag.Model,
    url: BASE_URL + '/tags',
    comparator: function(model) {
        return model.get('name').toLowerCase();
    }
});

App.Module.Tag.initialize(App);

/* Routes */

App.router.route('tag/:id', function(id) {
    var items = App.Session.get('item-collection'),
        settings = App.Session.get('setting-collection');

    App.switchView('content-view', 'item-list', function() {
        return new App.Module.Item.Views.List({
            collection: items
        });
    });

    if (items) {
        var data = App.Session.get('item-collection-data', function() {
                return {
                    mode: 'unread',
                    page: 1,
                    limit: settings.getByKey('view.unread.count', 50),
                    sortby: settings.getByKey('view.unread.sortby', 'newest')
                };
            }),
            selectedMenuItem = ['.menu-item-tag-' + id];

        selectedMenuItem.push('.menu-item-' + data.mode);
        data.page = 1;
        data.tag = id;
        if (data.source) {
            delete data.source;
        }

        App.Session.set('item-collection-data', data);
        items.fetch({
            reset: true,
            data: data
        });
        App.Session.set('selected-menu-items', selectedMenuItem);
        App.selectMenuItem();
    }
});
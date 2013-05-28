"use strict";

App.router.route('', function() {
    App.router.navigate('unread', { trigger: true });
});

App.router.route('unread', function() {
    var items = App.Session.get('item-collection');

    App.switchView('content-view', 'item-list', function() {
        return new App.Module.Item.Views.List({
            collection: items
        });
    });

    if (items) {
        var data = {
            'unread': true,
            'limit': 50,
            'page': 1,
            'sortby': 'oldest'
        };
        App.Session.set('item-collection-data', data);
        items.fetch({
            reset: true,
            data: data
        });
        App.Session.set('selected-menu-items', ['.menu-item-unread']);
        App.selectMenuItem();
    }
});

App.router.route('read', function() {
    var items = App.Session.get('item-collection');

    App.switchView('content-view', 'item-list', function() {
        return new App.Module.Item.Views.List({
            collection: items
        });
    });

    if (items) {
        var data = {
            'unread': false,
            'limit': 50,
            'page': 1,
            'sortby': 'newest'
        };
        App.Session.set('item-collection-data', data);
        items.fetch({
            reset: true,
            data: data
        });
        App.Session.set('selected-menu-items', ['.menu-item-read']);
        App.selectMenuItem();
    }
});

App.router.route('starred', function() {
    var items = App.Session.get('item-collection');

    App.switchView('content-view', 'item-list', function() {
        return new App.Module.Item.Views.List({
            collection: items
        });
    });

    if (items) {
        var data = {
            'starred': true,
            'limit': 50,
            'page': 1,
            'sortby': 'newest'
        };
        App.Session.set('item-collection-data', data);
        items.fetch({
            reset: true,
            data: data
        });

        App.Session.set('selected-menu-items', ['.menu-item-starred']);
        App.selectMenuItem();
    }
});

/* Sources */

App.router.route('sources', function() {
    var sources = App.Session.get('source-collection');
    App.switchView('content-view', 'source-list', function() {
        return new App.Module.Source.Views.List({
            collection: sources
        });
    });

    App.Session.get('content-footer-view').hide();
    App.Session.set('selected-menu-items', ['.menu-item-sources']);
    App.selectMenuItem();
});

App.router.route('sources/:id', function(id) {
    var items = App.Session.get('item-collection');

    App.switchView('content-view', 'item-list', function() {
        return new App.Module.Item.Views.List({
            collection: items
        });
    });

    if (items) {
        var data = App.Session.get('item-collection-data', function() {
            return {
                unread: true
            };
        }),
        selectedMenuItem = ['.menu-item-source-' + id];

        if (data.unread) {
            selectedMenuItem.push('.menu-item-unread');
        } else if (!data.unread) {
            selectedMenuItem.push('.menu-item-read');
        } else if (data.starred) {
            selectedMenuItem.push('.menu-item-starred');
        }

        data.limit = 50;
        data.page = 1;
        data.source = id;

        App.Session.set('item-collection-data', data);
        items.fetch({
            reset: true,
            data: data
        });
        App.Session.set('selected-menu-items', selectedMenuItem);
        App.selectMenuItem();
    }
});

App.router.route('sources/add', function(id) {
    var sources = App.Session.get('source-collection'),
        model = new App.Module.Source.Model();

    App.switchView('content-view', 'source-add', function() {
        return new App.Module.Source.Views.Form({
            el: '#content-list',
            model: model,
            collection: sources
        });
    });
    App.Session.get('content-footer-view').hide();
});

App.router.route('sources/:id/edit', function(id) {
    var sources = App.Session.get('source-collection'),
        model = sources.get(id);

    if (model) {
        App.switchView('content-view', 'source-edit', function() {
            return new App.Module.Source.Views.Form({
                el: '#content-list',
                model: model,
                collection: sources
            });
        });
        App.Session.get('content-footer-view').hide();
    } else {
        App.notifier.add("Source not found", "error");
        App.router.navigate('sources', { trigger: true });
    }
});

App.router.route('sources/update', function() {
    App.notifier.add("Update started.", "success");
    Backbone.ajax({
        url: BASE_PATH + '/update',
        cache: false,
        dataType: 'text',
        success: function() {
            App.notifier.add("Update successfull.", "success");
            App.notifier.show('#content');
        },
        error: function() {
            App.notifier.add("Update failed.", "error");
            App.notifier.show('#content');
        }
    });
});

App.router.route('sources/:id/update', function(id) {
    var sources = App.Session.get('source-collection'),
        model = sources.get(id);

    if (model) {
        Backbone.ajax({
            url: BASE_PATH + '/update/' + model.id,
            cache: false,
            dataType: 'text',
            success: function() {
                App.notifier.add(model.get('name') + " - Source update successfull.", "success");
                model.fetch();
            },
            error: function() {
                App.notifier.add(model.get('name') + " - Source update failed.", "error");
            }
        });
    }

    App.router.navigate('sources', { trigger: true });
});

"use strict";

App.router.route('', function() {
    App.router.navigate('unread', { trigger: true });
});

App.router.route('unread', function() {
    var items = App.Session.get('item-collection'),
        settings = App.Session.get('setting-collection');

    App.switchView('content-view', 'item-list', function() {
        return new App.Module.Item.Views.List({
            collection: items
        });
    });

    if (items) {
        var data = {
            mode: 'unread',
            page: 1,
            limit: settings.getByKey('view.unread.count', 50),
            sortby: settings.getByKey('view.unread.sortby', 'newest')
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
    var items = App.Session.get('item-collection'),
        settings = App.Session.get('setting-collection');

    App.switchView('content-view', 'item-list', function() {
        return new App.Module.Item.Views.List({
            collection: items
        });
    });

    if (items) {
        var data = {
            mode: 'read',
            page: 1,
            limit: settings.getByKey('view.read.count', 50),
            sortby: settings.getByKey('view.read.sortby', 'newest')
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
    var items = App.Session.get('item-collection'),
        settings = App.Session.get('setting-collection');

    App.switchView('content-view', 'item-list', function() {
        return new App.Module.Item.Views.List({
            collection: items
        });
    });

    if (items) {
        var data = {
            mode: 'starred',
            page: 1,
            limit: settings.getByKey('view.starred.count', 50),
            sortby: settings.getByKey('view.starred.sortby', 'newest')
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

App.router.route('search(/:name)', function(name) {
    if (!name || _.isEmpty(name)) {
        App.router.navigate('unread', { trigger: true });
    } else {
        var items = App.Session.get('item-collection'),
            settings = App.Session.get('setting-collection');

        App.switchView('content-view', 'item-list', function() {
            return new App.Module.Item.Views.List({
                collection: items
            });
        });

        if (items) {
            var query = name.replace(/\+/g,' ');

            Backbone.$('#search-input').val(query);

            var data = {
                search: query,
                sortby: 'newest'
            };
            App.Session.set('item-collection-data', {});
            items.fetch({
                reset: true,
                data: data
            });
            App.Session.set('selected-menu-items', []);
            App.selectMenuItem();
        }
    }
});

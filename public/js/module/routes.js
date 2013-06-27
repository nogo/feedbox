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
            'unread': true,
            'page': 1,
            'limit': settings.getByKey('view.unread.count', 50),
            'sortby': settings.getByKey('view.unread.sortby', 'newest')
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
            'unread': false,
            'page': 1,
            'limit': settings.getByKey('view.read.count', 50),
            'sortby': settings.getByKey('view.read.sortby', 'newest')
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

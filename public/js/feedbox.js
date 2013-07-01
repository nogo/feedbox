"use strict";

var FeedBox = new Nerve.Application();

FeedBox.Router.route('', function() {
    FeedBox.Router.navigate('unread', { trigger: true });
});

FeedBox.Router.route('unread', function() {
    var items = FeedBox.Session.get('item-collection'),
        settings = FeedBox.Session.get('setting-collection');

    FeedBox.switch('content-view', 'item-list', function() {
        return new FeedBox.Module.Item.Views.List({
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
        FeedBox.Session.set('item-collection-data', data);
        items.fetch({
            reset: true,
            data: data
        });
        FeedBox.Session.set('selected-menu-items', ['.menu-item-unread']);
        FeedBox.selectMenuItem();
    }
});

FeedBox.Router.route('read', function() {
    var items = FeedBox.Session.get('item-collection'),
        settings = FeedBox.Session.get('setting-collection');

    FeedBox.switch('content-view', 'item-list', function() {
        return new FeedBox.Module.Item.Views.List({
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
        FeedBox.Session.set('item-collection-data', data);
        items.fetch({
            reset: true,
            data: data
        });
        FeedBox.Session.set('selected-menu-items', ['.menu-item-read']);
        FeedBox.selectMenuItem();
    }
});

FeedBox.Router.route('starred', function() {
    var items = FeedBox.Session.get('item-collection'),
        settings = FeedBox.Session.get('setting-collection');

    FeedBox.switch('content-view', 'item-list', function() {
        return new FeedBox.Module.Item.Views.List({
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
        FeedBox.Session.set('item-collection-data', data);
        items.fetch({
            reset: true,
            data: data
        });

        FeedBox.Session.set('selected-menu-items', ['.menu-item-starred']);
        FeedBox.selectMenuItem();
    }
});

FeedBox.Router.route('search(/:name)', function(name) {
    if (!name || _.isEmpty(name)) {
        FeedBox.Router.navigate('unread', { trigger: true });
    } else {
        var items = FeedBox.Session.get('item-collection'),
            settings = FeedBox.Session.get('setting-collection');

        FeedBox.switch('content-view', 'item-list', function() {
            return new FeedBox.Module.Item.Views.List({
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
            FeedBox.Session.set('item-collection-data', {});
            items.fetch({
                reset: true,
                data: data
            });
            FeedBox.Session.set('selected-menu-items', []);
            FeedBox.selectMenuItem();
        }
    }
});

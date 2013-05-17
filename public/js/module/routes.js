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
            'page': 1
        };
        App.Session.set('item-collection-data', data);
        items.fetch({
            reset: true,
            data: data
        });
    }
});

App.router.route('read', function() {
    App.switchView('content-view', 'item-list', function() {
        return new App.Module.Item.Views.List({
            el: '#content'
        });
    });

    var items = App.Session.get('item-collection');
    if (items) {
        items.fetch({
            reset: true,
            data: {
                'unread': false,
                'limit': 50,
                'page': 1
            }
        });
    }
});

/* Sources */

App.router.route('sources', function() {
    App.switchView('content-view', 'source-list', function() {
        return new App.Module.Source.Views.List({
            el: '#content'
        });
    });
});

App.router.route('sources/add', function(id) {
    var sources = App.Session.get('source-collection'),
        model = new App.Module.Source.Model();

    App.switchView('content-view', 'source-add', function() {
        return new App.Module.Source.Views.Form({
            el: '#content',
            model: model,
            collection: sources
        });
    });
});

App.router.route('sources/:id/edit', function(id) {
    var sources = App.Session.get('source-collection'),
        model = sources.get(id);

    if (model) {
        App.switchView('content-view', 'source-edit', function() {
            return new App.Module.Source.Views.Form({
                el: '#content',
                model: model,
                collection: sources
            });
        });
    } else {
        App.notifier.add("Source not found", "error");
        App.router.navigate('sources', { trigger: true });
    }
});

App.router.route('sources/update', function() {
    App.notifier.add("Update started.", "success");
    $.ajax('api.php/update', {
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
        $.ajax('api.php/update/' + model.id, {
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

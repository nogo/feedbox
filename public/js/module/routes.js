"use strict";

App.router.route('', function() {
    App.switchView('content-view', new App.Module.Item.Views.List({
        el: '#content'
    }));
});

App.router.route('unread', function() {
    App.switchView('content-view', new App.Module.Item.Views.List({
        el: '#content'
    }));
});

App.router.route('read', function() {

});

/* Sources */

App.router.route('sources', function() {
    App.switchView('content-view', new App.Module.Source.Views.List({
        el: '#content'
    }));
});

App.router.route('sources/add', function(id) {
    var sources = App.Session.get('source-collection'),
        model = new App.Module.Source.Model();

    App.switchView('content-view', new App.Module.Source.Views.Form({
        el: '#content',
        model: model,
        collection: sources
    }));
});

App.router.route('sources/:id/edit', function(id) {
    var sources = App.Session.get('source-collection'),
        model = sources.get(id);

    if (model) {
        App.switchView('content-view', new App.Module.Source.Views.Form({
            el: '#content',
            model: model,
            collection: sources
        }));
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

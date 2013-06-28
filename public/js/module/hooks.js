"use strict";

App.Module.Hook = {
    Model: Backbone.Model.extend({
        defaults: {
            weight: 0,
            scope: 'default'
        }
    }),
    initialize: function(App) {
        App.Session.set('hooks', new App.Module.Hook.Collection());
    }
};

App.Module.Hook.Collection = Backbone.Collection.extend({
    model: App.Module.Hook.Model,
    comparator:function (model) {
        return model.get('weight');
    },
    call: function(scope) {
        var hooks = this.where({ scope: scope });
        for(var i=0; i<hooks.length; i++) {
            var model = hooks[i],
                callback = model.get('callback');

            if (callback) {
                callback();
            }
        }
    }
});

App.Module.Hook.initialize(App);
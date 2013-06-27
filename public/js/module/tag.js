"use strict";

App.Module.Tag = {
    Model: Backbone.Model.extend(),
    Views: {
        Item: App.Views.ListItem.extend({
            events: {
                'click .delete': 'deleteItem'
            },
            deleteItem: function (e) {
                if (e) {
                    e.preventDefault();
                }

                if (this.model) {
                    this.model.destroy();
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
    url: BASE_URL + '/tags'
});

App.Module.Tag.initialize(App);
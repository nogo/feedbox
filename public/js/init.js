"use strict";

(function(App, Backbone) {
    App.initialize();

    var user = App.Session.get('user');

    if (user) {
        if (user.accessNeeded()) {
            user.applySetup();
        }

        var appview = new App.Module.Main.Views.App({
            model: user
        });
        appview.render();
    }

})(FeedBox, Backbone);
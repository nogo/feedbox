"use strict";

(function(App, Backbone) {
    App.initialize();

    var sync = Backbone.sync,
        user = App.Session.get('user');

    if (user) {
        if (user.accessNeeded()) {
            Backbone.$.ajaxSetup({
                headers: user.accessHeader(),
                statusCode: {
                    401: function() {
                        var user = FeedBox.Session.get('user');
                        if (user && user.get('loggedId')) {
                            user.signout();
                        }
                    },
                    403: function() {
                        var user = FeedBox.Session.get('user');
                        if (user && user.get('loggedId')) {
                            user.signout();
                        }
                    }
                }
            });
        }

        var appview = new App.Module.Main.Views.App({
            model: user
        });
        appview.render();
    }

})(FeedBox, Backbone);
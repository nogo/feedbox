"use strict";

(function(App, Backbone) {
    App.initialize();

    var sync = Backbone.sync,
        user = App.Session.get('user');

    if (user) {
        if (user.accessNeeded()) {
            Backbone.$.ajaxSetup({
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

            // send token every time
            Backbone.sync = function(method, model, options) {
                options.headers = options.headers || {};
                if (user) {
                    _.extend(options.headers, user.accessHeader());
                }
                return sync.call(model, method, model, options);
            };
        }

        var appview = new App.Module.Main.Views.App({
            model: user
        });
        appview.render();
    }

})(FeedBox, Backbone);
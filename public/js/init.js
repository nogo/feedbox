"use strict";

Backbone.$.ajaxSetup({
    statusCode: {
        401: function() {
            var user = App.Session.get('user');
            if (user && user.get('loggedId')) {
                user.signout();
            }
            //App.router.navigate('login', { trigger: true });
        },
        403: function() {
            var user = App.Session.get('user');
            if (user && user.get('loggedId')) {
                user.signout();
            }
            //App.router.navigate('', { trigger: true });
        }
    }
});

(function(App, Backbone) {
    var sync = Backbone.sync,
        user = App.Session.get('user', function() {
            return new App.Module.User.Model();
        });

    // send token every time
    Backbone.sync = function(method, model, options) {
        if (user && user.get('loggedIn')) {
            options.headers = options.headers || {};
            _.extend(options.headers, {
                'AUTH_USER': user.get('user'),
                'AUTH_CLIENT': user.get('client'),
                'AUTH_TOKEN': user.get('token')
            });
        }
        return sync.call(model, method, model, options);
    };

    var appView = new App.Views.Main({
        model: user
    });
    appView.render();

})(App, Backbone);
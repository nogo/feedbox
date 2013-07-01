"use strict";

/**
 * User Module
 *
 * Hooks:
 * @call sigin-success
 * @call sigin-error
 * @call sigout-success
 * @call sigout-error
 *
 * @type {Nerve.Module}
 */
FeedBox.Module.User = new Nerve.Module({
    Model: Backbone.Model.extend({
        url: BASE_URL + '/login',
        defaults: {
            user: null,
            client: null,
            token: null,
            loggedIn: false,
            authRequired: undefined
        },
        initialize: function () {
            var client = localStorage['client'],
                data = {
                    user: localStorage['user'],
                    token: localStorage['token']
                };

            if (client) {
                data.client = client;
            } else {
                data.client = Math.uuid();
                localStorage['client'] = data.client;
            }

            data.loggedIn = (data.user && data.token);
            if (data.loggedIn) {
                data.authRequired = true;
            }
            this.set(data);
        },
        accessNeeded: function() {
            var that = this;

            if (this.get('authRequired') === undefined) {
                Backbone.$.ajax({
                    url: BASE_URL + '/login',
                    type: 'POST',
                    dataType: 'json',
                    async: false,
                    success: function(data, textStatus, jqXHR) {
                        that.set({
                            'authRequired': false,
                            'loggedIn': true
                        });
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        that.set({
                            'authRequired': true,
                            'loggedIn': false
                        });
                    }
                });
            }

            return this.get('authRequired');
        },
        accessHeader: function() {
            return {
                AUTH_USER: this.get('user'),
                AUTH_CLIENT: this.get('client'),
                AUTH_TOKEN: this.get('token')
            };
        },
        signin: function(user, password) {
            var that = this;

            Backbone.$.ajax({
                url: BASE_URL + '/login',
                type: 'POST',
                dataType: 'json',
                headers: {
                    'AUTH_USER': user,
                    'AUTH_PASS': password,
                    'AUTH_CLIENT': this.get('client')
                },
                success: function(data, textStatus, jqXHR) {
                    var token = jqXHR.getResponseHeader('NEXT_AUTH_TOKEN');

                    if (token) {
                        localStorage['user'] = user;
                        localStorage['token'] = token;
                        that.set({
                            user: user,
                            token: token,
                            loggedIn: true
                        }, { silent: true });
                        FeedBox.Hook.call('signin-success');
                        that.trigger('change:loggedIn');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    FeedBox.notify('Username or password wrong.', 'error');
                    FeedBox.Hook.call('signin-error');
                }
            });

            return this;
        },
        signout: function() {
            var that = this;

            Backbone.$.ajax({
                url: BASE_URL + '/logout',
                type: 'POST',
                dataType: 'json',
                data: {
                    user: this.get('user'),
                    client: this.get('client')
                },
                success: function(data, textStatus, jqXHR) {
                    localStorage.removeItem('user');
                    localStorage.removeItem('token');
                    that.set({ user: null, token: null, loggedIn: false }, { silent: true });
                    FeedBox.Hook.call('signout-success');
                    that.trigger('change:loggedIn');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    FeedBox.Hook.call('signout-error');
                }
            });

            return this;
        }
    }),
    initialize: function(App) {
        App.Session.set('user', new App.Module.User.Model());
    }
});

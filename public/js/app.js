"use strict";

var App = {
    Module: {
        User: {
            Model: Backbone.Model.extend({
                url: BASE_URL + '/login',
                defaults: {
                    user: null,
                    client: null,
                    token: null,
                    loggedIn: false
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
                    this.set(data);
                },
                signin: function(user, password) {
                    var that = this;

                    Backbone.$.ajax({
                        url: BASE_URL + '/login',
                        dataType: 'json',
                        headers: {
                            'AUTH_USER': user,
                            'AUTH_PASS': password,
                            'AUTH_CLIENT': this.get('client')
                        },
                        success: function(data, textStatus, jqXHR) {
                            var token = jqXHR.getResponseHeader('NEXT_AUTH_TOKEN');

                            if (token) {
                                that.set({
                                    user: user,
                                    token: token,
                                    loggedIn: true
                                });
                                localStorage['user'] = user;
                                localStorage['token'] = token;
                            }
                        }
                    });
                },
                signout: function() {
                    var that = this;

                    Backbone.$.ajax({
                        url: BASE_URL + '/logout',
                        dataType: 'json',
                        data: {
                            user: this.get('user'),
                            client: this.get('client')
                        },
                        headers: {
                            'AUTH_USER': this.get('user'),
                            'AUTH_CLIENT': this.get('client'),
                            'AUTH_TOKEN': this.get('token')
                        },
                        success: function(data, textStatus, jqXHR) {
                            localStorage.removeItem('user');
                            localStorage.removeItem('token');
                            that.set({ user: null, token: null, loggedIn: false });
                        }
                    });
                }
            })
        }
    },
    Views: {
        Main: Backbone.View.extend({
            el: '#application',
            events: {
                'submit #login-form': 'login'
            },
            options: {
                template: '#tpl-application',
                templateLogin: '#tpl-login'
            },
            initialize: function() {
                if (this.model) {
                    this.model.on('change:loggedIn', this.render, this);
                }
            },
            render: function() {
                var loggedIn = this.model && this.model.get('loggedIn'),
                    html = '';

                if (loggedIn) {
                    html = App.render(this.options.template);
                } else {
                    html = App.render(this.options.templateLogin);
                    Backbone.history.stop();
                }

                if (html) {
                    this.$el.html(html);
                    if (loggedIn) {
                        var hooks = App.Session.get('hooks');
                        if (hooks) {
                            hooks.call('after-login');
                            Backbone.history.start();
                        }
                    }
                }

                return this;
            },
            remove: function() {
                this.undelegateEvents();
                return this;
            },
            login: function(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                var data = App.Module.Form.Serialize(this.$(e.currentTarget)),
                    user = App.Session.get('user');

                user.signin(data.username, data.password);
            }
        })
    },
    Session: function () {
        var store = {};

        return {
            /**
             * Set a key-value-pair to session
             * @param key, string
             * @param value, anything
             * @return {*}
             */
            set: function (key, value) {
                if (key === undefined) throw 'No key given to add(key, value) function';

                store[key] = value;

                return value;
            },
            /**
             * Get the value with the given key from the session
             * @param key, string
             * @param callback, function to auto create a value if key not exists
             * @return {*}, value or the whole session
             */
            get: function (key, callback) {
                if (key) {
                    if (store[key]) {
                        return store[key];
                    }

                    if (callback && typeof callback == 'function') {
                        return this.set(key, callback());
                    }
                    return undefined;
                } else {
                    return store;
                }
            },
            /**
             * Check if the key exists
             * @param key
             * @return {Boolean}
             */
            has: function (key) {
                return (key && store[key] !== undefined);
            },
            /**
             * remove the key when key given or the whole session will be cleaned
             * @param key, string, optional
             */
            remove: function (key) {
                if (key && this.has(key)) {
                    delete store[key];
                } else {
                    store = {};
                }
            }
        };
    }(),
    notify: function(message, type, delay) {
        var template = App.render("#tpl-application-notification");
        if (template) {
            var data = {message: message, type: ' alert-info'};
            if (type !== undefined) {
                data.type = ' alert-' + type;
            }
            if (delay === undefined) {
                delay = 2000;
            }

            var $el = $(template(data).trim());
            if (delay) {
                $el.delay(2000).promise().done(function () {
                    $el.fadeOut('slow', function() {
                        $el.detach().remove();
                    });
                });
            }
            $('#notification').prepend($el);
        }
    },
    render: function(name, data) {
        var templates = this.Session.get('templates', function() {
            return {}
        });

        if (data && !data.App) {
            data.App = this;
        }

        if (templates[name]) {
            return (data) ? templates[name](data) : templates[name];
        }

        var html = $(name).html();
        if (html) {
            templates[name] = _.template(html);
            return (data) ? templates[name](data) : templates[name];
        }

        return undefined;
    },
    router: new Backbone.Router(),
    switchView: function(section, name, callback) {
        if (section && name && callback) {
            var currentName = App.Session.get(section + '-name');

            if (!currentName || currentName !== name) {
                if (App.Session.has(section)) {
                    App.Session.get(section).remove();
                }
                var view = callback();
                view.render();
                App.Session.set(section, view);
                App.Session.set(section + '-name', name);
            }
        }
    },
    selectMenuItem: function(item) {
        var items = App.Session.get('selected-menu-items', function() { return [] });

        if (item) {
            if (_.isString(item)) {
                items.push(item);
            } else if (_.isArray(item)) {
                items = $.merge(items, item);
            }
        }

        $('.menu-item').removeClass('active').each(function(idx, el) {
           $(el).parent('li').removeClass('active');
        });


        for (var i=0; i<items.length; i++) {
            var $item = $(items[i]),
                parent = $item.parent('li');

            if (parent) {
                parent.addClass('active');
            } else {
                $item.addClass('active');
            }
        }

        App.Session.set('selected-menu-items', items);
    }
};
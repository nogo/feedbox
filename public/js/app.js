"use strict";

var App = {
    Module: {},
    Views: {},
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
    notifier: function() {
        return {
            add: function (message, type, delay) {
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
                    var queue = App.Session.get('notify-queue', function() { return [] });
                    queue.push($el);
                }
            },
            show: function(el) {
                var queue = App.Session.get('notify-queue', function() { return [] }),
                    header = $(el);
                if (queue.length > 0 && header) {
                    while (queue.length > 0) {
                        header.prepend(queue.pop());
                    }
                }
            }
        }
    }(),
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
    switchView: function(name, view) {
        if (name && view) {
            if (App.Session.has(name)) {
                App.Session.get(name).remove();
            }
            view.render();
            App.Session.set(name, view);
            this.notifier.show('#content');
        }
    }
};

"use strict";

/**
 * Nerve - application framework on to of Backbone
 *
 * @version 0.1.0
 */
var Nerve = (function(global, Backbone, _) {
    var Nerve = Backbone.Nerve = {};

    Nerve.$ = Backbone.$;
    Nerve.VERSION = '0.1.0';
    Nerve.extend = Backbone.Model.extend;

    /**
     * Nerve.Store
     * @param options
     * @constructor
     */
    Nerve.Store = function (options) {
        this.store = {};
        _.extend(this, options);
    };

    _.extend(Nerve.Store.prototype, {
        /**
         * Set a key-value-pair to session
         * @param key, string
         * @param value, anything
         * @return {*}
         */
        set: function (key, value) {
            if (key === undefined) throw 'No key given to add(key, value) function';

            this.store[key] = value;

            return value;
        },
        /**
         * Get the value with the given key from the session
         * @param key, string
         * @param defaultValue function to auto create a value if key not exists
         * @return {*}, value or the whole session
         */
        get: function (key, defaultValue) {
            if (key) {
                if (this.store[key]) {
                    return this.store[key];
                }

                if (defaultValue) {
                    if (_.isFunction(defaultValue)) {
                        return this.set(key, defaultValue());
                    } else {
                        return this.set(key, defaultValue);
                    }
                }
                return undefined;
            } else {
                return this.store;
            }
        },
        /**
         * Check if the key exists
         * @param key
         * @return {Boolean}
         */
        has: function (key) {
            return (key && this.store[key] !== undefined);
        },
        /**
         * remove the key when key given or the whole session will be cleaned
         * @param key, string, optional
         */
        remove: function (key) {
            if (key && this.has(key)) {
                delete this.store[key];
            } else {
                this.store = {};
            }
        }
    });


    /**
     * Nerve.Hook
     * @returns {this.Collection}
     * @constructor
     */
    Nerve.Hook = function() {
        return new this.Collection();
    };

    _.extend(Nerve.Hook.prototype, {
        Model: Backbone.Model.extend({
            defaults: {
                weight: 0,
                scope: 'default'
            }
        }),
        Collection: Backbone.Collection.extend({
            Model: Nerve.Hook.Model,
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
        })
    });

    /**
     * Nerve Module
     * @param options
     * @constructor
     */
    Nerve.Module = function(options) {
        _.extend(this, options);
    };

    _.extend(Nerve.Module, {
        Views: {},
        initialize: function(App) {}
    });


    /**
     * Nerve Application
     * @param options
     * @constructor
     */
    Nerve.Application = function(options) {
        _.extend(this, options);
    };

    _.extend(Nerve.Application.prototype, {
        Helper: {},
        Module: {},
        Views: {},
        Session: new Nerve.Store(),
        Hook: new Nerve.Hook(),
        Router: new Backbone.Router(),
        initialize: function() {
           var modules = this.Module || {};
           for (var name in modules) {
                if (modules.hasOwnProperty(name)) {
                    if (modules[name].initialize) {
                        modules[name].initialize(this);
                    }
                }
            }
            this.Hook.call('initialize');
        },
        notify: function(message, type, delay) {
            var template = this.template("#tpl-application-notification");
            if (template) {
                var data = {message: message, type: ' alert-info'};
                if (type !== undefined) {
                    data.type = ' alert-' + type;
                }
                if (delay === undefined) {
                    delay = 2000;
                }

                var $el = Nerve.$(template(data).trim());
                if (delay) {
                    $el.delay(2000).promise().done(function () {
                        $el.fadeOut('slow', function() {
                            $el.detach().remove();
                        });
                    });
                }
                Nerve.$('#notification').prepend($el);
            }
        },
        renderTemplate: function(el, name, data) {
            if (el && name) {
                var html = this.template(name, data);
                if (html) {
                    // fill model date into template and push it into element html
                    Nerve.$(el).html(html);
                    return true;
                }
            }
            return false;
        },
        renderView: function(name, view) {
            if (name && view) {
                this.Session.set(name, view);
                return view.render();
            }
            return view;
        },
        switch: function(section, name, callback) {
            if (section && name && callback) {
                var currentName = this.Session.get(section + '-name');

                if (!currentName || currentName !== name) {
                    if (this.Session.has(section)) {
                        this.Session.get(section).remove();
                    }
                    this.renderView(section, callback());
                    this.Session.set(section + '-name', name);
                }
            }
        },
        selectMenuItem: function(item) {
            var items = this.Session.get('selected-menu-items', function() { return [] });

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

            this.Session.set('selected-menu-items', items);
        },
        template: function(name, data) {
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
        }
    });

    return Nerve;
})(this, Backbone, _);

'use strict';

/**
 * App.Views.ListItem
 * @type {*}
 */
App.Views.ListItem = Backbone.View.extend({
    render: function () {
        // grep template with jquery and generate template stub
        var html = App.render(this.options.template, { model: this.model });
        if (html) {
            // fill model date into template and push it into element html
            this.$el.html(html);

            // add element id with prefix
            this.$el.attr('id', this.elId());
        }

        return this;
    },
    remove: function () {
        this.undelegateEvents();
        this.$el.detach().remove();
        return this;
    },
    elId: function () {
        var id = this.model.get('id');
        return (this.options.prefix) ? this.options.prefix + id : id;
    }
});

/**
 * App.Views.List
 * @type {*}
 */
App.Views.List = Backbone.View.extend({
    options: {
        prefix: '',
        item: {
            attributes: {},
            tagName: 'div',
            template: '',
            View: App.Views.ListItem
        }
    },
    initialize: function () {
        // ListItems object
        this.items = {};

        // Assign function to collection events
        if (this.collection) {
            this.collection.on('reset', this.addAll, this);
            this.collection.on('sort', this.addAll, this);
            this.collection.on('add', this.addItem, this);
            this.collection.on('change', this.changeItem, this);
            this.collection.on('remove', this.removeItem, this);
        }
    },
    render: function () {
        if (this.options.templateEl) {
            this.setElement(this.options.templateEl);
        }

        if (this.collection && this.collection.length > 0) {
            this.addAll();
        }

        return this;
    },
    remove: function () {
        for (var name in this.items) {
            if (this.items.hasOwnProperty(name)) {
                this.items[name].remove();
            }
        }
        this.items = {};

        if (this.collection) {
            this.collection.off('reset', this.addAll, this);
            this.collection.off('sort', this.addAll, this);
            this.collection.off('add', this.addItem, this);
            this.collection.off('change', this.changeItem, this);
            this.collection.off('remove', this.removeItem, this);
        }
        return this;
    },
    addAll: function () {
        var that = this;

        // remove exiting models
        if (this.items) {
            for (var name in this.items) {
                if (this.items.hasOwnProperty(name)) {
                    this.items[name].remove();
                }
            }
        }

        // run addItem on each collection item
        if (this.collection && this.collection.length > 0) {
            if (this.options.isEmpty) {
                this.$el.html('');
                this.options.isEmpty = false;
            }

            var items = [];
            this.collection.each(function (model) {
                var view = that.renderItem(model);
                that.items[model.id] = view;
                items.push(view.el);
            });

            this.$el.append(items);
        } else if (this.options.emptyTemplate && this.$el.is(":empty")) {
            var emptyTemplate = App.render(this.options.emptyTemplate);
            this.$el.html(emptyTemplate());
            this.options.isEmpty = true;
        }
        return this;
    },
    addItem: function (model) {
        if (this.options.isEmpty) {
            this.$el.html('');
            this.options.isEmpty = false;
        }

        var view = this.renderItem(model);
        this.items[model.id] = view;
        this.$el.append(view.el);

        return this;
    },
    changeItem: function (model) {
        if (model.id !== undefined) {
            var oldView = this.items[model.id],
                newItem = this.renderItem(model);

            oldView.$el.after(newItem.el);
            oldView.remove();
            this.items[model.id] = newItem;
        } else { // run addAll if item has no Id
            this.addAll();
        }
        return this;
    },
    renderItem: function (model) {
        return new this.options.item.View({
            model: model,
            collection: this.collection,
            prefix: this.options.prefix,
            attributes: this.options.item.attributes,
            tagName: this.options.item.tagName,
            template: this.options.item.template
        }).render();
    },
    removeItem: function (model) {
        if (model.id !== undefined && this.items[model.id]) {
            this.items[model.id].remove();
        }
        return this;
    }
});

App.Module.Object = {
    'Set': function(obj, path, value) {
        if (_.isString(path)) {
            path = path.split('.');
        }
        var parent = obj,
            len = path.length;

        for (var i=0; i<len; i++) {
            var name = path[i];
            if (i >= len - 1) {
                if (name.search(/\[\]/) !== -1) {
                    name = name.replace('[]', '');
                    if (parent[name] === undefined) {
                        parent[name] = [];
                    }
                    parent[name].push(value);
                } else {
                    parent[name] = value;
                }
            } else if (parent[name] === undefined) {
                parent[name] = {};
            }
            parent = parent[name];
        }

        return obj;
    },
    'Get': function(obj, path) {
        if (obj && path) {
            if (_.isString(path)) {
                path = path.split('.');
            }

            var parent = obj;
            for (var i=0; i<path.length; i++) {
                var name = path[i].replace('[]', '');
                if (parent[name]) {
                    parent = parent[name];
                } else {
                    parent = undefined;
                    break;
                }
            }

            return parent;
        }
        return undefined;
    }
};


App.Module.Form = {
    Bind: function($form, data, ignore) {
        ignore = ignore || {};

        $(':input[name]', $form).each(function (idx, input) {
            var $input = $(input),
                name = input.name;

            if (name && !ignore[name]) {
                var parts = name.split('-'),
                    value = App.Module.Object.Get(data, parts);

                switch (input.type) {
                    case 'checkbox':
                        input.checked = (value !== undefined);
                        break;
                    case 'radio':
                        input.checked = ($input.val() == value);
                        break;
                    default:
                        $input.val(value);
                }
                $input.trigger('change');
            }
        });
    },
    Serialize: function($form, ignore, withoutEmpty) {
        ignore = ignore || {};
        var data = {},
            component = {};

        // Grep inputs with name
        $(':input[name]', $form).each(function (idx, input) {
            var $input = $(input),
                name = input.name;

            if (name) {
                if (component[name]) {
                    return;
                }

                var val = $input.val(),
                    parts = name.split('-');

                switch(input.type) {
                    case 'checkbox':
                        if (input.checked) {
                            if (val === undefined || _.isEmpty(val)) {
                                val = true;
                            }
                        } else {
                            val = false;
                        }
                        break;
                    case 'radio':
                        if (input.checked) {
                            component[name] = true;
                            if (val === undefined || _.isEmpty(val)) {
                                val = true;
                            }
                        } else {
                            val = false;
                        }
                        break;
                    case 'button': // button values are not intresting
                        return;
                }

                if (withoutEmpty) {
                    if (val === undefined || _.isEmpty(val)) {
                        return;
                    }
                }

                App.Module.Object.Set(data, parts, val);
            }
        });

        return data;
    }
};

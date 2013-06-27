"use strict";

App.Module.Setting = {
    Model: Backbone.Model.extend({}),
    Views: {
        Main: Backbone.View.extend({
            template: '#tpl-settings',
            isBinding: false,
            tab: 'view',
            events: {
                'change .update': 'changeSetting'
            },
            initialize: function () {
                if (this.options.tab) {
                    this.tab = this.options.tab;
                }

                this.isBinding = false;
                this.tags = App.Session.get('tag-collection');
                this.sources = App.Session.get('source-collection');
                this.settings = App.Session.get('setting-collection');
            },
            render: function() {
                var html = App.render(this.template, { model: this.model });
                if (html) {
                    // fill model date into template and push it into element html
                    this.$el.html(html);
                }

                this.isBinding = true;
                App.Module.Form.Bind(this.$el, this.settings.mapped(), {silent: true});
                this.isBinding = false;

                var tagList = new App.Views.List({
                    prefix: 'tag-',
                    tagName: 'tbody',
                    collection: this.tags,
                    item: {
                        tagName: 'tr',
                        template: '#tpl-setting-tag',
                        View: App.Module.Tag.Views.Item
                    }
                });
                this.$('#setting-tags').replaceWith(tagList.render().el);

                var sourceList = new App.Views.List({
                    prefix: 'source-',
                    collection: this.sources,
                    item: {
                        attributes: {
                            'class': 'box'
                        },
                        tagName: 'div',
                        template: '#tpl-source',
                        View: App.Module.Source.Views.Item
                    }
                });
                this.$('#tab-setting-sources').html(sourceList.render().el);

                this.activateTab(this.tab);

                return this;
            },
            activateTab: function(name) {
                var nav = this.$('.nav');
                nav.find('.active').removeClass('active');
                nav.find("a[href='#settings/"+ name + "']").parent('li').addClass('active');

                this.$('.tab-content .tab-pane.active').removeClass('active');
                this.$('#tab-setting-' + name).addClass('active');

            },
            remove: function() {
                this.undelegateEvents();
                return this;
            },
            changeSetting: function(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                var component = e.currentTarget,
                    name = component.name,
                    value = component.value;

                if (!this.isBinding && this.settings) {
                    var options = {
                        wait: true,
                        success: function (model) {
                            App.notify("Value has been saved", "success");
                        },
                        error: function (model, response, scope) {
                            App.notify(response.status + ": " + response.statusText, "error");
                        }
                    }

                    var setting = this.settings.where({ key: name });
                    if (setting.length >= 1) {
                        setting[0].save({ 'value': value }, options);
                    } else {
                        this.settings.create({
                            key: name,
                            value: value
                        }, options);
                    }
                }
            }
        })
    },
    initialize: function (App) {
        var settings = new App.Module.Setting.Collection();
        settings.fetch();
        App.Session.set('setting-collection', settings);
    }
};

App.Module.Setting.Collection = Backbone.Collection.extend({
    model: App.Module.Setting.Model,
    url: BASE_URL + '/settings',
    getByKey: function(key, default_value) {
        var value = default_value,
            settings = this.where({ key: key });

        if (settings.length > 0) {
            value = settings[0].get('value');
        }
        return value;
    },
    mapped: function(keypart) {
        var result = {};
        this.each(function(model) {
            var key = model.get('key');
            if (keypart) {
                if (key.search(keypart) != -1) {
                    result[key] = model.get('value');
                }
            } else {
                result[key] = model.get('value');
            }
        });
        return result;
    }
});

App.Module.Setting.initialize(App);

/* Routes */

App.router.route('settings(/:name)', function (name) {
    name = name || 'view';

    if (App.Session.get('content-view-name') === 'settings-view') {
        var view = App.Session.get('content-view');
        view.activateTab(name);
    } else {
        App.switchView('content-view', 'settings-' + name, function () {
            return new App.Module.Setting.Views.Main({
                el: '#content',
                tab: name
            });
        });

        App.Session.set('selected-menu-items', ['.menu-item-settings']);
        App.selectMenuItem();
        App.Session.get('footer-view').hide();
    }
});
"use strict";

FeedBox.Module.Setting = new Nerve.Module({
    Model: Backbone.Model.extend({}),
    Views: {
        Main: Backbone.View.extend({
            template: '#tpl-settings',
            isBinding: false,
            tab: 'view',
            events: {
                'change .update': 'changeSetting',
                'click #user-password': 'changePassword'
            },
            initialize: function () {
                if (this.options.tab) {
                    this.tab = this.options.tab;
                }

                this.isBinding = false;
                this.tags = FeedBox.Session.get('tag-collection');
                this.sources = FeedBox.Session.get('source-collection');
                this.settings = FeedBox.Session.get('setting-collection');
            },
            render: function() {
                FeedBox.renderTemplate(this.$el, this.template, { model: this.model });

                this.isBinding = true;
                FeedBox.Helper.Form.Bind(this.$el, this.settings.mapped(), {silent: true});
                this.isBinding = false;

                var tagList = new FeedBox.Views.List({
                    prefix: 'tag-',
                    tagName: 'tbody',
                    collection: this.tags,
                    item: {
                        tagName: 'tr',
                        template: '#tpl-setting-tag',
                        View: FeedBox.Module.Tag.Views.Item
                    }
                });
                this.$('#setting-tags').replaceWith(tagList.render().el);

                var sourceList = new FeedBox.Views.List({
                    prefix: 'source-',
                    collection: this.sources,
                    item: {
                        attributes: {
                            'class': 'box'
                        },
                        tagName: 'div',
                        template: '#tpl-source',
                        View: FeedBox.Module.Source.Views.Item
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
                            FeedBox.notify("Value has been saved", "success");
                        },
                        error: function (model, response, scope) {
                            FeedBox.notify(response.status + ": " + response.statusText, "error");
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
            },
            changePassword: function(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }
        })
    },
    initialize: function (App) {
        var settings = new App.Module.Setting.Collection();

        App.Session.set('setting-collection', settings);
        App.Hook.add({
            scope: 'render-app-view',
            weigth: -10,
            callback: function() {
                settings.fetch();
            }
        });
    }
});


FeedBox.Module.Setting.Collection = Backbone.Collection.extend({
    model: FeedBox.Module.Setting.Model,
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

/* Routes */

FeedBox.Router.route('settings(/:name)', function (name) {
    name = name || 'view';

    if (FeedBox.Session.get('content-view-name') === 'settings-view') {
        var view = FeedBox.Session.get('content-view');
        view.activateTab(name);
    } else {
        FeedBox.switch('content-view', 'settings-' + name, function () {
            return new FeedBox.Module.Setting.Views.Main({
                el: '#content',
                tab: name
            });
        });

        FeedBox.Session.set('selected-menu-items', ['.menu-item-settings']);
        FeedBox.selectMenuItem();
        FeedBox.Session.get('footer-view').hide();
    }
});
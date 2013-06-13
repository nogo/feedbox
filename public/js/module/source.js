"use strict";

App.Module.Source = {
    Model: Backbone.Model.extend({
        hasErrors: function () {
            return !_.isEmpty(this.get('errors'));
        }
    }),
    Views: {
        Item: App.Views.ListItem.extend({
            events: {
                'click .activate': 'activateItem',
                'click .update': 'updateItem',
                'click .delete': 'deleteItem'
            },
            render: function () {
                // Call parent contructor
                App.Views.ListItem.prototype.render.call(this);

                if (this.model) {
                    var errors = this.model.get('errors');
                    if (_.isEmpty(errors)) {
                        this.$el.removeClass('has-errors');
                    } else {
                        this.$el.addClass('has-errors');
                    }

                    var active = this.model.get('active');
                    if (active) {
                        this.$el.removeClass('muted');
                        this.$('.activate').addClass('active');
                    } else {
                        this.$el.addClass('muted');
                        this.$('.activate').removeClass('active');
                    }
                }

                return this;
            },
            activateItem: function (e) {
                if (e) {
                    e.preventDefault();
                }

                if (this.model) {
                    var value = this.model.get('active');

                    if (value == 0) {
                        this.model.save({ active: 1 });
                    } else {
                        this.model.save({ active: 0 });
                    }
                }
            },
            updateItem: function (e) {
                if (e) {
                    e.preventDefault();
                }

                if (this.model) {
                    var that = this;
                    Backbone.ajax({
                        url: BASE_URL + '/update/' + this.model.id,
                        cache: false,
                        dataType: 'json',
                        success: function (data, status, xhr) {
                            App.notifier.add(that.model.get('name') + " - Source update successfull.", "success");
                            that.model.set(data);
                            App.notifier.show('#content');
                        },
                        error: function (xhr, status, errors) {
                            App.notifier.add(that.model.get('name') + " - Source update failed.", "error");
                            if (xhr.responseText) {
                                that.model.set(jQuery.parseJSON(xhr.responseText));
                            }
                            App.notifier.show('#content');
                        }
                    });
                }
            },
            deleteItem: function (e) {
                if (e) {
                    e.preventDefault();
                }

                if (this.model) {
                    this.model.destroy();
                }
            }
        }),
        Form: Backbone.View.extend({
            template: '#tpl-source-form',
            events: {
                'click .save': 'save',
                'click .cancel': 'close',
                'submit': 'save'
            },
            initialize: function () {
            },
            render: function () {
                // grep template with jquery and generate template stub
                var html = App.render(this.template, { model: this.model });
                if (html) {
                    // fill model date into template and push it into element html
                    this.$el.html(html);
                }

                if (this.model && this.model.isNew()) {
                    this.$('legend').text('Add source')
                }

                App.Module.Form.Bind(this.$el, this.model.toJSON(), this.ignore);

                return this;
            },
            save: function (e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                if (this.model) {
                    var that = this,
                        data = App.Module.Form.Serialize(this.$el),
                        isNew = this.model.isNew(),
                        options = {
                            wait: true,
                            success: function (model) {
                                App.notifier.add(data.name + " - All data are saved properly.", "success");
                                that.close();
                            },
                            error: function (model, response, scope) {
                                $('.save i.icon').remove();
                                $('.cancel').removeAttr('disabled');
                                App.notifier.add(response.status + ": " + response.statusText, "error");
                                App.notifier.show();
                            }
                        };

                    this.$('.save').prepend('<i class="loading"></i> ');
                    this.$('.cancel').attr('disabled', 'disabled');

                    if (this.model.isNew()) {
                        this.collection.create(data, options);
                    } else {
                        this.model.save(data, options);
                    }
                }
            },
            close: function (e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                App.router.navigate('sources', { trigger: true });
            },
            remove: function () {
                this.undelegateEvents();
                this.$el.html('');
            }
        })
    },
    initialize: function (App) {
        App.Session.set('source-collection', new App.Module.Source.Collection());
    }
};

App.Module.Source.Views.List = App.Views.List.extend({
    el: '#content-list',
    options: {
        prefix: 'source-',
        bottom: 20,
        emptyTemplate: '#tpl-empty',
        item: {
            attributes: {
                'class': 'box'
            },
            tagName: 'div',
            template: '#tpl-source',
            View: App.Module.Source.Views.Item
        }
    },
    render: function () {
        // Call parent contructor
        App.Views.List.prototype.render.call(this);

        return this;
    }
});

App.Module.Source.Collection = Backbone.Collection.extend({
    model: App.Module.Source.Model,
    url: BASE_URL + '/sources',
    comparator: function (model) {
        return model.get('name');
    }
});
App.Module.Source.initialize(App);
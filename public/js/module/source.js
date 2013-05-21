"use strict";

App.Module.Source = {
    Model: Backbone.Model.extend({
        urlRoot: 'api.php/sources',
        hasErrors: function() {
            return !_.isEmpty(this.get('errors'));
        }
    }),
    Views: {
        Item: App.Views.ListItem.extend({
            render: function() {
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
                    } else {
                        this.$el.addClass('muted');
                    }
                }

                return this;
            }
        }),
        Form: Backbone.View.extend({
            template: '#tpl-source-form',
            events: {
                'click .save': 'save',
                'click .cancel': 'close',
                'submit': 'save'
            },
            initialize: function () {},
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
            save: function(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                if (this.model) {
                    var that = this,
                        data = App.Module.Form.Serialize(this.$el),
                        isNew = this.model.isNew();

                    this.$('.save').append(' <i class="icon loading-14-white"></i>');
                    this.$('.cancel').attr('disabled', 'disabled');

                    this.model.save(data, {
                        wait: true,
                        success: function(model) {
                            App.notifier.add("Nice, all data are saved properly.", "success");
                            if (isNew) {
                                that.collection.add(model);
                            }
                            that.close();
                        },
                        error: function(model, response, scope) {
                            $('.save i.icon').remove();
                            $('.cancel').removeAttr('disabled');
                            App.notifier.add(response.status + ": " + response.statusText, "error");
                            App.notifier.show();
                        }
                    });
                }
            },
            close: function(e) {
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
    render: function() {
        // Call parent contructor
        App.Views.List.prototype.render.call(this);

        var position = this.$el.position(),
            height = $(window).height() - this.options.bottom;

        if (position) {
            height -= position.top;
        }
        this.$el.height(height);

        return this;
    }
});

App.Module.Source.Collection = Backbone.Collection.extend({
    model: App.Module.Source.Model,
    url: 'api.php/sources',
    comparator: function(model) {
        return model.get('name');
    }
});
App.Module.Source.initialize(App);
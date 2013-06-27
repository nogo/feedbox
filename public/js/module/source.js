"use strict";

App.Module.Source = {
    Model: Backbone.Model.extend({
        hasErrors: function () {
            return !_.isEmpty(this.get('errors'));
        },
        parse: function (response, options) {
            var tags = App.Session.get('tag-collection');
            if (tags) {
                if (response.tag_id) {
                    response.tag = tags.get(response.tag_id);
                }
            }
            return response;
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
                            App.notify(that.model.get('name') + " - Source update successfull.", "success");
                            that.model.set(data);
                        },
                        error: function (xhr, status, errors) {
                            App.notify(that.model.get('name') + " - Source update failed.", "error");
                            if (xhr.responseText) {
                                that.model.set(jQuery.parseJSON(xhr.responseText));
                            }
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
                'change #source-tag': 'addNewTag',
                'submit': 'save'
            },
            initialize: function () {
                this.tags = App.Session.get('tag-collection');
                this.tag = this.model.get('tag');
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

                App.Module.Form.Bind(this.$el, this.model.toJSON(), {silent: true});

                // render tags
                var tagname = this.$('#source-tag');
                if (this.tag) {
                    tagname.val(this.tag.get('name'));
                }
                tagname.typeahead({
                    source: this.tags.pluck("name")
                });

                return this;
            },
            remove: function () {
                this.undelegateEvents();
                this.$el.html('');
            },
            save: function (e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                if (this.model) {
                    var that = this,
                        data = App.Module.Form.Serialize(this.$el)

                    if (!_.isEmpty(data['tag_name'])) {
                        var tag = this.tags.findWhere({name: this.$('#source-tag').val()});
                        if (tag) {
                            data['tag_id'] = tag.id;
                        }
                    } else {
                        data['tag_id'] = null;
                    }

                    this.$('.save').prepend('<i class="icon loading"></i> ');
                    this.$('.cancel').attr('disabled', 'disabled');

                    var options = {
                        wait: true,
                        success: function (model) {
                            App.notify(model.get('name') + " - All data are saved properly.", "success");
                            var collection = App.Session.get('tagless-source-collection');
                            if (data['tag_id']) {
                                model.get('tag').sources().add(model);
                                collection.remove(model);
                            } else {
                                this.tag.sources().remove(model);
                                collection.add(model);
                            }
                            that.close();
                        },
                        error: function (model, response, scope) {
                            $('.save i.icon').remove();
                            $('.cancel').removeAttr('disabled');
                            App.notify(response.status + ": " + response.statusText, "error");
                        }
                    };


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

                App.router.navigate('settings/sources', { trigger: true });
            },
            addNewTag: function(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                var component = e.currentTarget,
                    tagname = component.value;

                if (!_.isEmpty(tagname)) {
                    var tag = this.tags.findWhere({ name: tagname });
                    if (!tag) {
                        this.tags.create({ name: tagname }, {
                            wait: true,
                            success: function (model) {
                                App.notify("Tag created.", "success");
                            },
                            error: function (model, response, scope) {
                                App.notify("Tag could not be created.", "error");
                            }
                        });
                    }
                }
            }
        })
    },
    initialize: function (App) {
        App.Session.set('source-collection', new App.Module.Source.Collection());
    }
};

App.Module.Source.Views.List = App.Views.List.extend({
    el: '#content',
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
        return model.get('name').toLowerCase();
    }
});
App.Module.Source.initialize(App);

/* Routes */

App.router.route('sources/:id', function(id) {
    var items = App.Session.get('item-collection'),
        settings = App.Session.get('setting-collection');

    App.switchView('content-view', 'item-list', function() {
        return new App.Module.Item.Views.List({
            collection: items
        });
    });

    if (items) {
        var data = App.Session.get('item-collection-data', function() {
                return {
                    unread: true,
                    page: 1,
                    limit: settings.getByKey('view.unread.count', 50),
                    sortby: settings.getByKey('view.unread.sortby', 'newest')
                };
            }),
            selectedMenuItem = ['.menu-item-source-' + id];

        if (data.unread) {
            selectedMenuItem.push('.menu-item-unread');
        } else if (!data.unread) {
            selectedMenuItem.push('.menu-item-read');
        } else if (data.starred) {
            selectedMenuItem.push('.menu-item-starred');
        }

        data.page = 1;
        data.source = id;
        if (data.tag) {
            delete data.tag;
        }

        App.Session.set('item-collection-data', data);
        items.fetch({
            reset: true,
            data: data
        });
        App.Session.set('selected-menu-items', selectedMenuItem);
        App.selectMenuItem();
    }
});

App.router.route('sources/add', function() {
    var sources = App.Session.get('source-collection'),
        model = new App.Module.Source.Model();

    App.switchView('content-view', 'source-add', function() {
        return new App.Module.Source.Views.Form({
            el: '#content',
            model: model,
            collection: sources
        });
    });
    App.Session.get('footer-view').hide();
});

App.router.route('sources/:id/edit', function(id) {
    var sources = App.Session.get('source-collection'),
        model = sources.get(id);

    if (model) {
        App.switchView('content-view', 'source-edit', function() {
            return new App.Module.Source.Views.Form({
                el: '#content',
                model: model,
                collection: sources
            });
        });
        App.Session.get('footer-view').hide();
    } else {
        App.notify("Source not found", "error");
        App.router.navigate('settings/sources', { trigger: true });
    }
});

App.router.route('sources/update', function() {
    var sources = App.Session.get('source-collection');

    App.notify("Update started.", "success");
    Backbone.ajax({
        url: BASE_URL + '/update',
        cache: false,
        dataType: 'json',
        success: function(models, textStatus, jqXHR) {
            sources.set(models);
            App.notify("Update successfull.", "success");
        },
        error: function() {
            App.notify("Update failed.", "error");
        }
    });
});

App.router.route('sources/:id/update', function(id) {
    var sources = App.Session.get('source-collection'),
        model = sources.get(id);

    if (model) {
        Backbone.ajax({
            url: BASE_URL + '/update/' + model.id,
            dataType: 'json',
            success: function(modelData, textStatus, jqXHR) {
                model.set(modelData);
                App.notify(model.get('name') + " - Source update successfull.", "success");

            },
            error: function() {
                App.notify(model.get('name') + " - Source update failed.", "error");
            }
        });
    }

    App.router.navigate('settings/sources', { trigger: true });
});

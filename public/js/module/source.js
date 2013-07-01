"use strict";

FeedBox.Module.Source = new Nerve.Module({
    Model: Backbone.Model.extend({
        hasErrors: function () {
            return !_.isEmpty(this.get('errors'));
        },
        parse: function (response, options) {
            var tags = FeedBox.Session.get('tag-collection');
            if (tags) {
                if (response.tag_id) {
                    response.tag = tags.get(response.tag_id);
                }
            }
            return response;
        },
        color: function() {
            var color = undefined,
                tag = this.get('tag');

            if (tag) {
                color = tag.get('color');
            }

            return color;
        }
    }),
    Views: {
        Item: FeedBox.Views.ListItem.extend({
            events: {
                'click .activate': 'activateItem',
                'click .update': 'updateItem',
                'click .delete': 'deleteItem'
            },
            render: function () {
                // Call parent contructor
                FeedBox.Views.ListItem.prototype.render.call(this);

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
                    var that = this,
                        user = FeedBox.Session.get('user');
                    FeedBox.notify(that.model.get('name') + " - Source start update.", "info");
                    Backbone.ajax({
                        url: BASE_URL + '/update/source/' + this.model.id,
                        cache: false,
                        dataType: 'json',
                        headers: user.accessHeader(),
                        success: function (data, status, xhr) {
                            FeedBox.notify(that.model.get('name') + " - Source update successfull.", "success");
                            that.model.set(data);
                        },
                        error: function (xhr, status, errors) {
                            FeedBox.notify(that.model.get('name') + " - Source update failed.", "error");
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
                this.tags = FeedBox.Session.get('tag-collection');
                this.tag = this.model.get('tag');
            },
            render: function () {
                FeedBox.renderTemplate(this.$el, this.template, { model: this.model });

                if (this.model && this.model.isNew()) {
                    this.$('legend').text('Add source')
                }

                FeedBox.Helper.Form.Bind(this.$el, this.model.toJSON(), {silent: true});

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
                        data = FeedBox.Helper.Form.Serialize(this.$el)

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
                            FeedBox.notify(model.get('name') + " - All data are saved properly.", "success");
                            var collection = FeedBox.Session.get('tagless-source-collection');
                            if (data['tag_id']) {
                                model.get('tag').sources().add(model);
                                collection.remove(model);
                            } else {
                                that.tag.sources().remove(model);
                                collection.add(model);
                            }
                            that.close();
                        },
                        error: function (model, response, scope) {
                            $('.save i.icon').remove();
                            $('.cancel').removeAttr('disabled');
                            FeedBox.notify(response.status + ": " + response.statusText, "error");
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

                FeedBox.Router.navigate('settings/sources', { trigger: true });
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
                                FeedBox.notify("Tag created.", "success");
                            },
                            error: function (model, response, scope) {
                                FeedBox.notify("Tag could not be created.", "error");
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
});

FeedBox.Module.Source.Collection = Backbone.Collection.extend({
    model: FeedBox.Module.Source.Model,
    url: BASE_URL + '/sources',
    comparator: function (model) {
        return model.get('name').toLowerCase();
    }
});

FeedBox.Module.Source.Views.List = FeedBox.Views.List.extend({
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
            View: FeedBox.Module.Source.Views.Item
        }
    }
});

/* Routes */

FeedBox.Router.route('sources/:id', function(id) {
    var items = FeedBox.Session.get('item-collection'),
        settings = FeedBox.Session.get('setting-collection');

    FeedBox.switch('content-view', 'item-list', function() {
        return new FeedBox.Module.Item.Views.List({
            collection: items
        });
    });

    if (items) {
        var data = FeedBox.Session.get('item-collection-data', function() {
                return {
                    mode: 'unread',
                    page: 1,
                    limit: settings.getByKey('view.unread.count', 50),
                    sortby: settings.getByKey('view.unread.sortby', 'newest')
                };
            }),
            selectedMenuItem = ['.menu-item-source-' + id];

        selectedMenuItem.push('.menu-item-' + data.mode);

        data.page = 1;
        data.source = id;
        if (data.tag) {
            delete data.tag;
        }

        FeedBox.Session.set('item-collection-data', data);
        items.fetch({
            reset: true,
            data: data
        });
        FeedBox.Session.set('selected-menu-items', selectedMenuItem);
        FeedBox.selectMenuItem();
    }
});

FeedBox.Router.route('sources/add', function() {
    var sources = FeedBox.Session.get('source-collection'),
        model = new FeedBox.Module.Source.Model();

    FeedBox.switch('content-view', 'source-add', function() {
        return new FeedBox.Module.Source.Views.Form({
            el: '#content',
            model: model,
            collection: sources
        });
    });
    FeedBox.Session.get('footer-view').hide();
});

FeedBox.Router.route('sources/:id/edit', function(id) {
    var sources = FeedBox.Session.get('source-collection'),
        model = sources.get(id);

    if (model) {
        FeedBox.switch('content-view', 'source-edit', function() {
            return new FeedBox.Module.Source.Views.Form({
                el: '#content',
                model: model,
                collection: sources
            });
        });
        FeedBox.Session.get('footer-view').hide();
    } else {
        FeedBox.notify("Source not found", "error");
        FeedBox.Router.navigate('settings/sources', { trigger: true });
    }
});

FeedBox.Router.route('sources/update', function() {
    var sources = FeedBox.Session.get('source-collection'),
        user = FeedBox.Session.get('user');

    FeedBox.notify("Update started.", "success");
    Backbone.ajax({
        url: BASE_URL + '/update',
        cache: false,
        dataType: 'json',
        headers: user.accessHeader(),
        success: function(models, textStatus, jqXHR) {
            sources.set(models);
            FeedBox.notify("Update successfull.", "success");
        },
        error: function() {
            FeedBox.notify("Update failed.", "error");
        }
    });
});

FeedBox.Router.route('sources/:id/update', function(id) {
    var sources = FeedBox.Session.get('source-collection'),
        model = sources.get(id),
        user = FeedBox.Session.get('user');

    if (model) {
        Backbone.ajax({
            url: BASE_URL + '/update/' + model.id,
            dataType: 'json',
            headers: user.accessHeader(),
            success: function(modelData, textStatus, jqXHR) {
                model.set(modelData);
                FeedBox.notify(model.get('name') + " - Source update successfull.", "success");

            },
            error: function() {
                FeedBox.notify(model.get('name') + " - Source update failed.", "error");
            }
        });
    }

    FeedBox.Router.navigate('settings/sources', { trigger: true });
});

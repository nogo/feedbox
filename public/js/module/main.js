"use strict";

FeedBox.Module.Main = new Nerve.Module({
    Views: {
        App: Backbone.View.extend({
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
                if (this.model && this.model.get('loggedIn')) {
                    FeedBox.renderTemplate(this.$el, this.options.template);
                    FeedBox.Hook.call('render-app-view');
                    Backbone.history.start();

                    if (!this.model.get('authRequired')) {
                        this.$('.btn.logout').hide();
                    }
                } else {
                    FeedBox.Hook.call('render-login-view');
                    FeedBox.renderTemplate(this.$el, this.options.templateLogin);
                    Backbone.history.stop();
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

                var data = FeedBox.Helper.Form.Serialize(this.$(e.currentTarget)),
                    user = FeedBox.Session.get('user');

                user.signin(data.username, data.password);
            }
        }),
        Header: Backbone.View.extend({
            el: '#header',
            events: {
                'submit #search': 'search',
                'click .toggable': 'toggable',
                'click .mark-as-read': 'markAsRead',
                'click .reload': 'reload',
                'click .logout': 'logout'
            },
            render: function() {
                return this;
            },
            markAsRead: function(e) {
                if (e) {
                    e.preventDefault();
                }

                var that = this;

                // save models read state
                this.collection.markItemRead({
                    success: function(models, textStatus, jqXHR) {
                        that.collection.fetchNext({
                            reset: true,
                            success: function(models, textStatus, jqXHR) {
                                var view = FeedBox.Session.get('content-view');
                                view.el.scrollTop = 0;
                            }
                        });
                    }
                });
            },
            reload: function(e) {
                if (e) {
                    e.preventDefault();
                }

                this.collection.fetchNext({
                    reset: true,
                    success: function(models, textStatus, jqXHR) {
                        var view = FeedBox.Session.get('content-view');
                        view.el.scrollTop = 0;
                    }
                });
            },
            search: function(e) {
                if (e) {
                    e.preventDefault();
                }

                var value = Backbone.$.trim(this.$('#search-input').val());

                if (!_.isEmpty(value)) {
                    value = _.escape(value.replace(/\s+/g, '+'));
                    FeedBox.Router.navigate('search/' + value, {trigger: true});
                }
            },
            logout: function(e) {
                if (e) {
                    e.preventDefault();
                }

                var user = FeedBox.Session.get('user');
                if (user) {
                    user.signout();
                }
            }
        }),
        Sidebar: Backbone.View.extend({
            el: '#sidebar',
            initialize: function() {
                this.tags = FeedBox.Session.get('tag-collection');
                this.views = [];
            },
            render: function() {
                var elements = [];
                var tagList = new FeedBox.Views.List({
                    prefix: 'tag-',
                    tagName: 'ul',
                    collection: this.tags,
                    attributes: {
                        'class': 'nav nav-list'
                    },
                    item: {
                        tagName: 'li',
                        template: '#tpl-sidebar-tag-item',
                        View: FeedBox.Module.Tag.Views.SidebarItem
                    }
                });
                this.views.push(tagList);
                elements.push(tagList.render().el);

                var sources = FeedBox.Session.get('tagless-source-collection', function() {
                    var sourceCollection = FeedBox.Session.get('source-collection');
                    return new FeedBox.Module.Source.Collection(
                        sourceCollection.filter(function(model) {
                            var tagId = model.get('tag_id'),
                                active = model.get('active');
                            return active && (tagId === null || tagId === '');
                        })
                    );
                });

                var sourceList = new FeedBox.Views.List({
                    prefix: 'source-',
                    tagName: 'ul',
                    collection: sources,
                    attributes: {
                        'class': 'nav nav-list'
                    },
                    item: {
                        tagName: 'li',
                        template: '#tpl-sidebar-source-item',
                        View: FeedBox.Views.ListItem
                    }
                });
                this.views.push(sourceList);
                elements.push(sourceList.render().el);

                this.$('#sidebar-sources').html(elements);

                return this;
            },
            remove: function() {
                _.each(this.views, function(view) {
                    view.remove();
                });
                this.undelegateEvents();

                return this;
            }
        }),
        Footer: Backbone.View.extend({
            el: '#footer',
            options: {
                template: '#tpl-footer'
            },
            initialize: function() {
                if (this.collection) {
                    this.collection.on('sync', this.updateItemTotal, this);
                }
            },
            render: function() {
                if (this.collection) {
                    FeedBox.renderTemplate(this.$el, this.options.template, {
                        length: this.collection.length,
                        total: this.collection.total()
                    });
                }
                return this;
            },
            remove: function() {
                if (this.collection) {
                    this.collection.off('sync', this.render, this);
                }
            },
            updateItemTotal: function(collection, response, options) {
                if (options) {
                    var xhr = undefined;
                    if (options.hasOwnProperty('getResponseHeader')) {
                        xhr = options;
                    } else if (options.xhr) {
                        xhr = options.xhr;
                    }

                    if (xhr) {
                        var total = xhr.getResponseHeader('X-Items-Total');
                        if (collection && collection.total) {
                            collection.total(total);
                            this.render();
                        }
                    }
                }
            },
            hide: function(e) {
                this.$el.html('');
            }
        })
    },
    initialize: function(App) {
        App.Hook.add({
            scope: 'render-app-view',
            callback: function() {
                var tags = App.Session.get('tag-collection'),
                    sources = App.Session.get('source-collection'),
                    items = App.Session.get('item-collection');

                if (sources) {
                    sources.fetch({ async: false });
                }

                if (tags) {
                    tags.fetch({ async: false });
                }

                App.renderView('header-view', new App.Module.Main.Views.Header({
                    collection: items
                }));

                App.renderView('sidebar-view', new App.Module.Main.Views.Sidebar());

                App.renderView('footer-view', new App.Module.Main.Views.Footer({
                    collection: items
                }));
            }
        });
        App.Hook.add({
            scope: 'render-login-view',
            callback: function() {
                var view = App.Session.get('header-view');
                if (view) {
                    view.remove();
                }

                view = App.Session.get('sidebar-view');
                if (view) {
                    view.remove();
                }

                view = App.Session.get('footer-view');
                if (view) {
                    view.remove();
                }

                view = App.Session.get('content-view');
                if (view) {
                    view.remove();
                    App.Session.remove('content-view');
                    App.Session.remove('content-view-name');
                }
            }
        });
    }
});

"use strict";

App.Module.Content = {
    Views: {
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
                                var view = App.Session.get('content-view');
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
                        var view = App.Session.get('content-view');
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
                    App.router.navigate('search/' + value, {trigger: true});
                }
            },
            logout: function(e) {
                if (e) {
                    e.preventDefault();
                }

                var user = App.Session.get('user');
                if (user) {
                    user.signout();
                }
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
                    var html = App.render(this.options.template, {
                        length: this.collection.length,
                        total: this.collection.total()
                    });
                    if (html) {
                        this.$el.html(html);
                    }
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
        var hooks = App.Session.get('hooks');
        if (hooks) {
            hooks.add({
                scope: 'after-login',
                callback: function() {
                    var header = new App.Module.Content.Views.Header({
                        collection: App.Session.get('item-collection')
                    });
                    header.render();
                    App.Session.set('header-view', header);

                    var footer = new App.Module.Content.Views.Footer({
                        collection: App.Session.get('item-collection')
                    });
                    footer.render();
                    App.Session.set('footer-view', footer);
                }
            });
        }
    }
};

App.Module.Content.initialize(App);
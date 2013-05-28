"use strict";

App.Module.Content = {
    Views: {
        Footer: Backbone.View.extend({
            el: '#content-footer',
            options: {
                template: '#tpl-content-footer'
            },
            initialize: function() {
                if (this.collection) {
                    this.collection.on('sync', this.updateItemTotal, this);
                }
            },
            render: function() {
                if (this.collection) {
                    var html = App.render(this.options.template, { total: this.collection.total() });
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
        var footer = new this.Views.Footer({
            collection: App.Session.get('item-collection')
        });
        footer.render();
        App.Session.set('content-footer-view', footer);

    }
};

App.Module.Content.initialize(App);
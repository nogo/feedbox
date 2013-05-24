"use strict";

App.Module.Content = {
    Views: {
        Footer: Backbone.View.extend({
            el: '#content-footer',
            options: {
                template: '#tpl-content-footer'
            },
            initialize: function() {
                this.items = App.Session.get('item-collection');
                this.itemTotal = 0;

                if (this.items) {
                    this.items.on('sync', this.render, this);
                }
            },
            render: function(collection, xhr, options) {
                if(options && options.hasOwnProperty('getResponseHeader')) {
                    options = {
                        xhr: options
                    };
                }
                if(options && options.xhr && options.xhr.getResponseHeader('X-Items-Total')) {
                    this.itemTotal = options.xhr.getResponseHeader('X-Items-Total');
                }

                var html = App.render(this.options.template, {total: this.itemTotal});
                console.log(this.options.template);
                if (html) {
                    this.$el.html(html);
                }

                return this;
            },
            remove: function() {
                if (this.items) {
                    this.items.off('sync', this.render, this);
                }
            },
            hide: function(e) {
                this.$el.html('');
            }
        })
    },
    initialize: function(App) {
        var footer = new this.Views.Footer();
        footer.render();
        App.Session.set('content-footer-view', footer);

    }
};

App.Module.Content.initialize(App);
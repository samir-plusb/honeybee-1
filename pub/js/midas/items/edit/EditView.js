/**
 * The EditView module manages all behaviour for the system's Items/EditView.
 */
midas.items.edit.EditView = midas.core.BaseView.extend({

    log_prefix: "EditView",

    content_panel: null,

    content_item_menu: null,

    slide_panel: null,

    onInitGui: function()
    {
        this.initContentTabs();

        this.content_item_menu = new midas.core.CommandTriggerList(
            $('#content-item-menu').first()[0]
        );

        this.slide_panel = new midas.items.edit.SlidePanel(
            $('.slide-panel', this.layout_root)
            .css({ 'position': 'absolute', 'width': '100%' })
        );

        this.content_item_menu.registerCommands({
            'store': this.controller.onStoreItemIntent.bind(this.controller),
            'delete': this.controller.onDeleteItemIntent.bind(this.controller),
            'new': this.controller.onNewItemIntent.bind(this.controller),
            'list': this.slide_panel.toggle.bind(this.slide_panel)
        });

        var items_container = $('.content-items').first();
        items_container.css('left', -items_container.outerWidth());

        this.logInfo(items_container.css('padding'));
    },

    initContentTabs: function()
    {
        this.content_panel = $(this.options.tabs_container, this.layout_root);
        $('.legend', this.content_panel[0]).css('display', 'none');
        this.content_panel.tabs();
    }
});
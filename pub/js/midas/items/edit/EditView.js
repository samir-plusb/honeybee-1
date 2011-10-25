/**
 * @class
 * @augments midas.core.BaseObject
 * @description The EditView module manages all behaviour for the system's Items/EditSuccessView.
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.EditView = midas.core.BaseView.extend(
/** @lends midas.items.edit.EditView.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type string
     */
    log_prefix: "EditView",

    /**
     * Holds a jquery element, that represents our import item content-panel.
     * @type jquery
     */
    content_panel: null,

    /**
     * Holds an SlidePanel instance used to slide our content.
     * @type midas.core.CommandTriggerList
     */
    content_item_menu: null,

    /**
     * Holds an SlidePanel instance used to slide our content.
     * @type midas.items.edit.SlidePanel
     */
    slide_panel: null,

    /**
     * Holds a jquery element, that represents our content-item editing form.
     * @type jquery
     */
    editing_form: null,

    /**
     * @description <p>Initializes our gui (behaviours).</p>
     * <p>This method is invoked from midas.core.BaseView, upon init invocation.</p>
     */
    onInitGui: function()
    {
        this.initContentTabs();

        this.slide_panel = new midas.items.edit.SlidePanel(
            $('.slide-panel', this.layout_root)
            .css({ 'position': 'absolute', 'width': '100%' })
        );

        this.content_item_menu = new midas.core.CommandTriggerList(
            $('#content-item-menu').first()[0]
        );

        this.content_item_menu.registerCommands({
            'store': this.controller.onStoreItemIntent.bind(this.controller),
            'delete': this.controller.onDeleteItemIntent.bind(this.controller),
            'new': this.controller.onNewItemIntent.bind(this.controller),
            'list': this.slide_panel.toggle.bind(this.slide_panel)
        });

        this.editing_form = new midas.items.edit.EditForm(
            $('.document-editing form', this.layout_root)
        );

        var items_container = $('.content-items').first();
        items_container.css('left', -items_container.outerWidth());

        this.logInfo("Check that form", this.editing_form);
    },

    /**
     * @description Initializes the jquery-ui tabs for our content_panel.
     */
    initContentTabs: function()
    {
        this.content_panel = $(this.options.tabs_container, this.layout_root);
        $('.legend', this.content_panel[0]).css('display', 'none');
        this.content_panel.tabs();
    }
});
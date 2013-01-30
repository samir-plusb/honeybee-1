
honeybee.widgets.ClipboardWidget = honeybee.widgets.Widget.extend({
   
    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "AutoComplete",

    clipboard_client: null,

    init: function(element, options)
    {
        this.parent(element, options);

        this.options.copy_trigger_el = this.element.find(
            this.options.copy_trigger_el
        ).first();
        this.options.copy_text_el = this.element.find(
            this.options.copy_text_el
        ).first();

        this.clipboard_client = new ZeroClipboard.Client();
        this.clipboard_client.setText(this.options.copy_text);
        this.clipboard_client.glue(
            this.options.copy_trigger_el[0]
        );
        this.clipboard_client.addEventListener('onComplete', this.onCopiedToClipboard.bind(this));
    },

    // #################################
    // #     widget implementation     #
    // #################################
    getTemplate: function()
    {
        return null;
    },

    onCopiedToClipboard: function(client, text) 
    {
        this.fire(
            'notify::info', 
            ['Der Text: "' + text + '" wurde in die Zwischenablage kopiert.']
        );
    }
});

// #####################
// #     constants     #
// #####################
honeybee.widgets.ClipboardWidget.DEFAULT_OPTIONS = {
    autobind: true,
    copy_text: '',
    copy_trigger_el: null,
    copy_trigger_el: null
};

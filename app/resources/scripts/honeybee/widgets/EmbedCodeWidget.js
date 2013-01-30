honeybee.widgets.EmbedCodeWidget = honeybee.widgets.Widget.extend({

    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "EmbedCodeWidget",

    // <knockout_props>
    fieldname: null,

    embed_code: null,

    preview_code: null,

    is_valid: null,
    // </knockout_props>

    init: function(element, options)
    {
        this.parent(element, options);
    },

    // #################################
    // #     widget implementation     #
    // #################################
    getTemplate: function()
    {
        return 'static/widgets/EmbedCodeWidget.html';
    },

    initKnockoutProperties: function()
    {
        var that = this;
        this.fieldname = ko.observable(this.options.fieldname);
        this.embed_code = ko.observable(this.options.embed_code || '');
        this.preview_code = ko.observable('');
        this.is_valid = ko.computed(function()
        {
            if (that.embed_code().match(/<iframe.*?<\/iframe>/ig)) // quick expression, make it more fine grained later
            {
                that.preview_code(that.embed_code());
                return true;
            }
            else
            {
                that.preview_code('');
                return false;
            }
        });
    }
});

// #####################
// #     constants     #
// #####################
honeybee.widgets.EmbedCodeWidget.DEFAULT_OPTIONS = {
    autobind: true,
    fieldname: null,
    embed_code: null
};

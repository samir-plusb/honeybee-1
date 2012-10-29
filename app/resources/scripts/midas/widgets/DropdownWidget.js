midas.widgets.DropdownWidget = midas.widgets.Widget.extend({
    
    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "DropdownWidget",

    init: function(element, options)
    {
        this.parent(element, options);
    },

    // #################################
    // #     widget implementation     #
    // #################################
    getTemplate: function()
    {
        return false;
    },

    initGui: function()
    {
        var hidden_input = this.element.find(this.options.text_input);
        var dropdown = this.element.find(this.options.select_input);
        dropdown.change(function()
        {
            hidden_input.val(
                $(this).find(':selected').text().trim()
            );
        });
    }
});

// #####################
// #     constants     #
// #####################
midas.widgets.DropdownWidget.DEFAULT_OPTIONS = {
    autobind: true,
    select_input: '.value-select',
    text_input: '.text-hidden'
};
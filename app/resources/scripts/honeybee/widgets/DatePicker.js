honeybee.widgets.DatePicker = honeybee.widgets.Widget.extend({

    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "DatePicker",

    // <knockout_props>
    date: null,

    format: null,

    field_name: null,

    field_id: null,
    // </knockout_props>

    // #################################
    // #     widget implementation     #
    // #################################
    getTemplate: function()
    {
        return 'static/widgets/DatePicker.html';
    },

    initGui: function()
    {
        this.parent();

        var datepicker = this.element.find('.input-append.date');
        datepicker.datepicker();
        var input = datepicker.find('input');
        var trigger = datepicker.find('.add-on');

        input.focus(function()
        {
            datepicker.datepicker('show');
        });
        input.blur(function()
        {
            datepicker.datepicker('hide');
        });
        trigger.click(function()
        {
            input.focus();
        });
    },

    initKnockoutProperties: function()
    {
        this.date = ko.observable(this.options.date);
        this.format = ko.observable(this.options.format);
        this.field_name = ko.observable(this.options.field_name);
        this.field_id = ko.observable(this.options.field_id);
    }
});

// #####################
// #     constants     #
// #####################
honeybee.widgets.DatePicker.DEFAULT_OPTIONS = {
    autobind: true,
    date: '12.12.2013',
    format: 'dd.mm.yyyy',
    field_name: '',
    field_id: ''
};

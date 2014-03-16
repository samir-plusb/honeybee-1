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

    datepicker: null,

    // #################################
    // #     widget implementation     #
    // #################################
    init: function(element, options, ready_callback)
    {
        options.placeholder = options.placeholder || options.format;
        this.parent(element, options, ready_callback);
    },

    getTemplate: function()
    {
        return 'static/widgets/DatePicker.html';
    },

    initGui: function()
    {
        this.parent();

        this.datepicker = this.element.find('.input-append.date');
        var input = this.datepicker.find('input');
        this.datepicker.datetimepicker();
        var trigger = this.datepicker.find('.add-on');
        this.datepicker.on('show', function(){
            var val = input.val();
            if (val.length == 10) {
                that.datepicker.datepicker('setValue', input.val());
            }
        });
        var that = this;
        this.datepicker.on('changeDate', function(e) {
            that.fire('changeDate');
        });

        var cur_val = null;
        input.keyup(function(){
            var val = input.val();
            if (cur_val !== val && val.length == 10) {
                that.datepicker.datepicker('setValue', input.val());
                cur_val = val;
            }
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
    format: 'dd.MM.yyyy HH:mm:ss',
    field_name: '',
    field_id: '',
    placeholder: null
};

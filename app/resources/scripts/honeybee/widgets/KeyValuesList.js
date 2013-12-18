honeybee.widgets.KeyValuesList = honeybee.widgets.Widget.extend({

    // #########################
    // #     property defs     #
    // #########################
    log_prefix: 'KeyValuesList',

    // <knockout_properties>
    fieldname: null,

    keyvalues_pairs: null,
    // </knockout_properties>

    init: function(element, options)
    {
        this.parent(element, options);

        if (this.options.data)
        {
            for (var key in this.options.data)
            {
                var cur_values = this.options.data[key];
                this.keyvalues_pairs.push(
                    new honeybee.widgets.KeyValuesList.KeyValuesPair(
                        this.fieldname(),
                        key,
                        cur_values
                    )
                );
            }
        }
    },

    getTemplate: function()
    {
        return honeybee.widgets.KeyValuesList.TPL;
    },

    // ##################################
    // #     knockoutjs bound funcs     #
    // ##################################
    addNewKeyValuesPair: function()
    {
        this.keyvalues_pairs.push(
            new honeybee.widgets.KeyValuesList.KeyValuesPair(
                this.fieldname(),
                'Eigentschaft/Name',
                [ 'Eigentschaft/Wert 1', 'Eigenschaft/Wert 2' ]
            )
        );
    },

    removeKeyValuesPair: function(keyvalues_pair)
    {
        this.keyvalues_pairs.remove(keyvalues_pair);
    },

    // ###########################
    // #     working methods     #
    // ###########################
    initKnockoutProperties: function()
    {
        this.fieldname = ko.observable(this.options.fieldname);
        this.keyvalues_pairs = ko.observableArray([]);
    }
});

// ####################################
// #     nested(internal) classes     #
// ####################################
honeybee.widgets.KeyValuesList.KeyValuesPair = honeybee.core.BaseObject.extend({

    // #########################
    // #     property defs     #
    // #########################

    log_prefix: "KeyValuesList.KeyValuesPair",

    // <knockout_properties>
    fieldname: null,

    key: null,

    values: null,

    key_edit_enabled: null,

    key_input_focused: null,

    values_edit_enabled: null,

    values_string: null,

    values_input_focused: null,
    // </knockout_properties>

    init: function(fieldname, key, values)
    {
        this.parent();
        this.initKnockoutProperties(fieldname, key, values);
    },

    // ##################################
    // #     knockoutjs bound funcs     #
    // ##################################
    enableKeyEditing: function()
    {
        this.key_edit_enabled(true);
        this.key_input_focused(true);
    },

    enableValuesEditing: function()
    {
        this.values_edit_enabled(true);
        this.values_input_focused(true);
        this.values_string(this.values().join(', '));
    },

    applyCurrentValues: function()
    {
        this.values([$.trim(this.values_string())]);
    },

    handleFocus: function(event, action)
    {
        var el = $(event.originalEvent.target);
        if ('select' === action)
        {
            el.select();
        }
        if ('setend' === action)
        {
            el.putCursorAtEnd();
        }
    },

    onKeyInput: function(data, event)
    {
        if (13 === event.which)
        {
            $(event.originalEvent.target).blur();
            return false;
        }
        return true;
    },

    // ###########################
    // #     working methods     #
    // ###########################
    initKnockoutProperties: function(fieldname, key, values)
    {
        var that = this;
        this.key = ko.observable(key);
        this.fieldname = ko.computed(function()
        {
            return fieldname + "[" + that.key() + "][]";
        });
        this.values = ko.observableArray(values);
        this.key_edit_enabled = ko.observable(false);
        this.values_edit_enabled = ko.observable(false);
        this.key_input_focused = ko.observable(false);
        this.values_input_focused = ko.observable(false);
        this.values_string = ko.observable(values.join(', '));
        this.key_input_focused.subscribe(function(new_value)
        {
            if (false === new_value)
            {
                that.key_edit_enabled(false);
            }
        });
        this.values_input_focused.subscribe(function(new_value)
        {
            if (false === new_value)
            {
                that.values_edit_enabled(false);
                that.applyCurrentValues();
            }
        });
    },
});

// #####################
// #     constants     #
// #####################
honeybee.widgets.KeyValuesList.DEFAULT_OPTIONS = {
    autobind: true,
    fieldname: 'keyvalueslist'
};

honeybee.widgets.KeyValuesList.TPL = ''
+'<a class="btn btn-success hb-icon-plus" ' // ** add button section **
+'   data-bind="click: addNewKeyValuesPair">'
+'   Neue Eigenschaft'
+'</a>'
+'<br /><br />'
+'<table class="propertyExplorer table table-bordered table-striped">' // ** key-values pairs table section **
+'    <thead>'
+'        <tr>'
+'            <th>Name</th>'
+'            <th>Werte</th>'
+'            <th></th>'
+'        </tr>'
+'    </thead>'
+'    <tbody data-bind="foreach: keyvalues_pairs">'
+'        <tr>'
+'            <td class="propertyName">' // [property name cell]
+'                <input class="key-values-list-keyinput" type="text"' // text input for editing the prop name
+'                       data-bind="value: key,'
+'                                  valueUpdate: \'afterkeydown\','
+'                                  hasfocus: key_input_focused,'
+'                                  visible: key_edit_enabled,'
+'                                  event: {'
+'                                    focus: function(data, event) { handleFocus(event, \'select\'); },'
+'                                    keypress: onKeyInput'
+'                                  }" />'
+'                <span data-bind="text: key,' // text for displaying the property name
+'                                 visible: !key_edit_enabled(),'
+'                                 click: enableKeyEditing">'
+'                </span>'
+'            </td>'
+'            <td class="propertyValues">' // [property values cell]
+'                <textarea class="span8" ' // textarea for editing (comma separated)prop values
+'                          data-bind="hasfocus: values_input_focused, '
+'                                     visible: values_edit_enabled, '
+'                                     value: values_string,'
+'                                     event: { focus: function(data, event) { handleFocus(event, \'setend\'); } }">'
+'                </textarea>'
+'                <ul data-bind="foreach: values, ' // list for displaying prop values
+'                               visible: !values_edit_enabled()">'
+'                    <li class="label label-info" '
+'                        data-bind="text: $data, '
+'                                   click: function() { $parent.enableValuesEditing(); }">'
+'                    </li>'
+'                </ul>'
+'            </td>'
+'            <td>' // [remove icon cell]
+'                <i class="hb-icon-cancel" '
+'                   data-bind="click: function(data){ $parent.removeKeyValuesPair(data); }">'
+'                </i>'
+'            </td>'
+'        </tr>'
+'    </tbody>'
+'</table>'
+'<input type="hidden" value="" data-bind="attr: { name: fieldname() }" />'
+'<ul class="key-values-list-valuelist" style="display: none" ' // ** hidden values section **
+'    data-bind="foreach: keyvalues_pairs">'
+'    <div class="key-values-list-proplist" '
+'         data-bind="foreach: values">'
+'        <input type="hidden" '
+'               data-bind="attr: { name: $parent.fieldname }, '
+'                          value: $data" />'
+'    </div>'
+'</ul>';

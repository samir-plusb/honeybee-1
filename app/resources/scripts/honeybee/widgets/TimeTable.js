honeybee.widgets.TimeTable = honeybee.widgets.Widget.extend({
   
    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "TimeTable",

    // <knockout_properties>
    fieldname: null,

    time_spans: null,

    days_of_week: null,

    input_from: null,

    input_to: null,

    is_valid: null,
    // </knockout_properties>

    init: function(element, options)
    {
        this.parent(element, options);

        if (this.options.data)
        {
            for (var i = 0; i < this.options.data.length; i++)
            {
                var timespan = this.options.data[i];
                this.time_spans.push(
                    new honeybee.widgets.TimeTable.TimeSpan(timespan.from, timespan.to)
                );
            }
        }
    },

    getTemplate: function()
    {
        return honeybee.widgets.TimeTable.TPL;
    },

    // ##################################
    // #     knockoutjs bound funcs     #
    // ##################################
    onInput: function(data, event)
    {
        if (13 === event.which)
        {
            if (this.is_valid())
            {
                this.addCurrentTimeSpan();
            }
            return false;
        }
        return true;
    },

    validate: function()
    {
        this.is_valid(
            new RegExp(this.options.time_pattern,'g').test(this.input_from.time()) 
            && new RegExp(this.options.time_pattern,'g').test(this.input_to.time()) 
        );
        return this.is_valid();
    },

    addCurrentTimeSpan: function()
    {
        var from = {
            day: this.input_from.day()[0],
            time: this.input_from.time()
        };
        var to = {
            day: this.input_to.day()[0],
            time: this.input_to.time()
        };
        this.time_spans.push(new honeybee.widgets.TimeTable.TimeSpan(from, to));
        this.input_from.time('');
        this.input_to.time('');
        this.element.find('.tagslist-from-day').focus();
    },

    removeTimeSpan: function(time_span)
    {
        this.time_spans.remove(time_span);
    },

    // ###########################
    // #     working methods     #
    // ###########################
    initKnockoutProperties: function()
    {
        var that = this;
        this.is_valid = ko.observable(false);
        this.fieldname = ko.observable(this.options.fieldname);
        this.time_spans = ko.observableArray([]).indexed();
        this.days_of_week = ko.observableArray([
            'Montag', 'Dienstag' ,'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'
        ]);
        this.input_from = {
            day: ko.observable([ "Montag" ]),
            time: ko.observable('')
        };
        this.input_to = {
            day: ko.observableArray([ "Montag" ]),
            time: ko.observable('')
        };
        this.input_from.time.subscribe(function(new_value)
        {
            that.validate();
        });
        this.input_to.time.subscribe(function(new_value)
        {
            that.validate();
        });
    }
});

// ##########################
// #     nested classes     #
// ##########################
honeybee.widgets.TimeTable.TimeSpan = honeybee.core.BaseObject.extend({

    log_prefix: 'TimeSpan',

    from: null,

    to: null,

    init: function(from, to)
    {
        this.parent();
        this.from = from;
        this.to = to;
    }
});

// #####################
// #     constants     #
// #####################
honeybee.widgets.TimeTable.DEFAULT_OPTIONS = {
    autobind: true,
    fieldname: 'timetable',
    time_pattern: "([01]?[0-9]|2[0-3]):[0-5][0-9]",
    data: null
};

honeybee.widgets.TimeTable.TPL = ''
+'<div class="row-fluid"><div class="span3">' // ** input time: from-section (select day & enter time) **
+'  <div class="control-group">'
+'      <label>Von</label>'
+'      <div class="controls">'
+'          <select class="tagslist-from-day input-medium" '
+'                  data-bind="options: days_of_week, '
+'                             selectedOptions: input_from.day, '
+'                             event: { keypress: onInput }">'
+'          </select>'
+'          <input class="tagslist-from-time input-medium" placeholder="09:00" '
+'                 data-bind=\'value: input_from.time, '
+'                             valueUpdate: "afterkeydown", '
+'                             event: { keypress: onInput }\' />'
+'      </div>'
+'  </div>'
+'</div>' 
+'<div class="span3">' // ** input time: to-section (select day & enter time) **
+'  <div class="control-group">'
+'      <label>Bis</label>'
+'      <div class="controls">'
+'          <select class="tagslist-to-day input-medium" '
+'                  data-bind="options: days_of_week, '
+'                             selectedOptions: input_to.day, '
+'                             event: { keypress: onInput }">'
+'          </select>'
+'          <input class="tagslist-to-time input-medium" placeholder="09:00"'
+'                  data-bind=\'value: input_to.time, '
+'                              valueUpdate: "afterkeydown", '
+'                              event: { keypress: onInput }\' />'
+'      </div>'
+'  </div>'
+'</div>'
+'<div class="span3">' // ** input time: state label (might be joined by a configurable select-field one day) **
+'  <div class="control-group">'
+'      <label>ist dieser Ort geöffnet.</label>'
+'  </div>'
+'</div>'
+'<div class="span3">' // ** add button section **
+'  <button class="btn btn-success timetable-add" style="margin-top:32px" '
+'          data-bind="enable: is_valid, '
+'          click: addCurrentTimeSpan">hinzufügen</button>'
+'</div></div>'
+'<div class="row-fluid">' // ** data representation - (time)table **
+'  <table class="table table-bordered table-striped">'
+'      <thead>'
+'          <tr>'
+'              <th>Von</th>'
+'              <th>Bis</th>'
+'              <th>Status</th>'
+'              <th></th>'
+'          </tr>'
+'      </thead>'
+'      <tbody data-bind="foreach: time_spans">'
+'          <tr>'
+'              <td><span data-bind="text: from.day"></span>, <span data-bind="text: from.time"></span> Uhr</td>' // [from cell]
+'              <td><span data-bind="text: to.day"></span>, <span data-bind="text: to.time"></span> Uhr</td>' // [to cell]
+'              <td>geöffnet</td>' // [state cell]
+'              <td><i class="icon-remove" ' // [remove icon cell]
+'                     data-bind="click: function(data) { $parent.removeTimeSpan(data); }"></i>'
+'              </td>'
+'          </tr>'
+'      </tbody>'
+'  </table>'
+'</div>' // ** hidden values list **
+'<ul class="timetable-valuelist" style="display: none">'
+'  <li data-bind="foreach: time_spans">'
+'      <input type="hidden" data-bind="attr: { name: $parent.fieldname() + \'[\'+$index()+\'][from][day]\' }, value: from.day" />'
+'      <input type="hidden" data-bind="attr: { name: $parent.fieldname()+\'[\'+$index()+\'][from][time]\' }, value: from.time" />'
+'      <input type="hidden" data-bind="attr: { name: $parent.fieldname()+\'[\'+$index()+\'][to][day]\' }, value: to.day" />'
+'      <input type="hidden" data-bind="attr: { name: $parent.fieldname()+\'[\'+$index()+\'][to][time]\' }, value: to.time" />'
+'  </li>'
+'</ul>';
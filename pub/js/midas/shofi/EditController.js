midas.shofi.EditController = midas.core.BaseObject.extend({
    
    // #########################
    // #     property defs     #
    // #########################
    log_prefix: 'EditController',

    element: null,

    options: null,

    widgets: null,

    // <knockout_properties>
    alerts: null,
    // </knockout_properties>

    init: function(element, options)
    {
        this.parent();
        // basic member initialization
        this.element = element;
        this.options = options;
        this.widgets = {};
        // setup knockout properties
        this.initKnockoutProperties();
        // then bind this instance to the (rendered)widget gui.
        if (this.options.autobind)
        {
            ko.applyBindings(this, this.element[0]);
            this.registerWidgets();
        }
    },

    // ##################################
    // #     knockoutjs bound funcs     #
    // ##################################

    onFormSubmit: function()
    {
        var that = this,
        form = this.element.find('form'),
        handleResponse = function(resp_data)
        {
            if (! resp_data || ! resp_data.state)
            {
                throw "Unexpected response data structure received from midas backend (places save).";
            }
            if ('ok' === resp_data.state)
            {
                var ticket_id_el = that.element.find('.ticket-identifier').first();
                var cur_ticket_id = ticket_id_el.val();
                ticket_id_el.val(resp_data.data.ticket_id);
                that.addAlerts(
                    resp_data.messages || [ ],
                    'success'
                );
                that.addAlerts(
                    resp_data.errors || [ ],
                    'error'
                );
                if (! cur_ticket_id)
                {
                    // @todo consistent url append
                    window.location.href += '?ticket='+resp_data.data.ticket_id;
                }
            }
            else if('error' === resp_data.state)
            {
                that.addAlerts(
                    resp_data.errors || [ ],
                    'error'
                );
            }
        };
        midas.core.Request.curry(
            form.attr('action'), 
            form.serialize(), 
            'post'
        )(handleResponse, handleResponse);
    },

    addAlerts: function(alerts, type)
    {
        var that = this;
        for (var i = 0; i < alerts.length; i++)
        {
            this.addAlert({
                type: type,
                message: alerts[i]
            });
        }
    },

    addAlert: function(alert)
    {
        var that = this;
        this.alerts.push(alert);
        setTimeout(function()
        {
            if (-1 !== that.alerts.indexOf(alert)) // if alert is still there.
            {
                that.removeAlert(alert);
            }
        }, 10000);
    },

    removeAlert: function(alert)
    {
        this.alerts.remove(alert);
    },

    showAlert: function(elem, idx, alert) 
    { 
        var that = this;
        if (elem.nodeType === 1) 
        {
            $(elem).animate({
               opacity: 1
            }, { duration: 600, queue: false });
            $(elem).animate({
               'margin-top': '0px'
            }, { duration: 400, queue: false });
        }
    },

    hideAlert: function(elem, idx, alert)
    {
        if (elem.nodeType === 1) 
        {
            $(elem).animate({
               opacity: 0
            }, { duration: 300, queue: false });
            $(elem).animate({
               'margin-top': '-55px'
            }, { duration: 600, queue: false, complete: function() 
            {
                $(elem).remove();
            } });
        }
    },

    // ###########################
    // #     working methods     #
    // ###########################
    initKnockoutProperties: function()
    {
        this.alerts = ko.observableArray([ ]);
    },

    registerWidgets: function()
    {
        var that = this;
        for (var i = 0; i < this.options.widgets.length; i++)
        {
            var widget_def = this.options.widgets[i];
            var widget = midas.widgets.Widget.factory(
                widget_def.selector, 
                widget_def.type 
                // @todo support namespaces within the "type" value by parsing the latter,
                // thereby introducing some kind of syntax that allows one to provide a namespace.
            );
            this.widgets[widget.name] = widget;
            widget.on('notify::info', function(message)
            {
                that.addAlert({
                    'type': 'success',
                    'message': message
                });
            });
        }
    }
});

// ##########################
// #     static methods     #
// ##########################
midas.shofi.EditController.factory = function(element)
{
    element = $(element);
    var options = $.extend(
        {},
        midas.shofi.EditController.DEFAULT_OPTIONS,
        JSON.parse(element.attr('data-edit-controller-options') || "{}")
    );
    options.widgets = options.widgets || [];
    return new midas.shofi.EditController(element, options);
};

// #####################
// #     constants     #
// #####################
midas.shofi.EditController.DEFAULT_OPTIONS = {
    autobind: true,
    view_name: 'CoreItem',
    widgets: null
};
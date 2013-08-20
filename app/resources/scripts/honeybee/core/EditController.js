honeybee.core.EditController = honeybee.core.BaseObject.extend({

    // #########################
    // #     property defs     #
    // #########################
    log_prefix: 'EditController',

    element: null,

    options: null,

    widgets: null,

    // <knockout_properties>
    alerts: null,

    identifier: null,

    revision: null,

    shortId: null,

    request_pending: null,
    // </knockout_properties>

    widgets: null,

    init: function(element, options)
    {
        this.parent();
        // basic member initialization
        this.element = element;
        this.options = options;

        this.widgets = [];
        // setup knockout properties
        this.initKnockoutProperties();
        // then bind this instance to the (rendered)widget gui.
        if (this.options.autobind)
        {
            ko.applyBindings(this, this.element[0]);
            this.registerWidgets();
        }
        var that = this;
        var hide = function(help_trigger)
        {
            help_trigger.data('visible', false);
            help_trigger.removeClass('active');
            help_trigger.popover('hide');
        };

        var show = function(help_trigger)
        {
            that.element.find('.help-trigger.active').each(function(idx, active_trigger)
            {
                hide($(active_trigger));
            });

            help_trigger.data('visible', true);
            help_trigger.popover('show');
            help_trigger.addClass('active');

            var popover = that.element.find('.popover');
            popover.css('width', 400);

            var element_pos = help_trigger.position();
            var position_top = -(popover.height() / 2) + 15;
            if (help_trigger.hasClass('group-help'))
            {
                position_top += 10;
            }

            popover.css({
                'left': element_pos.left - popover.width() - 5,
                'top': position_top
            });
        };

        this.element.find('.help-trigger').each(function(idx, element)
        {
            element = $(element);
            element.popover();
            element.data('visible', false);

            element.click(function()
            {
                var visible = element.data('visible');
                if (visible)
                {
                    hide(element);
                }
                else
                {
                    show(element);
                }
            });
        });

        window.addEventListener('message', this.onDomMessagePostReceived.bind(this), false);
    },

    // ##################################
    // #     knockoutjs bound funcs     #
    // ##################################

    onFormSubmit: function(form, event)
    {
        var that = this;
        var form = this.element.find('form');
        var post_url = form.attr('action');

        if (event)
        {
            form.append($('<input type="hidden" name="save_type" value="save_and_new" />'));
        }

        if (this.request_pending() || this.options.readonly)
        {
            return false;
        }

        // make sure all ckeditor values are correctly populated.
        // this is workaround for values sometimes not being updated for whatever reason ...
        $('textarea.ckeditor').each(function () {
            var textarea = $(this);
            textarea.val(
                CKEDITOR.instances[textarea.attr('id')].getData()
            );
        });

        if (this.identifier() && ! $.url().param('id'))
        {
            post_url += '?id=' + this.identifier();
        }

        handleResponse = function(resp_data)
        {
            that.request_pending(false);

            if (! resp_data || ! resp_data.state)
            {
                throw "Unexpected response data structure received from honeybee backend (places save).";
            }

            if ('ok' === resp_data.state)
            {
                that.addAlerts(
                    resp_data.messages || [ ],
                    'success'
                );
                that.addAlerts(
                    resp_data.errors || [ ],
                    'error'
                );

                that.identifier(resp_data.data.identifier);
                that.revision(resp_data.data.revision);
                that.shortId(resp_data.data.shortId);

                if (resp_data.redirect_url)
                {
                    window.location.href = resp_data.redirect_url;
                }
                // @todo history.pushState nice 2 have here
            }
            else if('error' === resp_data.state)
            {
                that.addAlerts(
                    resp_data.errors || [ ],
                    'error'
                );
            }
        };

        this.request_pending(true);
        honeybee.core.Request.curry(
            post_url,
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

    onDomMessagePostReceived: function(event)
    {
        if (0 !== this.options.event_origin.indexOf(event.origin))
        {
            return;
        }

        var msg_data = JSON.parse(event.data);

        if (! msg_data.event_type)
        {
            return;
        }

        if (msg_data.event_type === 'info-message')
        {
            this.addAlert({
                type: 'success',
                message: msg_data.message
            });
        }
        else if (msg_data.event_type === 'error-message')
        {
            this.addAlert({
                type: 'error',
                message: msg_data.message
            });
        }
    },

    // ###########################
    // #     working methods     #
    // ###########################
    initKnockoutProperties: function()
    {
        this.alerts = ko.observableArray([ ]);
        this.request_pending = ko.observable(false);
        this.identifier = ko.observable(this.options.identifier || "");
        this.revision = ko.observable(this.options.revision || "");
        this.uuid = ko.observable(this.options.uuid || "");
        this.shortId = ko.observable(this.options.shortId || "");
        this.language = ko.observable(this.options.language || "");
        this.version = ko.observable(this.options.version || "");
        this.redirect_after_request = ko.observable(false);
    },

    registerWidgets: function()
    {
        var that = this;

        $('.honeybee-widget').each(function(idx, element)
        {
            var type_key;

            $.each($(element).attr('class').split(' '), function(index, css_class)
            {
                css_class = css_class.trim();
                if (css_class.match(/^widget-/))
                {
                    type_key = css_class.replace('widget-', '');
                }
            });

            if (type_key)
            {
                that.widgets.push(
                    honeybee.widgets.Widget.factory(element, type_key)
                );
            }
        });
    }
});

// ##########################
// #     static methods     #
// ##########################
honeybee.core.EditController.factory = function(element)
{
    element = $(element);
    var options = $.extend(
        {},
        honeybee.core.EditController.DEFAULT_OPTIONS,
        JSON.parse(element.attr('data-edit-controller-options') || "{}")
    );
    options.widgets = options.widgets || [];
    return new honeybee.core.EditController(element, options);
};

// #####################
// #     constants     #
// #####################
honeybee.core.EditController.DEFAULT_OPTIONS = {
    autobind: true,
    view_name: 'CoreItem',
    widgets: null
};
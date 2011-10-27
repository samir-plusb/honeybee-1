/**
 * @class
 * @augments midas.core.BaseObject
 * @description <p>The EditForm serves as the behavioural pendant to the Items/EditSuccessView's html form element.</p>
 * <p>It takes care of managing field related behaviours such as validation and formatting.</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.EditForm = midas.core.BaseObject.extend(
/** @lends midas.items.edit.EditForm.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'EditForm',

    /**
     * Holds a jQuery element that represents our HtmlFormElement.
     * @type jQuery
     */
    element: null,

    /**
     * Holds all the inputs that belong to our form.
     * They are held with their name attribute as object property names.
     * @type Object
     */
    fields: null,

    /**
     * Holds a user control that composes behaviour around entering a date range
     * into two HtmlInputElements.
     * @type midas.items.edit.DateRangeInput
     */
    publish_period: null,

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(element, options)
    {
        this.parent(options);
        this.element = element;
        this.fields = {};

        $('input, select, textarea').each(function(idx, field)
        {
            field = new midas.items.edit.Input(field);
            field.on('changed', function(event)
            {
                this.logInfo("Changed event", event);
                this.fire('changed', event);
            }.bind(this));

            this.fields[field.getName()] = field;
        }.bind(this));

        var start_date = this.fields['data[date[from]]'];
        var end_date = this.fields['data[date[till]]'];
        this.publish_period = new midas.items.edit.DateRangeInput(start_date, end_date);

        this.publish_period.on('changed', function(event)
        {
            this.logInfo("Changed event", event);
            this.fire('changed', event);
        }.bind(this));
    },

    val: function(field)
    {
        if (this.fields[field])
        {
            return this.fields[field].val();
        }

        return null;
    },

    validate: function()
    {
        var result = {
            success: true,
            messages: {}
        };

        $.each(this.fields, function(name, field)
        {
            var validation_res = field.validate();

            if (! validation_res.success)
            {
                result.success = false;
                result.messages[name] = validation_res.messages;
                field.element.addClass('ui-state-error');
            }
            else
            {
                field.element.removeClass('ui-state-error');
            }
        }.bind(this));

        return result;
    }
});
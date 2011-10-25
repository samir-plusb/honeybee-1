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
    fields: {},

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

        $('input, select, textarea').each(function(idx, field)
        {
            field = $(field);
            this.fields[field.attr('name')] = field;
        }.bind(this));

        var start_date = this.fields['data[date[from]]'];
        var end_date = this.fields['data[date[till]]'];
        this.publish_period = new midas.items.edit.DateRangeInput(start_date, end_date);
    }
});
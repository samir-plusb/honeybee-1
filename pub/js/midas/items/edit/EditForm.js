/**
 * @class
 * @augments midas.core.BaseObject
 * @description <p>The EditForm serves as the behavioural pendant to the Items/EditSuccessView's html form element.</p>
 * <p>It </p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.EditForm = midas.core.BaseObject.extend(
/** @lends midas.items.edit.EditFrom.prototype */
{
    log_prefix: '[EditForm]',

    form: null,

    fields: {},

    init: function(element, options)
    {
        this.parent(options);
        this.form = element;

        $('input, select, textarea').each(function(idx, field)
        {
            field = $(field);
            this.fields[field.attr('name')] = field;

            if (field.hasClass('date-picker'))
            {
                field.datepicker();
            }
        }.bind(this));
    },

    validateAll: function()
    {
        this.logInfo("Validate....");
    }
});
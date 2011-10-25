/**
 * @class
 * @augments midas.core.DateRangeInput
 * @description <p>The DateRangeInput wraps to input fields describing a start and an end date.</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.DateRangeInput = midas.core.BaseObject.extend(
/** @lends midas.items.edit.DateRangeInput.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'DateRangeInput',

    /**
     * Holds a jQuery element that represents our start date input field.
     * @type jQuery
     */
    start_field: null,

    /**
     * Holds a jQuery element that represents our end date input field.
     * @type jQuery
     */
    end_field: null,

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(start_date_input, end_date_input, options)
    {
        this.parent(options);

        this.start_field = start_date_input;
        this.end_field = end_date_input;

        this.start_field.datepicker({
            onSelect: function(date_string)
            {
                if (! this.end_field.val())
                {
                    this.end_field.val(date_string);
                }
            }.bind(this)
        });
    }
});

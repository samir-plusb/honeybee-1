/**
 * @class
 * @augments midas.core.BaseObject
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
     * @type midas.items.edit.Input
     */
    start_field: null,

    /**
     * Holds a jQuery element that represents our end date input field.
     * @type midas.items.edit.Input
     */
    end_field: null,

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(start_date_input, end_date_input, options)
    {
        this.parent(options);

        this.end_field = end_date_input;
        this.end_field.element.datepicker({
            dateFormat: this.getDateFormat(),
            beforeShow: function()
            {
                this.end_field.prev_val = this.end_field.val();
            }.bind(this),
            onSelect: function(date_string)
            {
                if (this.end_field.prev_val != date_string)
                {
                    this.fire('changed', { prev: this.end_field.prev_val, cur: date_string });
                }
            }.bind(this)
        });

        this.start_field = start_date_input;
        this.start_field.element.datepicker({
            dateFormat: this.getDateFormat(),
            beforeShow: function()
            {
                this.start_field.prev_val = this.start_field.val();
            }.bind(this),
            onSelect: function(date_string)
            {
                if (this.start_field.prev_val != date_string)
                {
                    this.fire('changed', {
                        prev: this.start_field.prev_val,
                        cur: date_string,
                        src: this.start_field
                    });
                }

                if (! this.end_field.val())
                {
                    this.start_field.val(date_string);
                }
            }.bind(this)
        });
    },

    getDateFormat: function()
    {
        return this.options.date_format || 'dd.mm.yy';
    }
});

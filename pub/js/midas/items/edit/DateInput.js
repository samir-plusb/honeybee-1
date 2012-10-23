/**
 * @class
 * @augments midas.items.edit.Input
 * @description The DateInput provides behaviour for picking dates from a datepicker for a HTMLInput element.
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.DateInput = midas.items.edit.Input.extend(
/** @lends midas.items.edit.DateInput.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'DateInput',
    
    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {HTMLElement} element The HTMLInput element to enhance.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(element, options)
    {
        this.parent(element, options);

        this.element.datepicker({
            dateFormat: this.getDateFormat(),
            onSelect: function(date_string)
            {
                if (this.prev_val != date_string)
                {
                    this.revalidate();
                    this.fire('changed', [{ prev: this.prev_val, cur: date_string }]);
                }
            }.bind(this)
        });
    },
    
    /**
     * @description Return the date format to use for this instance.
     * @returns  {String} A date format like: yy/mm/dd or dd-mm-yy.
     */
    getDateFormat: function()
    {
        return this.options.date_format || 'yy/mm/dd';
    }
});

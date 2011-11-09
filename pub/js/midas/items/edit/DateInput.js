/**
 * @class
 * @augments midas.items.edit.Input
 * @description <p>The DateInput handles.</p>
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

    getDateFormat: function()
    {
        return this.options.date_format || 'yy/mm/dd';
    }
});

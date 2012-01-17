/**
 * @class
 * @augments midas.core.Behaviour
 * @description <p>The SearchBox module provides behaviour for interacting with the list's search form.</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.list.SearchBox = midas.core.Behaviour.extend(
/** @lends midas.items.list.SearchBox.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'SearchBox',

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {HTMLElement} element The HTMLForm input, select...
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(element, options)
    {
        this.parent(element, options);

        this.search_input = this.element.children('input[name="search_phrase"]').first();
        this.reset_btn = this.element.children('.reset-search').first();
        var that = this;

        this.reset_btn.click(function(event)
        {
            event.preventDefault();
            that.search_input.val('');
            that.search_input.focus();
            $(this).hide();
        });

        this.search_input.focus(function()
        {
            $(this).select();
        });
    }
});
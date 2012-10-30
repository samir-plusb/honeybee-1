/**
 * @class
 * @augments midas.items.edit.Input
 * @description The UrlInput provides behaviour for holding a user provided url.
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.UrlInput = midas.items.edit.Input.extend(
/** @lends midas.items.edit.UrlInput.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'UrlInput',
    
    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {HTMLElement} element The HTMLInput element to enhance.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(element, options)
    {
        this.parent(element, options);
        var that = this;
        this.element.change(function(event)
        {
            that.val(
                that.normalizeUrl(that.val())
            );
        });
    },
    
    /**
     * @description Getter and setter for our input's value.
     * When a parameter is supplied the method will behave as getter else as setter.
     * @param {String} value [Optional] When given the method will act as a setter.
     * @returns {String} When no parameter is passed the method will return the input's current value.
     */
    val: function()
    {
        if (1 <= arguments.length)
        {
            arguments[0] = this.normalizeUrl(arguments[0]);
        }
        return this.parent.apply(this, arguments);
    },
    
    normalizeUrl: function(url)
    {
        if (5 < url.length && -1 === url.indexOf('http'))
        {
            url = 'http://' + url;
        }
        
        return url;
    }
});

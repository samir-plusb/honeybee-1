/**
 * @class
 * @augments midas.core.BaseObject
 * @description The BaseController module provides base functionality for connecting views to an async backend.
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.core.BaseController = midas.core.BaseObject.extend(
/** @lends midas.core.BaseController.prototype */
{

    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'BaseController',
    
    /**
     * An object holding the (regexp) filters we use to capture intents.
     * @type Object
     */
    intent_filters: null,
    
    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {midas.items.edit.EditController} controller The view's (parent) controller.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(options)
    {
        this.parent(options);
        this.intent_filters = {};
        this.intent_filters = this.getIntentFilters();
    },
    
    /**
     * @description Return an object describing the filters we provide for capturing intents.
     * The returned object is a flat key-value structure whereas the key is a regular expression
     * and the value either string (name of function callable on this instance) or a function that is passed
     * an intent and shall return true or false depending on whether it captured the intent or not.
     * Example: {
            '/midas/intents/contentItem/store': this.onStoreContentItemIntent.bind(this),
            '/midas/intents/contentItem/delete': 'onDeleteContentItemIntent'
        }
     */
    getIntentFilters: function()
    {
        throw "A BaseController implementation must override the getIntentFilters method.";
    },
    
    /**
     * @description Apply our filters on the given intent and capture it
     * if any match.
     * @param {Object} intent
     */
    apply: function(intent)
    {
        this.logDebug("Incoming intent:", intent);
        var intent_handled = false;

        $.each(this.intent_filters, function(pattern, func)
        {
            if (! intent_handled && intent.name.match(pattern))
            {
                this.logDebug("Matched intent:", pattern, "against", intent.name);

                if ('string' == typeof func && 'function' == typeof this[func])
                {
                    this[func].apply(this, [intent]);
                    intent_handled = true;
                }
                else if ('function' == typeof func && true === func(intent))
                {
                   intent_handled = true;
                }
            }
        }.bind(this));

        return intent_handled;
    }
});
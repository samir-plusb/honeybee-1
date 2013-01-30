/**
 * @class
 * @augments honeybee.core.Behaviour
 * @description <p>The Input module provides behaviour, such as validation, for HTMLForm input element.</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
honeybee.core.Routing = honeybee.core.Behaviour.extend(
/** @lends honeybee.core.Routing.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'Routing',

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {HTMLElement} element The HTMLForm input, select...
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(element, options)
    {
        this.parent(element, options);
    },

    getRoute: function(name)
    {
        return this.options[name];
    }
});
/**
 * @class
 * @augments honeybee.core.BaseObject
 * @description <p>The Behaviour module provides separation of configuration state (options) and implementation.</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
honeybee.core.Behaviour = honeybee.core.BaseObject.extend(
/** @lends honeybee.core.Behaviour.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'Behaviour',

    /**
     * The prefix to use when resolving our type-id from our element's class attribute.
     * @type String
     */
    prefix: 'jsb-',

    /**
     * Holds our behaviour's root element.
     */
    element: null,

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {HTMLElement} element Our root element.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(element, options)
    {
        this.parent(options);
        this.element = $(element);
        this.apply();
    },

    /**
     * Applies our behaviour to the root element.
     * Hence loookup and initialze our configuration.
     */
    apply: function()
    {
        var classes = this.element.attr('class');

        if (typeof classes !== 'undefined' && classes !== false)
        {
            $.each(classes.split(" "), function(idx, cur_class)
            {
                if (cur_class.match(this.prefix))
                {
                    // first search for a nested config and if there is none,
                    // then check for a config sibling.
                    var options_selector = '.' + cur_class + '-options';
                    var config_input = this.element.children(options_selector);
                    config_input = (0 < config_input.length)? config_input : this.element.next(options_selector);

                    if (0 < config_input.length)
                    {
                        // if we found a config parse and set our options.
                        var parsed_conf = $.parseJSON(config_input.val());
                        config_input.remove();
                        this.element.removeClass(cur_class);
                        for(var prop in parsed_conf)
                        {
                            this.options[prop] = parsed_conf[prop];
                        }
                    }
                }
            }.bind(this));
        }
    }
});
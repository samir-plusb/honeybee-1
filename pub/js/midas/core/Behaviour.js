/**
 * @class
 * @augments midas.core.Behaviour
 * @description <p>The Behaviour module provides separation of configuration state (options) and implementation.</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.core.Behaviour = midas.core.BaseObject.extend(
/** @lends midas.core.Behaviour.prototype */
{
    log_prefix: 'Behaviour',

    prefix: 'jsb-',

    element: null,

    init: function(element, options)
    {
        this.parent(options);
        this.element = $(element);
        this.apply();
    },

    apply: function()
    {
        var classes = this.element.attr('class');

        if (typeof classes !== 'undefined' && classes !== false)
        {
            $.each(classes.split(" "), function(idx, cur_class)
            {
                if (cur_class.match(this.prefix))
                {
                    var options_selector = '.' + cur_class + '-options';
                    var config_input = this.element.next(options_selector);

                    if (0 < config_input.length)
                    {
                        var parsed_conf = JSON.parse(config_input.val());

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
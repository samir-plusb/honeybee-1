midas.core.BaseController = midas.core.BaseObject.extend({

    log_prefix: 'BaseController',

    intent_filters: null,

    init: function(options)
    {
        this.parent(options);
        this.intent_filters = {};

        if ('function' != typeof this.getIntentFilters)
        {
            throw "A BaseController implemenation shall must implement a getIntentFilters method.";
        }

        this.intent_filters = this.getIntentFilters();
    },

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
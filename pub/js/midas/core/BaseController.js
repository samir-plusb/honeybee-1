midas.core.BaseController = midas.core.BaseObject({

    intent_filters: {},

    init: function(options)
    {
        this.parent(options);

        if ('function' != typeof this.getIntentFilters)
        {
            throw "A BaseController implemenation shall must implement a getIntentFilters method.";
        }
    },

    applyIntent: function(intent)
    {
        this.intent_filters.each(
            function(pattern, filter_func)
            {

            }
        );

        this.logDebug("Incoming intent:", intent);
    }
});
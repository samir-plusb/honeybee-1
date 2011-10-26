midas.core.BaseController = midas.core.BaseObject({

    init: function(options)
    {
        this.parent(options);
    },

    applyIntent: function(intent)
    {
        this.logDebug("Incoming intent:", intent);
    }
});
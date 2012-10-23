midas.list.Action = midas.core.BaseObject.extend({

    log_prefix: "Action",

    payload: null,

    init: function(payload)
    {
        this.parent();

        if ('function' !== typeof payload)
        {
            throw "The payload parameter must be a function and is reqired.";
        }
        this.payload = payload;
    },

    execute: function()
    {
        var that = this;
        try
        {
            that.fire('start');
            that.payload(
                function()
                {
                    that.fire('success');
                },
                function(error)
                {
                    that.fire('failure', [ error ]);
                }
            );
        }
        catch(error)
        {
            that.fire('failure', [ error ]);
        }
    }
});
/**
 * @class
 * The BaseObject serves as the base implementation for all 'modules'.
 * It passes options support and logging functionality on to it's children.
 *
 * @author Thorsten Schmitt-Rink
 */
midas.core.BaseObject = midas.core.Module.create(
/** @lends midas.core.BaseObject.prototype */
{
    log_prefx: "[BaseObject]",

    logger: null,

    /**
     * @contructor
     * @description Create a new BaseObject instance, thereby passing in optional options.
     */
    init: function(options)
    {
        this.logger = {}; // @todo create a logger class and use it here.
    },

    logDebug: function(msg)
    {
        var msg = "["+(new Date()).getTime()+"] ["+this.log_prefix+"] " + msg;
        var args = [msg];

        for (var i = 1; i < arguments.length; i++)
        {
            args.push(arguments[i]);
        }
        // @todo use logger as soon as available.
        if (console && console.log) console.log.apply(console, args);
    },

    logInfo: function(msg)
    {
        var msg = "["+(new Date()).getTime()+"] ["+this.log_prefix+"] " + msg;
        var args = [msg];

        for (var i = 1; i < arguments.length; i++)
        {
            args.push(arguments[i]);
        }
        // @todo use logger as soon as available.
        if (console && console.info) console.info.apply(console, args);
    },

    logError: function(msg)
    {
        var msg = "["+(new Date()).getTime()+"] ["+this.log_prefix+"] " + msg;
        var args = [msg];

        for (var i = 1; i < arguments.length; i++)
        {
            args.push(arguments[i]);
        }
        // @todo use logger as soon as available.
        if (console && console.error) console.error.apply(console, args);
    }
});
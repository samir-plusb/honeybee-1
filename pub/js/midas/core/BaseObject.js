/**
 * @class
 * @description <p>The BaseObject serves as the base implementation for all 'modules'.</p>
 * <p>It passes options support and logging functionality on to it's children.</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.core.BaseObject = midas.core.Module.create(
/** @lends midas.core.BaseObject.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type string
     */
    log_prefix: "[BaseObject]",

    /**
     * Holds our currently used logger instance.
     * @type midas.core.Logger
     */
    logger: null,

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(options)
    {
        this.logger = {}; // @todo create a logger class and use it here.
    },

    /**
     * @description Logs a debug message to our debug logging destination.
     * @param {string} msg The message to log.
     */
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

    /**
     * @description Logs a info message to our info logging destination.
     * @param {string} msg The message to log.
     */
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

    /**
     * @description Logs a error message to our error logging destination.
     * @param {string} msg The message to log.
     */
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
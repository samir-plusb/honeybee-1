/**
 * @class
 * @description <p>The BaseObject serves as the base implementation for all 'modules'.</p>
 * <p>It passes options support and logging functionality on to it's children.</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
honeybee.core.BaseObject = honeybee.core.Module.create(
/** @lends honeybee.core.BaseObject.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: "BaseObject",

    /**
     * Holds our currently used logger instance.
     * @type honeybee.core.Logger
     */
    logger: null,

    /**
     * An object holding our registered event listeners.
     * @type Object
     */
    listeners: null,

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(options)
    {
        this.options = options || {};
        this.logger = {}; // @todo create a logger class and use it here.
        this.listeners = {};
    },

    /**
     * @description Add a listener for the given event.
     * @param {String} event The name of the event to listen for.
     * @param {Function} callback A callback function that is invoked when the event is fired.
     * @returns {honeybee.core.BaseObejct} Returns the same instance for fluent api support.
     */
    on: function(event, callback)
    {
        if (! this.listeners[event])
        {
            this.listeners[event] = [];
        }
        this.listeners[event].push(callback);
        return this;
    },

    /**
     * @description Remove a listener for the given event.
     * @param {String} event The name of the event to listen for.
     * @param {Function} callback The callback function to remove.
     * @returns {honeybee.core.BaseObejct} Returns the same instance for fluent api support.
     */
    removeListener: function(event, callback)
    {
        if (! this.listeners[event])
        {
            return this;
        }
        var idx = this.listeners[event].indexOf(callback);
        if (-1 !== idx)
        {
            this.listeners[event].splice(idx, 1);
        }
        return this;
    },

    /**
     * @description Remove all listeners for the given event.
     * @param {String} event The name of the event to listen for.
     * @returns {honeybee.core.BaseObejct} Returns the same instance for fluent api support.
     */
    removeAllListeners: function(event)
    {
        if (! this.listeners[event])
        {
            return this;
        }
        this.listeners[event] = [];
        return this;
    },

    /**
     * @description Propagate the given event to all listeners that registered for it,
     * thereby passing in the given state.
     * @param {String} event The name of the event to listen for.
     * @param {Object} state Holds the state that goes along with the event.
     * @returns {honeybee.core.BaseObejct} Returns the same instance for fluent api support.
     */
    fire: function(event, state)
    {
        if (! this.listeners[event])
        {
            return this;
        }
        for (var i = 0; i < this.listeners[event].length; i++)
        {
            this.listeners[event][i].apply(null, state);
        }
        return this;
    },

    /**
     * @description Logs a debug message to our debug logging destination.
     * @param {String} msg The message to log.
     * @returns {honeybee.core.BaseObejct} Returns the same instance for fluent api support.
     */
    logDebug: function(msg)
    {
        var args = [];
        for (var i = 1; i < arguments.length; i++)
        {
            args.push(arguments[i]);
        }
        this.log(msg, 'debug', args);
        return this;
    },

    /**
     * @description Logs a info message to our info logging destination.
     * @param {String} msg The message to log.
     * @returns {honeybee.core.BaseObejct} Returns the same instance for fluent api support.
     */
    logInfo: function(msg)
    {
        var args = [];
        for (var i = 1; i < arguments.length; i++)
        {
            args.push(arguments[i]);
        }
        this.log(msg, 'info', args);
        return this;
    },

    /**
     * @description Logs a error message to our error logging destination.
     * @param {String} msg The message to log.
     * @returns {honeybee.core.BaseObejct} Returns the same instance for fluent api support.
     */
    logWarning: function(msg)
    {
        var args = [];
        for (var i = 1; i < arguments.length; i++)
        {
            args.push(arguments[i]);
        }
        this.log(msg, 'warn', args);
        return this;
    },

    /**
     * @description Logs a a given message to the output dest. matching the provided severity.
     * @param {String} msg The message to log.
     * @param {String} severity The severity of the given log message (log, info ,error);
     */
    log: function(msg, severity, args)
    {
        // %c, args.unshift('color: white; background-color:black');

        args.unshift(
            "[" + new Date().getTime() + "] [" + this.log_prefix + "] " + msg
        );

        // @todo use logger as soon as available.
        if (console && console[severity]) console[severity].apply(console, args);
    },

    /**
     * @description <p>Handles the given error.</p>
     * <p>In development a stacktrace is printed to the logging.</p>
     * @param {Error} exception
     */
    handleException: function(exception)
    {
        if (console && console.exception) console.exception.apply(console, [exception]);
    }
});
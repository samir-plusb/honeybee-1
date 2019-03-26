/**
 * @class
 * @augments honeybee.core.BaseObject
 * @description <p>The CommandTriggerList is a generic component for handling click events on a list of elements
 * and mapping them to specific commands.</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
honeybee.core.CommandTriggerList = honeybee.core.BaseObject.extend(
/** @lends honeybee.core.CommandTriggerList.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: "CommandTriggerList",

    /**
     * Holds a jQuery element that is treated as a trigger container (ul) and searched for items (li a).
     * @type jQuery
     */
    container: null,

    /**
     * Holds an object that is used to store our command items together with their related actions.
     * @type Object
     */
    command_items: null,

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {jQuery} container The container (list) that contains our command item elements.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(container, options)
    {
        this.parent(options);

        this.command_items = {};
        this.container = container;

        this.container.find('li a').each(function(idx, item)
        {
            var command_name = this.parseCommandName(item);
            this.command_items[command_name] = {
                name: command_name,
                item: $(item),
                callback: null
            };
        }.bind(this));

        this.container.find('li a').click(function(event)
        {
            event.originalEvent._preventDefault = true;

            if (! $(event.target).parent('li').hasClass('inactive'))
            {
                this.dispatchCommand(
                    this.parseCommandName(event.target),
                    event.target
                );
            }

            return false;
        }.bind(this));

        if (this.options.commands)
        {
            this.registerCommands(this.options.commands);
        }
    },

    /**
     * @description Registers the given commands.
     * @param {Object} callback_map
     */
    registerCommands: function(callback_map)
    {
        for (var name in callback_map)
        {
            this.registerCommand(name, callback_map[name]);
        }
    },

    /**
     * @description Registers the given command.
     * @param {String} name
     * @param {Function} callback
     */
    registerCommand: function(name, callback)
    {
        if ('function' != typeof callback)
        {
            throw "The 'command' parameter must be a function. Invalid type given.";
        }

        if (! this.command_items[name])
        {
            throw "There is no command item that maps to the given command name: " + name;
        }

        this.command_items[name].callback = callback;
    },

    /**
     * @description Dispatches the given command.
     * @param {String} name
     * @param {jQuery} affected_item
     */
    dispatchCommand: function(name, affected_item)
    {
        if (! this.command_items[name])
        {
            throw "There is no command item that maps to the given command name: " + name;
        }

        if (this.command_items[name].callback)
        {
            this.command_items[name].callback(affected_item);
        }
    },

    /**
     * @description Resolve the command name for a given command item.
     * @param {jQuery} item
     * @return {String} The parsed command name.
     */
    parseCommandName: function(item)
    {
        var name_string = $(item).attr('href');

        if (0 !== name_string.indexOf('#'))
        {
            return name_string;
        }

        return name_string.substr(1);
    },
    
    /**
     * @description Enable the ui trigger element for the given command name.
     * @param {String} command The name of the command trigger to enable.
     */
    enable: function(command)
    {
        if (! this.command_items[command])
        {
            throw "There is no command item that maps to the given command name: " + command;
        }
        this.command_items[command].item.parent('li').removeClass('inactive');
    },
    
    /**
     * @description Disable the ui trigger element for the given command name.
     * @param {String} command The name of the command trigger to disable.
     */
    disable: function(command)
    {
        if (! this.command_items[command])
        {
            throw "There is no command item that maps to the given command name: " + command;
        }
        this.command_items[command].item.parent('li').addClass('inactive');
    }
});
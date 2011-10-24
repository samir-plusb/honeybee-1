midas.core.CommandTriggerList = midas.core.BaseObject.extend({

    log_prefix: "CommandTriggerList",

    container: null,

    command_items: {},

    init: function(container)
    {
        this.container = container;

        $('li a', this.container).each(function(idx, item)
        {
            var command_name = this.parseCommandName(item);
            this.command_items[command_name] = {
                name: command_name,
                item: item,
                callback: null
            };
        }.bind(this));

        $('li a', this.container).live('click', function(event)
        {
            event.preventDefault();
            this.dispatchCommand(
                this.parseCommandName(event.target),
                event.target
            );
        }.bind(this));
    },

    registerCommands: function(callback_map)
    {
        for (var name in callback_map)
        {
            this.registerCommand(name, callback_map[name]);
        }
    },

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

    dispatchCommand: function(name, affected_item)
    {
        if (! this.command_items[name])
        {
            throw "There is no command item that maps to the given command name: " + name;
        }

        this.logInfo(name);

        if (this.command_items[name].callback)
        {
            this.command_items[name].callback(affected_item);
        }
    },

    parseCommandName: function(item)
    {
        var name_string = $(item).attr('href');

        if (0 !== name_string.indexOf('#'))
        {
            return name_string;
        }

        return name_string.substr(1);
    }
});
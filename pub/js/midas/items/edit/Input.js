/**
 * @class
 * @augments midas.items.edit.Input
 * @description <p>The Input module...</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.Input = midas.core.Behaviour.extend(
/** @lends midas.items.edit.Input.prototype */
{
    log_prefix: 'Input',

    name: null,

    init: function(element)
    {
        this.parent(element);
        this.name = this.element.attr('name');
        this.element.change(function(event)
        {
            this.logInfo("Changed event ", event);
            this.fire('changed', event);
        }.bind(this));
    },

    getName: function()
    {
        return this.name;
    },

    val: function()
    {
        return this.element.val.apply(this.element, arguments);
    },

    validate: function()
    {
        var result = {
            success: true,
            messages: {}
        };

        if (this.options.mandatory && ! this.val())
        {
            result.success = false;
            result.messages.mandatory = "Mandatory err";
        }

        if (this.options.regexp && ! this.val().match(this.options.regexp))
        {
            result.success = false;
            result.messages.mandatory = "Regexp err for pattern " + this.options.regexp;
        }

        return result;
    }
});
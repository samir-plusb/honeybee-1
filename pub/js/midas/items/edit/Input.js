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
            this.fire('changed', event);
        }.bind(this));
    },

    getName: function()
    {
        return this.name;
    },

    val: function()
    {
        return this.element.val.apply(
            this.element,
            arguments
        );
    },

    validate: function()
    {
        var result = {
            success: true,
            messages: {}
        };

        var value = this.val();

        if (this.options.mandatory && ! value)
        {
            result.success = false;
            result.messages.mandatory = "Mandatory err";
        }

        if (this.options.regex && ! value.match(this.options.regex))
        {
            result.success = false;
            result.messages.regex = "Regexp err for pattern " + this.options.regex;
        }

        if (this.options.min && value.length < this.options.min)
        {
            result.success = false;
            result.messages.min = "Err for min " + this.options.min;
        }

        if (this.options.max && value.length > this.options.max)
        {
            result.success = false;
            result.messages.max = "Err for min " + this.options.max;
        }

        return result;
    }
});
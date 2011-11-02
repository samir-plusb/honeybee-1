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
    prefix: 'jsb-input',

    log_prefix: 'Input',

    name: null,

    prev_val: null,

    init: function(element, options)
    {
        this.parent(element, options);

        if (undefined === this.options.validate_correction)
        {
            this.options.validate_correction = true;
        }

        if (undefined === this.options.ui_states)
        {
            this.options.ui_states = { invalid: 'ui-state-error' };
        }

        this.name = this.element.attr('name');
        this.element.change(function(event)
        {
            this.revalidate();
            this.fire('changed', event);
        }.bind(this));

        this.element.focus(function(event)
        {
            this.prev_val = this.element.val();
        }.bind(this));
    },

    getName: function()
    {
        return this.name;
    },

    val: function()
    {
        var ret = this.element.val.apply(
            this.element,
            arguments
        );

        if (1 === arguments.length && this.prev_val != arguments[0])
        {
            this.revalidate();
        }

        return ret;
    },

    revalidate: function()
    {
        var result = null;

        if (true === this.options.validate_correction && this.isMarkedAs('invalid'))
        {
            result = this.validate()

            if (true === result.success)
            {
                this.unmarkAs('invalid');
            }
        }

        return result;
    },

    validate: function()
    {
        var result = {
            success: true,
            messages: {}
        };
        var value = this.val();
        // empty values are not validated but may throw a mandatory error.
        if (true === this.options.mandatory && 0 == value.length)
        {
            result.success = false;
            result.messages.mandatory = "Mandatory err";
            return result;
        }
        else if(0 == value.length)
        {
            return result;
        }
        // default regex validation
        if (this.options.regex && ! value.match(this.options.regex))
        {
            result.success = false;
            result.messages.regex = "Regexp err for pattern " + this.options.regex;
        }
        // min & max validation for numeric and common strings
        var maybe_int = parseInt(value);
        var compare = isNaN(maybe_int) ? value.length : maybe_int;
        if (this.options.min && compare < this.options.min)
        {
            result.success = false;
            result.messages.min = "Err for min " + this.options.min;
        }
        if (this.options.max && compare > this.options.max)
        {
            result.success = false;
            result.messages.max = "Err for min " + this.options.max;
        }
        return result;
    },

    getSelection: function()
    {
        var text = this.val().substring(
            this.element[0].selectionStart,
            this.element[0].selectionEnd
        );
        return text.replace(/[\<\>&]/g, ' ');
    },

    markAs: function(state)
    {
        var css_class = this.options.ui_states[state]
         || 'input-' + state;

        this.element.addClass(css_class);
    },

    unmarkAs: function(state)
    {
        var css_class = this.options.ui_states[state]
         || 'input-' + state;

        this.element.removeClass(css_class);
    },

    isMarkedAs: function(state)
    {
        var css_class = this.options.ui_states[state]
         || 'input-' + state;

        return this.element.hasClass(css_class);
    },

    reset: function()
    {
        this.element.val('')
         .removeAttr('checked')
         .removeAttr('selected');
        this.unmarkAs('invalid');
    }
});
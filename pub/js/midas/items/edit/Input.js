/**
 * @class
 * @augments midas.core.Behaviour
 * @description <p>The Input module provides behaviour, such as validation, for HTMLForm input element.</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.Input = midas.core.Behaviour.extend(
/** @lends midas.items.edit.Input.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'Input',
    
    /**
     * The prefix to use when resolving our type-id from our element's class attribute.
     * @type String
     */
    prefix: 'jsb-input',
    
    /**
     * The prefix to use when resolving our type-id from our element's class attribute.
     * @type String
     */
    name: null,
    
    /**
     * The previous value of the element.
     * @type String
     */
    prev_val: null,
    
    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {HTMLElement} element The HTMLForm input, select...
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
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
            this.fire('changed', [event]);
        }.bind(this));

        this.element.focus(function(event)
        {
            this.prev_val = this.element.val();
        }.bind(this));
    },
    
    /**
     * @description Return the name of the input we are reflecting.
     * @returns {String}
     */
    getName: function()
    {
        return this.name;
    },
    
    /**
     * @description Getter and setter for our input's value.
     * When a parameter is supplied the method will behave as getter else as setter.
     * @param {String} value [Optional] When given the method will act as a setter.
     * @returns {String} When no parameter is passed the method will return the input's current value.
     */
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
    
    /**
     * @description Revalidates our input and removes the invalid marker if the input is valid.
     * @return {Object} Same kind of validation result as returned from {midas.items.edit.Input.validate}.
     */
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
    
    /**
     * @description Validates the given input.
     * @returns {Object} An object reflecting the validation result.
     * The structure looks like this: {
           "success": true, // Tells whether the input is valid or not.
           "messages": {} // Holds an object with error messages, 
                          // with error-type as key and the related msg as value.
       } 
     */
    validate: function()
    {
        var result = {
            success: true,
            messages: {}
        };
        var value = this.val();
        // empty values are not validated but may throw a mandatory error.
        if (true === this.options.mandatory && (!value || 0 == value.length))
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
    
    /**
     * @description Returns the current text selection of the input.
     * @returns {String} The selected text.
     */
    getSelection: function()
    {
        var text = this.val().substring(
            this.element[0].selectionStart,
            this.element[0].selectionEnd
        );

        return text.replace(/[\<\>&]/g, ' ');
    },
    
    /**
     * @description Marks the input to be in a given state.
     * @param {String} state The state we are marking.
     */
    markAs: function(state)
    {
        var css_class = this.options.ui_states[state]
         || 'input-' + state;

        this.element.addClass(css_class);
    },
    
    /**
     * @description Unmarks the input from being in the given state.
     * @param {String} state The state we are unmarking.
     */
    unmarkAs: function(state)
    {
        var css_class = this.options.ui_states[state]
         || 'input-' + state;

        this.element.removeClass(css_class);
    },
    
    /**
     * @description Tells if the input is currently marked for the given state.
     * @param {String} state The state to check for.
     */
    isMarkedAs: function(state)
    {
        var css_class = this.options.ui_states[state]
         || 'input-' + state;

        return this.element.hasClass(css_class);
    },
    
    /**
     * @description Reset the input's value and recover from 'invalid' state.
     */
    reset: function()
    {
        this.element.val('')
         .removeAttr('checked')
         .removeAttr('selected');
        this.unmarkAs('invalid');
    }
});
/**
 * @class
 * @augments midas.core.BaseObject
 * @description <p>The EditForm serves as the behavioural pendant to the Items/EditSuccessView's html form element.</p>
 * <p>It takes care of managing field related behaviours such as validation, formatting, retrieving and setting values.</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.EditForm = midas.core.BaseObject.extend(
/** @lends midas.items.edit.EditForm.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'EditForm',

    /**
     * Holds a jQuery element that represents our HtmlFormElement.
     * @type jQuery
     */
    element: null,

    /**
     * Holds all the inputs that belong to our form.
     * They are held with their name attribute as object property names.
     * @type Object
     */
    fields: null,

    /**
     * Holds a user control that composes behaviour around entering a date range
     * into two HtmlInputElements.
     * @type midas.items.edit.DateRangeInput
     */
    publish_period: null,

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(element, options)
    {
        this.parent(options);
        this.element = element;
        this.fields = {};

        this.element.find('input[type!=hidden], select, textarea').each(function(idx, field)
        {
            var implementor = this.resolveInputFieldClass(field);

            if (! midas.items.edit[implementor])
            {
                implementor = 'Input';
            }

            var input_field = new midas.items.edit[implementor](field);
            input_field.on('changed', function(event)
            {
                if ('data[date[from]]' == input_field.getName())
                {
                    if (0 >= this.fields['data[date[till]]'].val().length)
                    {
                        this.fields['data[date[till]]'].val(
                            input_field.val()
                        );
                    }
                }

                this.fire('changed', event);
            }.bind(this));

            this.fields[input_field.getName()] = input_field;
        }.bind(this));

        // delegate contextmenu events from our text data to the outside world.
        this.fields['data[text]'].on('contextMenuSelect', function(field, item)
        {
            this.fire('contextMenuSelect', [field, item]);
        }.bind(this));
    },

    val: function(field, value)
    {
        if (! field)
        {
            // @todo return all values.
            return {};
        }
        else if (this.fields[field] && undefined == value)
        {
            return this.fields[field].val();
        }
        else if (this.fields[field])
        {
            return this.fields[field].val(value);
        }

        return null;
    },

    validate: function()
    {
        var result = {
            success: true,
            messages: {}
        };

        $.each(this.fields, function(name, field)
        {
            var validation_res = field.validate();

            if (! validation_res.success)
            {
                result.success = false;
                result.messages[name] = validation_res.messages;
                field.element.addClass('ui-state-error');
            }
            else
            {
                field.element.removeClass('ui-state-error');
            }
        }.bind(this));

        return result;
    },

    resolveInputFieldClass: function(input_field)
    {
        var classes = $(input_field).attr('class');
        var type_key = '';
        var resolved_class = '';

        if (typeof classes !== 'undefined' && classes !== false)
        {
            $.each(classes.split(" "), function(idx, cur_class)
            {
                if (! type_key && cur_class.match('jsb-input'))
                {
                    // @todo use constant pattern instead of magic string.
                    type_key = cur_class.replace(/(jsb-input)-?/, '');
                }
            }.bind(this));
        }

        if (type_key)
        {
            // camelize class name
            resolved_class = type_key.replace(/^([a-z])|[\s\-]+([a-z])/g, function ($1) {
                return $1.toUpperCase().replace('-', '');
            });
        }

        resolved_class += 'Input';

        return resolved_class;
    },

    getSelection: function(field)
    {
        if (this.fields[field])
        {
            return this.fields[field].getSelection();
        }

        return '';
    }
});
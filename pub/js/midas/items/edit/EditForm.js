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
     * Flag revealing wether the form is in a dirty state
     * (has been modified and not saved) or not.
     * @type Boolean
     */
    is_dirty: null,

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {HTMLFormElement} The form element to enhance with behaviour.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(element, options)
    {
        this.is_dirty = false;
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
            var input_field = new midas.items.edit[implementor](field)
            .on('changed', function(event)
            {
                if ('date[from]' == input_field.getName())
                {
                    if (0 >= this.fields['date[till]'].val().length)
                    {
                        this.fields['date[till]'].val(
                            input_field.val()
                        );
                    }
                }
                this.markDirty();
                this.fire('changed', [event]);
            }.bind(this));
            this.fields[input_field.getName()] = input_field;
        }.bind(this));
        // delegate contextmenu events from our text data to the outside world.
        this.fields['text'].on('contextMenuSelect', function(field, item)
        {
            this.fire('contextMenuSelect', [field, item]);
        }.bind(this));
        this.fields['tags'].on('createTagDenied', function(field, msg)
        {
            this.fire("createTagDenied", [this, field, msg]);
        }.bind(this));
    },
    
    /**
     * @description Serves as a getter or setter for field values,
     * depending on how many arguments are supplied.
     * @param {String|Object} field When field is a string and no value param has been supplied,
     * the value for the given field is returned. (field-getter)
     * When field is an object the method will take that object and hydrate it into the form fields.
     * @param {String} value When the field param is provided as a string, 
     * value will be set on the corresponding field.
     * @returns {String|Object} When field is a string and value is provided the corresponding field value is returned.
     * When no parameters are passed at all an object holding all the values of all fields is returned.
     */
    val: function(field, value)
    {
        var processed_inputs = [];
        if (! field) // toObject
        {
            var data = {};
            for (var input_name in this.fields)
            {
                data[input_name] = this.val(input_name);
                processed_inputs.push(input_name);
            }
            this.element.find(':input')
             .not(':button, :submit, :reset')
             .each(function(idx, input_field)
            {
                var name = $(input_field).attr('name');
                if (-1 === processed_inputs.indexOf(name))
                {
                    data[name] = $(input_field).val();
                    processed_inputs.push(name);
                }
            }.bind(this));
            return data;
        }
        else if('object' == typeof field) // hydrate
        {
            for (var name in this.fields)
            {
                this.fields[name].val(field[name] || '');
                processed_inputs.push(name);
            }
            this.element.find(':input')
             .not(':button, :submit, :reset')
             .each(function(idx, input_field)
            {
                var name = $(input_field).attr('name');

                if (-1 === processed_inputs.indexOf(name))
                {
                    $(input_field).val(field[name] || '');
                    processed_inputs.push(name);
                }
            }.bind(this));
        }
        else if (field && undefined == value) // input-field getter
        {
            if (this.fields[field])
            {
                return this.fields[field].val();
            }
            return this.element.find('input[name='+field+']').val();
        }
        else // input-field setter
        {
            if (this.fields[field])
            {
                return this.fields[field].val(value);
            }
            return this.element.find('input[name='+field+']').val(value);
        }
        return this;
    },
    
    /**
     * @description Validate all fields and return an aggregated result.
     * @returns {Object}
     */
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
                field.markAs('invalid');
            }
            else
            {
                field.unmarkAs('invalid');
            }
        }.bind(this));

        return result;
    },
    
    /**
     * @description Resolve the input behaviour type
     * that is associated with the given input field.
     * For example the given input field has a class 'jsb-input-date',
     * then DateInput is returned as the behaviour to use.
     * @param {HTMLInput} 
     * @returns {String}
     */
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
    
    /**
     * @description Get the current text selection for the given field.
     * @returns {String} If the field does not exist, an empty string is returned.
     */
    getSelection: function(field)
    {
        if (this.fields[field])
        {
            return this.fields[field].getSelection();
        }

        return '';
    },
    
    /**
     * @description Marks the form as clean (no unsaved modifications).
     */
    markClean: function()
    {
        this.is_dirty = false;
    },
    
    /**
     * @description Marks the form as dirty (unsaved modifications).
     */
    markDirty: function()
    {
        this.is_dirty = true;
    },
    
    /**
     * @description Tells whether the form is currently marked as dirty.
     * @returns {Boolean}
     */
    isDirty: function()
    {
        return this.is_dirty;
    },
    
    /**
     * @description Reset the form, meaning all input values are reset
     * to their defaults (mostly empty) and all state markers removed (invalid e.g.).
     */
    reset: function()
    {
        var processed_inputs = [];

        for (var name in this.fields)
        {
            this.fields[name].reset();
            processed_inputs.push(name);
        }

        this.element.find(':input')
         .not(':button, :submit, :reset').each(
            function(idx, input)
            {
                if (-1 === processed_inputs.indexOf($(input).attr('name')))
                {
                    $(input).val('')
                     .removeAttr('checked')
                     .removeAttr('selected');
                }
            }
        );

        this.markClean();
    },
    
    /**
     * @description Triggers an ui effect letting all inputs flash for a short moment.
     */
    highlight: function()
    {
        this.element.find(':input')
         .not(':button, :submit, :reset')
         .effect("highlight", {}, 500);
    }
});
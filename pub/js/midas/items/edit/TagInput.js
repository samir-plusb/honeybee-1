/**
 * @class
 * @augments midas.items.edit.Input
 * @description <p>The TagInput handles.</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.TagInput = midas.items.edit.Input.extend(
/** @lends midas.items.edit.TagInput.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'TagInput',

    /**
     * Holds a list of tags that are currently selected.
     * @type Array
     */
    selected_tags: null,

    /**
     * The html list element holding the tag list items.
     * @type HTMLUlElement
     */
    tag_container: null,

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {HTMLElement} element The input element to enhance.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(element, options)
    {
        this.parent(element, options);
        this.tag_container = this.element.parents('ul');
        this.createTagHandler(this.options.assigned_tags);
    },

    /**
     * @description Creates an instance of the jQuery tagHandler plugin we are using.
     */
    createTagHandler: function(assigned_tags)
    {
        var new_container = $(document.createElement('ul'));
        this.tag_container.replaceWith(new_container);
        this.tag_container = new_container;
        this.selected_tags = $.isArray(assigned_tags) ? assigned_tags : [];

        this.tag_container.tagHandler({
            availableTags: this.getAvailableTags(),
            allowAdd: false,
            autocomplete: true,
            assignedTags: this.selected_tags,
            msgNoNewTag: "Das anlegen neuer Tags wird derzeit nicht unterstützt.",
            msgNoCallback: function(msg)
            {
                this.fire("createTagDenied", [this, msg]);
            }.bind(this),
            onTagAdded: function(tag)
            {
                this.prev_val = this.val().slice(0);
                this.selected_tags.push(tag);
                this.revalidate();
                this.fire('changed', [{prev: this.prev_val, cur: this.val()}]);
            }.bind(this),
            onTagRemoved: function(tag)
            {
                this.prev_val = this.val().slice(0);
                this.selected_tags.splice(
                    this.selected_tags.indexOf(tag),
                    1
                );
                this.revalidate();
                this.fire('changed', [{prev: this.prev_val, cur: this.val()}]);
            }.bind(this)
        });
    },

    /**
     * @description Returns an array of tags that are allowd for input.
     * @returns {Array}
     */
    getAvailableTags: function()
    {
        return this.options.tags || [];
    },

    // -----------
    // --------------- midas.items.edit.Input overrides
    // -----------

    /**
     * @description Getter and setter for our input's value.
     * When a parameter is supplied the method will behave as getter else as setter.
     * @param {Array} value [Optional] When given the method will act as a setter.
     * @returns {Array} When no parameter is passed the method will return the currently selected tags.
     */
    val: function()
    {
        if (1 === arguments.length)
        {
            this.createTagHandler(
                $.isArray(arguments[0]) ? arguments[0] : []
            );
            this.revalidate();
            return this;
        }

        return this.selected_tags;
    },

    /**
     * @description Marks the input to be in a given state.
     * @param {String} state The state we are marking.
     */
    markAs: function(state, data)
    {
        if (this.isMarkedAs(state))
        {
            return;
        }
        var css_class = this.options.ui_states[state]
         || 'input-' + state;

        if ('invalid' == state)
        {
            this.displayErrorHint(data.messages);
        }

        this.tag_container.addClass(css_class);
    },

    /**
     * @description Unmarks the input from being in the given state.
     * @param {String} state The state we are unmarking.
     */
    unmarkAs: function(state)
    {
        var css_class = this.options.ui_states[state]
         || 'input-' + state;

        if ('invalid' === state && this.error_hint)
        {
            this.error_hint.remove();
        }

        this.tag_container.removeClass(css_class);
    },

    /**
     * @description Tells if the input is currently marked for the given state.
     * @param {String} state The state to check for.
     */
    isMarkedAs: function(state)
    {
        var css_class = this.options.ui_states[state]
         || 'input-' + state;

        return this.tag_container.hasClass(css_class);
    },

    /**
     * @description Reset the input's value and recover from 'invalid' state.
     */
    reset: function()
    {
        this.val([]);
        this.unmarkAs('invalid');
    },

    displayErrorHint: function(messages)
    {
        var hint_element = this.renderErrorHint(messages);
        var el_pos = this.tag_container.parent().offset();
        var rel_pos = $('.document-editing').offset();

        var pos = {
            left: (el_pos.left - rel_pos.left),
            display: 'none'
        };
        hint_element.css(pos);
        $('.document-editing').append(hint_element);
        hint_element.css('top', el_pos.top - rel_pos.top - hint_element.height() - 6);

        this.tag_container.parent().mouseenter(function()
        {
            hint_element.fadeIn(250);
        });
        this.tag_container.parent().mouseleave(function()
        {
            hint_element.fadeOut(500);
        });
        this.error_hint = hint_element;
    }
});

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

    selected_tags: null,

    tag_container: null,

    init: function(element, options)
    {
        this.parent(element, options);
        this.tag_container = this.element.parents('ul');
        this.createTagHandler(this.options.assigned_tags);
    },

    createTagHandler: function(assigned_tags)
    {
        var new_container = $(document.createElement('ul'));
        this.tag_container.replaceWith(new_container);
        this.tag_container = new_container;
        this.selected_tags = $.isArray(assigned_tags) ? assigned_tags : [];

        this.tag_container.tagHandler({
            availableTags: this.getTags(),
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

    getTags: function()
    {
        return this.options.tags || [];
    },

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

    markAs: function(state)
    {
        var css_class = this.options.ui_states[state]
         || 'input-' + state;

        this.tag_container.addClass(css_class);
    },

    unmarkAs: function(state)
    {
        var css_class = this.options.ui_states[state]
         || 'input-' + state;

        this.tag_container.removeClass(css_class);
    },

    isMarkedAs: function(state)
    {
        var css_class = this.options.ui_states[state]
         || 'input-' + state;

        return this.tag_container.hasClass(css_class);
    },

    reset: function()
    {
        this.val([]);
        this.unmarkAs('invalid');
    }
});

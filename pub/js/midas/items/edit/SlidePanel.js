/**
 * @class
 * @augments midas.core.BaseObject
 * @description The SlidePanel allows you to slide a container from left to right.
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.SlidePanel = midas.core.BaseObject.extend(
/** @lends midas.items.edit.SlidePanel.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'SlidePanel',

    /**
     * Holds the panel that wraps our content and makes it slideable.
     * @type jQuery
     */
    element: null,

    /**
     * Internal helper var, that stores our slide state.
     * @type Boolean
     */
    has_slided: false,

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {jQuery} element The block level element (panel), that we will make slideable.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(element, options)
    {
        this.parent(options);
        this.element = element;

        if (! this.options.range)
        {
            this.logWarning("No valid range option supplied. Options:", this.options);
            throw "[SlidePanel] You must provide a valid range option.";
        }

        $(window.document.body).css('overflow-x', 'hidden');
    },

    /**
     * @description Toggle our slide state, thereby sliding in or out,
     * depending on wether we have previously been slided in or out.
     */
    toggle: function()
    {
        if (! this.has_slided)
        {
            this.slideIn();
            this.has_slided = true;
        }
        else
        {
            this.slideOut();
            this.has_slided = false;
        }
    },

    /**
     * @description Slide our panel in (left to right).
     */
    slideIn: function()
    {
        if (this.has_slided)
        {
            return;
        }

        this.slide("+=" + this.options.range, function()
        {
        }.bind(this));
    },

    /**
     * @description Slide our panel out (right to left).
     */
    slideOut: function()
    {
        if (! this.has_slided)
        {
            return;
        }

        this.slide("-=" + this.options.range, function()
        {
        }.bind(this));
    },

    /**
     * @description <p>Slide from left to right by the given range.</p>
     * <p>Negativ ranges will result in the oposite slide direction (right to left).</p>
     * @param {String} range A string determining how far to slide. For example: "23%".
     * @param {Function} (optional) Callback that is invoked when sliding has finished.
     */
    slide: function(range, callback)
    {
        this.element.animate({
            left: range
        }, this.options.duration || 500, this.options.transition || 'easeOutExpo', callback);
    }
});
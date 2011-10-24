midas.items.edit.SlidePanel = midas.core.BaseObject.extend({

    log_prefix: 'SlidePanel',

    panel: null,

    options: null,

    has_slided: false,

    init: function(panel, options)
    {
        $(window.document.body).css('overflow-x', 'hidden');

        this.panel = panel;
        this.options = options || {};
    },

    toggle: function()
    {
        if (! this.has_slided)
        {
            this.logInfo("open");
            this.slideIn();
            this.has_slided = true;
        }
        else
        {
            this.logInfo("close");
            this.slideOut();
            this.has_slided = false;
        }
    },

    slideIn: function()
    {
        if (this.has_slided)
        {
            return false;
        }

        this.slide('+=20em', function()
        {
            this.logInfo("slideIn complete.");
        }.bind(this));
    },

    slideOut: function()
    {
        if (! this.has_slided)
        {
            return false;
        }

        this.slide('-=20em', function()
        {
            this.logInfo("slideOut complete.");
        }.bind(this));
    },

    slide: function(range, callback)
    {
        this.panel.animate({
            left: range
        }, this.options.duration || 500, this.options.transition || 'easeOutExpo', callback);
    }
});
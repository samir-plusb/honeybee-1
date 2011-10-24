/**
 * The BaseView module provides base functionality for organizing client side behaviour
 * for a certain view.
 * It has access to the view's layout root and manages the various behaviours on a page
 * as aggregated components that derive from the Behaviour core module.
 */
midas.core.BaseView = midas.core.BaseObject.extend({

    log_prefix: "BaseView",

    controller: null,

    layout_root: null,

    options: null,

    init: function(controller, layout_root, options)
    {
        this.parent();

        this.controller = controller;
        this.layout_root = layout_root;
        this.options = options || {};

        if (typeof this.onInitGui == "function")
        {
            this.onInitGui.apply(this);
        }
    }
});
/**
 * @class
 * @augments midas.core.BaseObject
 * @description <p>The BaseView module provides base functionality for organizing client side behaviour for a certain view.</p>
 * <p>It has access to the view's layout root and manages the various behaviours on a page
 * as aggregated components that derive from the Behaviour core module.</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.core.BaseView = midas.core.BaseObject.extend(
/** @lends midas.core.BaseView.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: "BaseView",

    /**
     * Holds a list of controllers (midas.core.BaseController) that are currently attached to this view.
     * @type Array
     */
    controllers: null,

    /**
     * Holds a jQuery element that represents the layout root for the view's gui.
     * @type jQuery
     */
    layout_root: null,

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {midas.items.edit.EditController} controller The view's (parent) controller.
     * @param {jQuery} layout_root
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(layout_root, options)
    {
        this.parent(options);
        this.controllers = [];
        this.layout_root = layout_root;

        // Hook method that child classes may implement.
        if (typeof this.onInitGui == "function")
        {
            this.onInitGui.apply(this);
        }
    },

    propagateIntent: function(intent)
    {
        for (var i = 0; i < this.controllers.length; i++)
        {
            if (this.controllers[i].apply(intent))
            {
                return true;
            }
        }

        this.logWarning("No one liked your intent.");

        return false;
    },

    /**
     * @description <p>Attaches the given controller to the view.</p>
     * <p>Controllers that are attached to a view participate in the view's intent propagation and may take action,
     * if they feel responseable for handling a certain action.</p>
     * @type {midas.core.BaseController}
     */
    attach: function(controller)
    {
        for (var i = 0; i < this.controllers.length; i++)
        {
            if (this.conrollers[i] === controller)
            {
                this.logWarning("The given controller has allready been attached.");
                return;
            }
        }

        this.controllers.push(controller);
    },


    dettach: function(controller)
    {
        for (var i = 0; i < this.controllers.length; i++)
        {
            if (this.conrollers[i] === controller)
            {
                this.logWarning("The given controller has allready been attached.");
                return;
            }
        }

        this.controllers.push(controller);
    }
});
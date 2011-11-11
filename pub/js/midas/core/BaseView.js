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
     * @param {HTMLElement} layout_root
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(layout_root, options)
    {
        this.parent(options);
        this.controllers = [];
        this.layout_root = $(layout_root);

        // Hook method that child classes may implement.
        if (typeof this.onInitGui == "function")
        {
            this.onInitGui.apply(this);
        }
    },
    
    /**
     * Propagte an intent to all attached controllers.
     * @param {Object} intent
     * @returns {Boolean} True if the intent was handled else false.
     */
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
        if (! (controller instanceof midas.core.BaseController))
        {
            throw "Only midas.core.BaseController instances may be attached to a BaseView instance";
        }
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

    /**
     * @description <p>Dettaches the given controller from the view.</p>
     * @type {midas.core.BaseController}
     */
    dettach: function(controller)
    {
        var index = this.controllers.indexOf(conroller);
        if (-1 < index)
        {
            this.controllers = this.controllers.splice(index, 1);
        }
    },
    
    // -----------
    // --------------- DIALOG HANDLING
    // -----------
    
    /**
     * @description Displays a confirm dialog.
     * @param {String} title The text to display as the dialog's title.
     * @param {String} message The message to display inside the dialog body.
     * @param {Function} confirm Callback that is invoked when the dialog is confirmed.
     * @param {Function} abort Callback that is invoked when the dialog is cancelled.
     */
    confirm: function(title, message, confirm, abort)
    {
        if (! this.layout_root.confirm_dialog)
        {
            this.layout_root.confirm_dialog = ich['dialog-tpl']({ title: title, message: message });
            this.layout_root.append(this.layout_root.confirm_dialog);
            this.layout_root.confirm_dialog.dialog({
                resizable: false,
                modal: true,
                width: '20em',
                buttons: {
                    "Ja": function() {
                        $(this).dialog("close");
                        if (confirm)
                        {
                            confirm();
                        }
                    },
                    "Nein": function() {
                        $(this).dialog("close");
                        if (abort)
                        {
                            abort();
                        }
                    }
                }
            });
        }
        else
        {
            this.layout_root.confirm_dialog.dialog("open");
        }
    },
    
    /**
     * @description Displays a warning dialog.
     * @param {String} title The text to display as the dialog's title.
     * @param {String} message The message to display inside the dialog body.
     * @param {Function} ok Callback that is invoked when the dialog acknowledged.
     */
    warn: function(title, message, ok)
    {
        if (! this.layout_root.warn_dialog)
        {
            this.layout_root.warn_dialog = ich['dialog-tpl']({ title: title, message: message });
            this.layout_root.append(this.layout_root.warn_dialog);
            this.layout_root.warn_dialog.dialog({
                resizable: false,
                modal: true,
                width: '20em',
                buttons: {
                    "Ok": function() {
                        $( this ).dialog( "close" );
                        if (ok) ok();
                    }
                }
            }).prev().addClass('ui-state-error'); ;
        }
        else
        {
            this.layout_root.warn_dialog.dialog("open");
        }
    }
});
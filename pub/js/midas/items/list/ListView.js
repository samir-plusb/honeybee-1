/**
 * @class
 * @augments midas.core.BaseView
 * @description The ListView module manages all behaviour for the system's Items/ListSuccessView.
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.list.ListView = midas.core.BaseView.extend(
/** @lends midas.items.list.ListView.prototype */
{
    // -----------
    // --------------- PROPERTIES
    // -----------

    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: "ListView",

    search_box: null,

    error_dialog: null,

    confirm_dialog: null,

    // -----------
    // --------------- CONSTRUCTION / GUI INITIALIZING
    // -----------

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {HTMLElement} element The view's layout root.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(element, options)
    {
        this.parent(element, options);

        this.search_box = new midas.items.list.SearchBox('.search-form');
        this.error_dialog = $('#ajax-error');
        this.confirm_dialog = $('#confirm-dialog');
        this.registerEvents();
    },

    registerEvents: function()
    {
        var that = this;
        this.error_dialog.find('.modal-footer button').click(function()
        {
            that.error_dialog.modal('hide');
        });
        this.confirm_dialog.find('.modal-footer .confirm').click(function()
        {
            if (that.confirm_dialog._callback)
            {
                that.confirm_dialog._callback();
            }
            that.confirm_dialog.modal('hide');
        });
        this.confirm_dialog.find('.modal-footer .deny').click(function()
        {
            that.confirm_dialog.modal('hide');
        });
        $("table").delegate(".title a", "click", function(event)
        {
            event.preventDefault();
            that.editItem($(this));
        });
        $("table").delegate(".owner a", "click", function(event)
        {
            event.preventDefault();
            that.releaseTicket($(this));
        });
        $("table").delegate(".delete-item", "submit", function(event)
        {
            if (event.originalEvent)
            {
                // the submit event was triggered per gui
                // and not by calling form.submit from code.
                event.preventDefault();
                that.deleteItem($(this));
            }
        });
    },

    // -----------
    // --------------- EVENT CALLBACKS / WORKING METHODS
    // -----------

    editItem: function(edit_link)
    {
        this.grabTicket(edit_link, function(err, resp)
        {
            if (! err)
            {
                window.location.href = edit_link.attr('href');
            }
        });
    },

    deleteItem: function(delete_form)
    {
        this.confirm_dialog.find('.error-title').text(
            'Command Confirmation'
        );
        this.confirm_dialog.find('.error-text').text(
            "Are you sure you want to delete this item?"
        );
        this.confirm_dialog.find('.confirm').focus();
        this.confirm_dialog.modal('show');
        var that = this;
        this.confirm_dialog._callback = function()
        {
            that.deactivateRow(delete_form.parents('tr'));
            that.grabTicket(delete_form, function(err, resp)
            {
                if (! err)
                {
                    $.post(delete_form.attr('action'), delete_form.serialize(),function(data)
                    {
                        delete_form.parents('tr').remove();
                    }, 'json');
                }
            });
        };
    },

    grabTicket: function(ticket_src, callback)
    {
        var grab_url = ticket_src.attr('data-checkout-url');
        var that = this;
        $.getJSON(grab_url, function(resp)
        {
            if ('ok' === resp.state)
            {
                callback(null, resp);
            }
            else
            {
                that.error_dialog.find('.error-title').text(
                    "Taking ticket ownership failed!"
                );
                that.error_dialog.find('.error-text').text(resp.msg);
                that.error_dialog.modal('show');
                callback(resp, null);
            }
        });
    },

    releaseTicket: function(release_link)
    {
        var release_url = release_link.attr('href');
        var that = this;
        $.getJSON(release_url, function(resp)
        {
            if ('ok' !== resp.state)
            {
                that.error_dialog.find('.error-title').text(
                    "Releasing ticket ownership failed!"
                );
                that.error_dialog.find('.error-text').text(resp.msg);
                that.error_dialog.modal('show');
            }
            else
            {
                release_link.fadeOut(250, function()
                {
                    release_link.replaceWith(
                        $('<span class="label">nobody</span>')
                    );
                });
            }
        });
    },

    deactivateRow: function(row)
    {
        var edit_link = row.find('.title a');
        edit_link.replaceWith(
            $('<span>'+edit_link.text()+"</span>")
        );
    }
});
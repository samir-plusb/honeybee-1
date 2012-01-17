/**
 * @class
 * @augments midas.core.BaseView
 * @description The EditView module manages all behaviour for the system's Items/EditSuccessView.
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.EditView = midas.core.BaseView.extend(
/** @lends midas.items.edit.EditView.prototype */
{
    // -----------
    // --------------- PROPERTIES
    // -----------

    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: "EditView",

    /**
     * Holds an SlidePanel instance used to slide our content.
     * @type midas.items.edit.SlidePanel
     */
    slide_panel: null,

    /**
     * Represents a set of commands that can be triggered for manipulating content-item state.
     * @type midas.core.CommandTriggerList
     */
    content_item_menu: null,

    /**
     * Holds a jquery element, that represents our content-item editing form.
     * @type midas.items.edit.EditForm
     */
    editing_form: null,

    /**
     * Represents a set of commands that can be triggered for manipulating import-item state.
     * @type midas.core.CommandTriggerList
     */
    import_item_menu: null,

    /**
     * Holds a jquery element, that represents our import item content-panel.
     * @type jQuery
     */
    import_item_container: null,

    /**
     * Holds an object that maps context-menu item-keys to view actions.
     * @type Object
     */
    context_menu_actions: null,

    /**
     * Holds the service we use to have things like localization and data extraction done.
     * @type midas.items.edit.EditService
     */
    edit_service: null,

    /**
     * Holds a list component that contains and renders all content-items,
     * providing methods to access and modify the latter collection.
     * @type midas.items.edit.ContentItemsList
     */
    items_list: null,

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
        this.edit_service = new midas.items.edit.EditService({
            api: {
                extract_date: 'index.php/de/items/api/extract_date',
                validate_url: 'index.php/de/items/api/validate_url',
                extract_location: 'index.php/de/items/api/api/extract_location'
            }
        });
    },

    /**
     * @description <p>Initializes our gui (behaviours).</p>
     * <p>This method is invoked from midas.core.BaseView, upon init invocation.</p>
     */
    onInitGui: function()
    {
        this.createSlidePanel()
            .createContentItemsList()
            .createImportItemContainer()
            .createMenus()
            .createEditForm();

        window.onunload = function()
        {
            var release_url = $('.release_ticket_url').val();
            if (release_url)
            {
                $.getJSON(release_url);
            }
            return false;
        };
    },

    /**
     * @description Creates the view's slide panel,
     * a component that is used to slide in/out the content item list.
     * @returns {midas.items.edit.EditView} Returns the same instance for fluent api support.
     */
    createSlidePanel: function()
    {
        this.slide_panel = new midas.items.edit.SlidePanel(
            this.layout_root.find('.slide-panel').first()
            .css({'position': 'absolute', 'width': '100%'}),
            {range: '20em'}
        ).on('slideinstart', function()
        {
            var list_button = $('.action-list');
            list_button.html(list_button[0].orgText + " &#9667;");
            $('.import-data-layoutbox').animate({'opacity': 0.4}, 500);
            $('.import-data-layoutbox .overlay').css('display', 'block').animate({'opacity': 0.5}, 500);
        }).on("slideoutstart", function()
        {
            var list_button = $('.action-list');
            list_button.html(list_button[0].orgText + " &#9657;");
            $('.import-data-layoutbox').animate({'opacity': 1}, 500);
            $('.import-data-layoutbox .overlay').animate({'opacity': 0}, 500, function()
            {
                 $('.import-data-layoutbox .overlay').css('display', 'none');
            });
        });
        var list_button = $('.action-list');
        list_button[0].orgText = list_button.text();
        list_button.html(list_button[0].orgText + " &#9657;");
        $('.import-data-layoutbox').append($('<div class="overlay"></div>').css('opacity', 0).css('display', 'none'));
        return this;
    },

    /**
     * @description Creates the content item list,
     * a component that reflects a list of content items,
     * thereby rendering them and providing read/write access to the underlying collection.
     * @returns {midas.items.edit.EditView} Returns the same instance for fluent api support.
     */
    createContentItemsList: function()
    {
        // Set our content-items-list container position to be just outside our viewport,
        // so it will slide in to view when our slide_panel's slideIn method is executed.
        var items_container = this.layout_root.find('.content-items').first();
        items_container.css('left', -items_container.outerWidth());
        var that = this;
        this.items_list = new midas.items.edit.ContentItemsList(
            items_container.find('ul').first(), {
                items: this.loadItems(),
                state_display: this.layout_root.find('.info-small')
            }
        )
        .on('itemClicked', function(item)
        {
            if (that.items_list.org_item)
            {
                that.items_list.org_item = null;
            }
            that.loadContentItem(item);
        })
        .on('itemEnter', function(item)
        {
            clearTimeout(that.items_list.timer);
            var delay = that.items_list.org_item ? 100 : 500;
            that.items_list.timer = setTimeout(function()
            {
                if (! that.items_list.org_item)
                {
                    that.items_list.org_item = {
                        data: that.editing_form.val(),
                        dirty: that.editing_form.isDirty()
                    };
                }
                if (item.data.cid == that.items_list.org_item.data.cid)
                {
                    $('.document-editing').removeClass('preview');
                    // @todo Sugar: apply diff if the item is the same as loaded.
                    // Overwrite item.data for this, before passing it to the form.
                }
                else
                {
                    $('.document-editing').addClass('preview');
                }
                that.editing_form.val(item.data);
            }, delay);
        })
        .on('itemLeave', function(item)
        {
            clearTimeout(that.items_list.timer);
            that.items_list.timer = setTimeout(function()
            {
                that.items_list.timer = null;
                $('.document-editing').removeClass('preview');
                if (! that.items_list.org_item)
                {
                    return;
                }
                that.editing_form.val(that.items_list.org_item.data);
                if (! that.items_list.org_item.dirty)
                {
                    that.editing_form.markClean();
                }
                that.items_list.org_item = null;
            }, 50);
        });
        return this;
    },

    /**
     * @description Creates the import item container,
     * a component that wraps up the part of the gui dedicated to presenting
     * the current import item's data and providing behaviour such as tabs and context-menu.
     * @returns {midas.items.edit.EditView} Returns the same instance for fluent api support.
     */
    createImportItemContainer: function()
    {
        this.import_item_container = new midas.items.edit.ImportItemContainer(
            this.layout_root.find('.document-data').first(),
            {tabs_container: '.item-content'}
        ).on('contextMenuSelect', this.onContextMenuClicked.bind(this));
        return this;
    },

    /**
     * @description Creates the edit form,
     * a component that reflects the content item editing form
     * and provides behaviour such as validation, getting and setting values.
     * @returns {midas.items.edit.EditView} Returns the same instance for fluent api support.
     */
    createEditForm: function()
    {
        this.editing_form = new midas.items.edit.EditForm(
            this.layout_root.find('.document-editing form')
        )
        .on('changed', function()
        {
            this.editing_form.markDirty();
        }.bind(this))
        .on('contextMenuSelect', this.onContextMenuClicked.bind(this))
        .on('createTagDenied', function(form, field, msg)
        {
            this.warn("Aktion nicht erlaubt", msg);
        }.bind(this));
        return this;
    },

    /**
     * @description Creates the top level menus of the view.
     * One for triggering actions related to the import item container
     * and one for controlling the content item related components (slide panel and form).
     * @returns {midas.items.edit.EditView} Returns the same instance for fluent api support.
     */
    createMenus: function()
    {
        this.context_menu_actions = this.getContextMenuBindings();
        this.content_item_menu = new midas.core.CommandTriggerList(
            this.layout_root.find('#content-item-menu'),
            {'commands': this.getContentItemMenuBindings()}
        );
        this.import_item_menu = new midas.core.CommandTriggerList(
            this.layout_root.find('#import-item-menu'),
            {'commands': this.getImportItemMenuBindings()}
        );
        return this;
    },

    /**
     * @description Load a list of content items.
     * @returns {Array} An array containing content-item objects.
     */
    loadItems: function()
    {
        var data_input = $('.content-list-src');
        if (data_input)
        {
            var items = $.parseJSON(data_input.val());
            for (var i = 0; i < items.length; i++)
            {
                items[i].cid = midas.core.CidSequence.nextCid('content_item');
            }
            data_input.remove();
            return items;
        }
        return [];
    },

    // -----------
    // --------------- INTERACTION BINDINGS
    // -----------

    /**
     * @description Returns a set of bound functions that define the set
     * of commands available for manipulating the state of content-item related components (slide panel and form).
     * @returns {Object}
     */
    getContentItemMenuBindings: function()
    {
        return {
            'store': this.storeContentItem.bind(this),
            'delete': this.deleteContentItem.bind(this),
            'new': this.createNewContentItem.bind(this),
            'list': this.slide_panel.toggle.bind(this.slide_panel)
        };
    },

    /**
     * @description Returns a set of bound functions that define the set
     * of commands available for manipulating the import-item container's state.
     * @returns {Object}
     */
    getImportItemMenuBindings: function()
    {
        return {
            'prev': function() {this.logDebug('onImportItemPrev');}.bind(this),
            'delete': function() {this.logDebug("onImportItemDelete");}.bind(this),
            'mark': this.markImportItem.bind(this),
            'next': function() {this.logDebug('onImportItemNext');}.bind(this)
        };
    },

    /**
     * @description Returns a set of functions that are bound to the contextmenu
     * commands of the content item form and import container components.
     * @returns {Object}
     */
    getContextMenuBindings: function()
    {
        var that = this
        // Helper function for setting input values.
        var setInputValue = function(target_fieldname, append, src_field)
        {
            var value = src_field.getSelection();
            if (true === append)
            {
                value = this.editing_form.val(target_fieldname) + value;
            }

            this.editing_form.val(target_fieldname, value);
        };
        // Helper function for setting dates [from or till].
        var setDate = function(target_fieldname, src_field)
        {
            this.edit_service.extractDate(src_field.getSelection(), function(date)
            {
                that.editing_form.val(target_fieldname, date);
            });
        };

        return {
            'set_title': setInputValue.bind(this, 'title', false),
            'append_title': setInputValue.bind(this, 'title', true),
            'set_text': setInputValue.bind(this, 'text', false),
            'append_text': setInputValue.bind(this, 'text', true),
            'set_startdate': setDate.bind(this, 'date[from]'),
            'set_enddate': setDate.bind(this, 'date[till]'),
            'remove_hyphens': function(src_field)
            {
                var selection = src_field.getSelection();
                src_field.val(
                    src_field.val().replace(
                        selection,
                        that.edit_service.removeHyphens(selection)
                    )
                );
            },
            'remove_linefeeds': function(src_field)
            {
                var selection = src_field.getSelection();
                src_field.val(
                    src_field.val().replace(
                        selection,
                        that.edit_service.removeLineFeeds(selection)
                    )
                );
            },
            'set_url': function(src_field)
            {
                var urls = that.edit_service.extractUrls(
                    src_field.getSelection()
                );
                if (0 < urls.length)
                {
                    that.editing_form.val('url', urls[0].trim());
                }
            },
            'localize': function()
            {
                that.logDebug("on::contentMenu::localize::clicked");
            }
        };
    },

    // -----------
    // --------------- HANDLING CONTENT ITEM DATA
    // -----------

    markImportItem: function()
    {
        var ticket = this.editing_form.val('ticket');
        if (ticket)
        {
            // create intent to pass to our attached controllers.
            var intent = {
                'name': '/midas/intents/importItem/mark',
                'data': {
                    ticket: ticket,
                    gate: 'publish'
                },
                'target_uri': 'index.php/de/workflow/proceed'
            };
            this.propagateIntent(intent);
        }
    },

    /**
     * @description Loads the given content item into the form.
     * If the form is currently marked as dirty (modified and not saved),
     * the user will be prompted for storing before load or immediately loading the item.
     */
    loadContentItem: function(item)
    {
        var that = this;
        var load = function()
        {
            that.editing_form.val(item.data);
            that.editing_form.markClean();
            that.slide_panel.toggle();
        };
        var store_and_load = function()
        {
            that.storeContentItem(load, function(err)
            {
                this.logDebug("loadCOntentItem::store_and_load.error", err);
            });
        };
        if (this.editing_form.isDirty())
        {
            this.confirm("Item wurde noch nicht gespeichert!", "Jetzt speichern?", store_and_load, load);
        }
        else
        {
            load();
        }
    },

    /**
     * @description Creates a new content item.
     * If the form is currently marked as dirty the user will be prompted
     * to store his changes before loading.
     */
    createNewContentItem: function()
    {
        if (this.editing_form.isDirty())
        {
            var that = this;
            var store_and_reset = function()
            {
                that.storeContentItem(
                    that.editing_form.reset.bind(that.editing_form)
                );
            };
            this.confirm(
                "Drohender Datenverlust",
                "Item wurde noch nicht gespeichert. Jetzt speichern?",
                store_and_reset,
                this.editing_form.reset.bind(this.editing_form)
            );
        }
        else
        {
            this.editing_form.reset();
        }
    },

    /**
     * @description Propagtes a save intent to all attached controllers,
     * which call either our success or error callback when they have completed.
     * @param {Function} callback Invoked when an store intent has succesfully been dispatched.
     * @param {Function} err_callback Invoked when preparing the store intent failed due to validation or other incidents.
     */
    storeContentItem: function(callback, err_callback)
    {
        err_callback = 'Function' == typeof err_callback ? err_callback : function() {};
        callback = 'Function' == typeof callback ? callback : function() {};
        var validation_res = this.editing_form.validate();
        var that = this;
        // does the actual storing and is called if the data validation complies.
        var store_data = function()
        {
            var item = that.editing_form.val();

            if (0 == +item.cid)
            {
                // for new items set an identifier and cid
                item.cid = midas.core.CidSequence.nextCid('content_item');
                var item_pos = that.items_list.size() + 1;
                item.identifier = item.parentIdentifier + '-' + item_pos;
                that.editing_form.val('cid', item.cid);
                that.editing_form.val('identifier', item.identifier);
            }
            var ticket = item.ticket;
            delete item.ticket;
            // create intent to pass to our attached controllers.
            var intent = {
                'name': '/midas/intents/contentItem/store',
                'data': {
                    content_item: item,
                    ticket: ticket
                },
                'target_uri': 'index.php/de/workflow/run'
            };
            // @todo update the content item state from the list
            // and decide if and how we want to reflect the state changes in the gui.
            that.propagateIntent(intent, callback);
            that.items_list.add(item);
            that.editing_form.highlight();
            that.editing_form.markClean();
        };
        if (true == validation_res.success)
        {
            // after validation, soft check for location data and warn if they have not been provided.
            var latitude = +this.editing_form.val('location[coordinates][latitude]');
            var longitude = +this.editing_form.val('location[coordinates][longitude]');

            if (0 < latitude && 0 < longitude)
            {
               store_data();
            }
            else
            {
                this.confirm(
                    "Lokalisierung fehlt!",
                    "Dieses Item wurde noch nicht lokalisiert. Bist Du sicher, dass Du das Item ohne Lokalisierung speichern willst?",
                    store_data,
                    function()
                    {
                        err_callback({type: 'location', data: null, msg: "Location not provided, save aborted by user."});
                    }
                );
            }
        }
        else
        {
            this.displayValidationNotification();
            err_callback({type: 'validation', data: validation_res, msg: "EditForm validation failed."});
        }
    },

    displayValidationNotification: function()
    {
        var lower_bound = $(document).scrollTop() + $(window).height();
        var upper_bound = $('.document-editing').offset().top;
        var input_visible = false;
        var isVisible = function(item)
        {
            item = $(item);
            var bottom_pos = (item.offset().top + item.height()) - $(document).scrollTop();
            if (item.offset().top < lower_bound && bottom_pos > upper_bound)
            {
                return true;
            }
        };
        $('.ui-state-error').each(function(idx, item)
        {
            if (isVisible(item))
            {
                input_visible = true;
            }
        });
        if (input_visible)
        {
            // one of the invalid inputs is inside the viewport.
            // no need to show a hint.
            return;
        }
        // none of the invalid inputs are inside the viewport.
        // show a hint to the user indicating in which direction he has to scroll to find the first invalid input.
        // if he clicks it we'll take him there
        var first_invalid = $('.ui-state-error').first();
        var bottom_pos = (first_invalid.offset().top + first_invalid.height()) - $(document).scrollTop();
        if (first_invalid.offset().top > lower_bound)
        {
            this.renderValidationHint('bottom');
        }
        else if (bottom_pos < upper_bound)
        {
            this.renderValidationHint('top');
        }
    },

    renderValidationHint: function(pos)
    {
        var hint;
        var main_data = $('.main-data');

        if (pos == 'top')
        {
            hint = $('<div class="validation-hint top"><h4>&uarr;</h4><p>Bitte hoch scrollen</p></div>');
        }
        else
        {
            hint = $('<div class="validation-hint bottom"><p>Bitte runter scrollen</p><h4>&darr;</h4>');
        }
        $(document.body).append(hint);
        hint.css('left', main_data.offset().left + (main_data.width() / 2) - (hint.width() / 2));
        hint.animate({
            opacity: 0
        }, 3000, function() { hint.remove(); });
    },

    /**
     * @description Propagtes a delete intent to all attached controllers,
     * which call either our success or error callback when they have completed.
     */
    deleteContentItem: function()
    {
        var item = this.items_list.getItem(this.editing_form.val('cid'));
        if (item)
        {
            item = item.data;
            this.items_list.remove(item.cid);
            this.editing_form.reset();
            var ticket = this.editing_form.val('ticket');
            // create intent to pass to our attached controllers.
            var intent = {
                'name': '/midas/intents/contentItem/delete',
                'data': {
                    content_item: item.identifier,
                    ticket: ticket
                },
                'target_uri': 'index.php/de/items/api/delete_item'
            };
            this.propagateIntent(intent);
        }
    },

    // -----------
    // --------------- EVENT HANDLERS
    // -----------

    /**
     * @description Event handler that maps contextmenu events
     * to the corresponding bound contextmenu callback, see this.getContextMenuBindings()
     */
    onContextMenuClicked: function(content_field, menu_item)
    {
        if (this.context_menu_actions[menu_item.key])
        {
            this.context_menu_actions[menu_item.key].apply(this, [content_field]);
        }
    },

    /**
     * @description Event callback that is invoked when a content-item
     * has been persisted to the backend.
     */
    onContentItemStored: function(cid)
    {
        this.logInfo("Content item with cid: " + cid + " has successfully been stored.");
    },

    /**
     * @description Event callback that is invoked when a content-item
     * has been deleted from the backend.
     */
    onContentItemDeleted: function(cid)
    {
        this.logInfo("Content item with cid: " + cid + " has successfully been deleted.");
    }
});
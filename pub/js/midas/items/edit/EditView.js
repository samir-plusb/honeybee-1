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
        this.edit_service = new midas.items.edit.EditService();
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
        );
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
        this.items_list = new midas.items.edit.ContentItemsList(
            items_container.find('ul').first(), { 
                items: this.loadItems(),
                state_display: this.layout_root.find('.info-small')
            }
        ).on('itemClicked', this.loadContentItem.bind(this));
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
            'mark': function() {this.logDebug('onImportItemMark');}.bind(this),
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
            var selection = src_field.getSelection();
            var that = this;
            // @todo Configure per option. Might wanna make the view a behaviour.
            $.getJSON('http://localhost/contentworker/index.php/items/extract_date', {date_text: selection}, function(data)
            {
                that.editing_form.val(target_fieldname, data.date);
            });
        }
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
                        this.edit_service.removeHyphens(selection)
                    )
                );
            }.bind(this),
            'remove_linefeeds': function(src_field)
            {
                var selection = src_field.getSelection();
                src_field.val(
                    src_field.val().replace(
                        selection,
                        this.edit_service.removeLineFeeds(selection)
                    )
                );
            }.bind(this),
            'set_url': function(src_field)
            {
                var selection = src_field.getSelection();
                var urls = this.edit_service.extractUrls(selection);
                if (0 < urls.length)
                {
                    var url = urls[0].trim();

                    if (-1 === url.indexOf('http'))
                    {
                        url = 'http://' + url;
                    }
                    this.editing_form.val('url', url);
                }
            }.bind(this)
        };
    },

    // -----------
    // --------------- HANDLING CONTENT ITEM DATA
    // -----------
    
    /**
     * @description Loads the given content item into the form.
     * If the form is currently marked as dirty (modified and not saved),
     * the user will be prompted for storing before load or immediately loading the item.
     */
    loadContentItem: function(item)
    {
        var load = function()
        {
            this.editing_form.val(item.data);
            this.editing_form.markClean();
            this.slide_panel.toggle();
        }.bind(this);
        
        var store_and_load = function()
        {
            if (! this.storeContentItem())
            {
                this.warn("Fehler", "Item konnte aufgrund unvollständiger Daten nicht gespeichert werden.");
            }
            else
            {
                load();
            }
        }.bind(this);
        
        if (this.editing_form.isDirty())
        {
            this.confirm("Item wurde noch nicht gespeichert!", "Jetzt speichern?", store_and_load, function()
            {
                load();
            });
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
                if (that.storeContentItem())
                {
                    that.editing_form.reset();
                }
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
     */
    storeContentItem: function()
    {
        var validation_res = this.editing_form.validate();

        if (true == validation_res.success)
        {
            var item = this.editing_form.val();

            if (! item.cid)
            {
                item.cid = midas.core.CidSequence.nextCid('content_item');
            }

            var intent = {
                'name': '/midas/intents/contentItem/store',
                'data': item
            };

            this.propagateIntent(intent);
            this.items_list.add(item);
            this.editing_form.highlight();
            this.editing_form.markClean();
            // @todo Pass a success callback that updates the content item state from the list
            // and decide if and how we want to reflect the state changes in the gui.
            return true;
        }

        return false;
    },
    
    /**
     * @description Propagtes a delete intent to all attached controllers,
     * which call either our success or error callback when they have completed.
     */
    deleteContentItem: function()
    {
        if (this.items_list.remove(this.editing_form.val('cid')))
        {
            this.editing_form.reset();
            this.propagateIntent({
                'name': '/midas/intents/contentItem/delete',
                'data': {}
            });
            
            // @todo Pass a success callback that purges the content item from the list
            // and decide if and how we want to express the pending delete operation in the gui.
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
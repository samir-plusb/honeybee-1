/**
 * @class
 * @augments midas.core.BaseObject
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

    edit_service: null,

    items_list: null,

    // -----------
    // --------------- CONSTRUCTION / GUI INITIALIZING
    // -----------

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
        this.createSlidePanel();
        this.createContentItemsList();
        this.createImportItemContainer();
        this.createMenus();
        this.createEditForm();
    },

    createSlidePanel: function()
    {
        this.slide_panel = new midas.items.edit.SlidePanel(
            this.layout_root.find('.slide-panel').first()
            .css({'position': 'absolute', 'width': '100%'}),
            {range: '20em'}
        );
    },

    createContentItemsList: function()
    {
        // Set our content-items-list container position to be just outside our viewport,
        // so it will slide in to view when our slide_panel's slideIn method is executed.
        var items_container = this.layout_root.find('.content-items').first();
        items_container.css('left', -items_container.outerWidth());

        this.items_list = new midas.items.edit.ContentItemsList(
            items_container.find('ul').first(),
            {items: this.loadItems(), state_display: this.layout_root.find('.info-small')}
        );
        this.items_list.on('itemClicked', this.loadContentItem.bind(this));
    },

    createImportItemContainer: function()
    {
        this.import_item_container = new midas.items.edit.ImportItemContainer(
            this.layout_root.find('.document-data').first(),
            {tabs_container: '.item-content'}
        );
        this.import_item_container.on(
            'contextMenuSelect',
            this.onContextMenuClicked.bind(this)
        );
    },

    createEditForm: function()
    {
        this.editing_form = new midas.items.edit.EditForm(
            this.layout_root.find('.document-editing form')
        );

        this.editing_form.on('changed', function()
        {
            this.editing_form.markDirty();
        }.bind(this));
        this.editing_form.on('contextMenuSelect', this.onContextMenuClicked.bind(this));
        this.editing_form.on('createTagDenied', function(form, field, msg)
        {
            this.warn("Aktion nicht erlaubt", msg);
        }.bind(this));
    },

    createMenus: function()
    {
        this.content_item_menu = new midas.core.CommandTriggerList(
            this.layout_root.find('#content-item-menu'),
            {'commands': this.getContentItemMenuBindings()}
        );
        this.import_item_menu = new midas.core.CommandTriggerList(
            this.layout_root.find('#import-item-menu'),
            {'commands': this.getImportItemMenuBindings()}
        );

        this.context_menu_actions = this.getContextMenuBindings();
    },

    loadItems: function()
    {
        return [
            {
                'cid': midas.core.CidSequence.nextCid('content_items'),
                'title': 'Mondkuchenfest in den Gärten der Welt ',
                'text': 'Ein buntes Bühnenprogramm mit asiatischen Drachentänzen, Ausschnitten aus der Peking-Oper und asi... ',
                'date[from]': '28.12.1981'
            }
        ];
    },

    // -----------
    // --------------- INTERACTION BINDINGS
    // -----------

    /**
     * @description Returns a set of bound functions that define the set
     * of commands available for manipulating content-item state.
     * @returns {Object}
     */
    getContentItemMenuBindings: function()
    {
        return {
            'store': this.storeContentItem.bind(this),
            'delete': this.deleteContentItem.bind(this),
            'new': this.createContentItem.bind(this),
            'list': this.slide_panel.toggle.bind(this.slide_panel)
        };
    },

    /**
     * @description Returns a set of bound functions that define the set
     * of commands available for manipulating import-item state.
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

    getContextMenuBindings: function()
    {
        var setInputValue = function(target_fieldname, append, src_field)
        {
            var value = src_field.getSelection();
            if (true === append)
            {
                value = this.editing_form.val(target_fieldname) + value;
            }

            this.editing_form.val(target_fieldname, value);
        };

        return {
            'set_title': setInputValue.bind(this, 'title', false),
            'append_title': setInputValue.bind(this, 'title', true),
            'set_text': setInputValue.bind(this, 'text', false),
            'append_text': setInputValue.bind(this, 'text', true),
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
    // --------------- EVENT HANDLERS
    // -----------

    confirm: function(title, message, abort, confirm)
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
                        $( this ).dialog( "close" );
                        if (confirm) confirm();
                    },
                    "Nein": function() {
                        $( this ).dialog( "close" );
                        if (abort) abort();
                    }
                }
            });
        }
        else
        {
            this.layout_root.confirm_dialog.dialog("open");
        }
    },

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
    },

    loadContentItem: function(item)
    {
        var load = function()
        {
            this.editing_form.val(item.data);
            this.editing_form.markClean(); 
            this.slide_panel.toggle();
        }.bind(this);

        this.logInfo("loadContentItem", item);

        if (this.editing_form.isDirty())
        {
            this.confirm("Item wurde noch nicht gespeichert!", "Jetzt speichern?", function()
            {
                load();

                return true;
            }, function()
            {
                if (! this.storeContentItem())
                {
                    this.warn("Fehler", "Item konnte aufgrund unvollständiger Daten nicht gespeichert werden.");

                    return false;
                }

                load();

                return true;
            }.bind(this));
        }
        else
        {
            load();
        }
    },

    createContentItem: function()
    {
        if (this.editing_form.isDirty())
        {
            this.confirm(
                "Drohender Datenverlust",
                "Item wurde noch nicht gespeichert. Jetzt speichern?",
                function()
                {
                    if (this.storeContentItem())
                    {
                        this.editing_form.reset();
                    }
                }.bind(this)
            );
        }
        else
        {
            this.editing_form.reset();
        }
    },

    storeContentItem: function()
    {
        var validation_res = this.editing_form.validate();

        if (true === validation_res.success)
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

            this.editing_form.markClean(); // @todo fe dev only, remove l8r
            this.editing_form.highlight();
            return true;
        }

        return false;
    },

    deleteContentItem: function()
    {
        if (this.items_list.remove(this.editing_form.val('cid')))
        {
            this.editing_form.reset();
            this.propagateIntent({
                'name': '/midas/intents/contentItem/delete',
                'data': {}
            });
        }
    },

    onContextMenuClicked: function(content_field, menu_item)
    {
        if (this.context_menu_actions[menu_item.key])
        {
            this.context_menu_actions[menu_item.key].apply(this, [content_field]);
        }
    },

    onContentItemStored: function(cid)
    {
        this.logInfo("Content item with cid: " + cid + " has successfully been stored.");

        this.editing_form.markClean();
    }
});
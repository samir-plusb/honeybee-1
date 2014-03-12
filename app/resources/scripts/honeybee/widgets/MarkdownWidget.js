honeybee.widgets.MarkdownWidget = honeybee.widgets.Widget.extend({

    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "MarkdownWidget",

    epic_editor: null,

    textarea: null,

    loaded: null,

    init: function(element, options, ready_callback)
    {
        this.parent(element, options, ready_callback);
        this.loaded = false;
    },

    // #################################
    // #     widget implementation     #
    // #################################
    getTemplate: function()
    {
        return false;
    },

    initGui: function()
    {
        this.parent();

        this.textarea = this.element.find('> textarea');
        if (this.textarea.length === 0) {
            throw "Unable to find corresponding textrea for markdown editor.";
        }
        var that = this;
        this.element.prev('label').click(function()
        {
            that.epic_editor.focus();
        });

        // lazy initialize the epic editor instance on 'tab shown', if we are rendered within a hidden tab.
        var $parent_tab = this.element.parents('.tab-pane');
        $('a[data-toggle="tab"]').on('shown', function (e) {
            var $tab_pane = $($(e.target).attr('href'));
            if ($tab_pane.length > 0 && $tab_pane[0] === $parent_tab[0]) {
                that.loadEpicEditor();
            }
        });
        if ($parent_tab.length === 0 || $parent_tab.hasClass('active')) {
            this.loadEpicEditor();
        }
    },

    loadEpicEditor: function()
    {
        if (this.loaded) {
            return;
        }
        var that = this;
        var container = this.element.find('> .epic-editor');
        var base_href = $('#base_href').prop('href');
        var base_theme = this.options.themes.base;
        var preview_theme = this.options.themes.preview;
        var editor_theme = this.options.themes.editor;
        this.epic_editor = new EpicEditor({
            container: container[0],
            textarea: this.textarea[0],
            clientSideStorage: false,
            localStorageName: this.element.prop('id'),
            theme: {
                base: base_href + 'static/deploy/_global/binaries/epic_themes/base/' + base_theme + '.css',
                preview: base_href + 'static/deploy/_global/binaries/epic_themes/preview/' + preview_theme + '.css',
                editor: base_href + 'static/deploy/_global/binaries/epic_themes/editor/' + editor_theme + '.css'
            }
        });
        this.epic_editor.load(function()
        {
            that.textarea.hide();
            that.loaded = true;
        });
    },

    disable: function()
    {
        if (!this.disabled_overlay) {
            this.disabled_overlay = $('<div class="epic-overlay" />');
            $(this.epic_editor.getElement('container')).append(this.disabled_overlay);
        }
        this.disabled_overlay.show();
    },

    enable: function()
    {
        if (!this.disabled_overlay) {
            return;
        }
        this.disabled_overlay.hide();
    }
});

// #####################
// #     constants     #
// #####################
honeybee.widgets.MarkdownWidget.DEFAULT_OPTIONS = {
    autobind: true,
    fieldname: null,
    themes: {
        base: 'epiceditor',
        preview: 'github',
        editor: 'epic-light'
    }
};

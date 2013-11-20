honeybee.widgets.MarkdownWidget = honeybee.widgets.Widget.extend({

    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "MarkdownWidget",

    epic_editor: null,

    textarea: null,

    init: function(element, options, ready_callback)
    {
        this.parent(element, options, ready_callback);
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

        var that = this;
        var base_href = $('#base_href').prop('href');
        this.textarea = this.element.find('> textarea').first();
        this.element.prev('label').click(function()
        {
            that.epic_editor.focus();
        });
        if (this.textarea.length > 0) {
            var base_theme = this.options.themes.base;
            var preview_theme = this.options.themes.preview;
            var editor_theme = this.options.themes.editor;
            this.epic_editor = new EpicEditor({
                container: this.element.find('.epic-editor').first()[0],
                textarea: this.textarea[0],
                theme: {
                    base: base_href + 'static/deploy/_global/binaries/epic_themes/base/' + base_theme + '.css',
                    preview: base_href + 'static/deploy/_global/binaries/epic_themes/preview/' + preview_theme + '.css',
                    editor: base_href + 'static/deploy/_global/binaries/epic_themes/editor/' + editor_theme + '.css'
                }
            });
            this.epic_editor.load(function() { that.textarea.hide(); });
        }
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
        preview: 'preview-dark',
        editor: 'epic-dark'
    }
};
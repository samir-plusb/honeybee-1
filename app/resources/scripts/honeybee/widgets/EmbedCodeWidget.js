honeybee.widgets.EmbedCodeWidget = honeybee.widgets.Widget.extend({

    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "EmbedCodeWidget",

    // <knockout_props>
    fieldname: null,

    embed_url: null,

    preview_code: null,

    is_valid: null,
    // </knockout_props>

    init: function(element, options)
    {
        this.parent(element, options);
    },

    // #################################
    // #     widget implementation     #
    // #################################
    getTemplate: function()
    {
        return 'static/widgets/EmbedCodeWidget.html';
    },

    initKnockoutProperties: function()
    {
        var that = this;
        this.fieldname = ko.observable(this.options.fieldname);
        this.embed_url = ko.observable(this.options.field_value || '');
        this.preview_code = ko.observable('');
        this.is_valid = ko.computed(function()
        {
            var code = that.getEmbedForUrl(that.embed_url());
            if (code) {
                that.preview_code(code);
                return true;
            } else {
                that.preview_code('');
                return false;
            }
        });
    },

    getEmbedForUrl: function(url)
    {
        var youtube_long = /(http\:\/\/)?(?:www\.)?youtube\-nocookie\.com\/watch\?v=([\-\w\d]+)/;
        // var youtube_long = /(http\:\/\/)?(?:www\.)?youtube\.com\/watch\?v=([\-\w\d]+)/; # deactivated
        var youtube_short = /(http\:\/\/)?(?:www\.)?youtube\.de\/([\-\w\d]+)/;
        var vimeo = /(http\:\/\/)?vimeo\.com\/(\d+)/;
        var matches = url.match(youtube_long);
        matches = matches || url.match(youtube_short);
        if (matches) {
            return this.getYoutubeEmbed(matches[2]);
        } else if (matches = url.match(vimeo)) {
            return this.getVimeoEmbed(matches[2]);
        } else {
            return false;
        }
    },

    getYoutubeEmbed: function(tpl_payload)
    {
        return this.options.embed_templates.youtube.replace('{embed_payload}', tpl_payload);
    },

    getVimeoEmbed: function(tpl_payload)
    {
        return this.options.embed_templates.vimeo.replace('{embed_payload}', tpl_payload);
    }
});

// #####################
// #     constants     #
// #####################
honeybee.widgets.EmbedCodeWidget.DEFAULT_OPTIONS = {
    autobind: true,
    fieldname: null,
    embed_url: null,
    embed_templates: {
        'youtube': '<iframe class="span11" height="315" src="//www.youtube-nocookie.com/embed/{embed_payload}" frameborder="0" allowfullscreen></iframe>',
        'vimeo': '<iframe src="//player.vimeo.com/video/{embed_payload}" class="span11" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'
    }
};

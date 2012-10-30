midas.shofi.SelectVerticalDialog = midas.core.BaseObject.extend({

    log_prefix: 'SelectVerticalDialog',

    vertical_prompt: null,

    verticals_uri: null,

    currently_valid: null,

    init: function(el, verticals_uri)
    {
        this.parent();
        var that = this;
        this.verticals_uri = verticals_uri;
        this.vertical_prompt = el.twodal({
            show: false,
            backdrop: true,
            events: {
                verticalselect: this.onVerticalSelected.bind(this)
            }
        });
        this.currently_valid = {};
        this.vertical_prompt.find('input').typeahead({
            property: 'name',
            items: 50,
            source: function(typeahead, phrase)
            {
                if (0 >= phrase.length)
                {
                    typeahead.process([]);
                    that.currently_valid = {};
                    return;
                }
                var req = midas.core.Request.curry(that.verticals_uri.replace('{PHRASE}', phrase));
                req(function(resp)
                {
                    var data = resp.data;
                    that.currently_valid = {};
                    for (var i = 0; i < data.length; i++)
                    {
                        that.currently_valid[data[i].name] = data[i].identifier;
                    }
                    typeahead.process(data);
                });
            }
        });
    },

    show: function()
    {
        this.vertical_prompt.twodal('show');
        return this;
    },

    hide: function()
    {
        this.vertical_prompt.twodal('hide');
        return this;
    },

    onVerticalSelected: function()
    {
        var vertical = this.vertical_prompt.twodal('promptVal', 'input');
        if (this.validate(vertical))
        {
            this.fire('vertical::selected', [ { id: this.currently_valid[vertical], name: vertical } ]);
        }
    },

    validate: function(vertical)
    {
        return 'undefined' !== typeof this.currently_valid[vertical];
    },

    reset: function()
    {
        this.vertical_prompt.twodal('promptVal', 'input', '');
        return this;
    }
});
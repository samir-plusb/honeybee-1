midas.shofi.SelectCategoryDialog = midas.core.BaseObject.extend({

    log_prefix: 'SelectCategoryDialog',

    category_prompt: null,

    categories_uri: null,

    currently_valid: null,

    init: function(categories_uri)
    {
        this.parent();
        var that = this;
        this.categories_uri = categories_uri;
        this.category_prompt = $('#batchAssignNewCategoryModal').twodal({
            show: false,
            backdrop: true,
            events: {
                categoryselect: this.onCategorySelected.bind(this)
            }
        });
        this.currently_valid = {};
        this.category_prompt.find('input').typeahead({
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
                var req = midas.core.Request.curry(that.categories_uri.replace('{PHRASE}', phrase));
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
        this.category_prompt.twodal('show');
        return this;
    },

    hide: function()
    {
        this.category_prompt.twodal('hide');
        return this;
    },

    onCategorySelected: function()
    {
        var category = this.category_prompt.twodal('promptVal', 'input');
        if (this.validate(category))
        {
            this.fire('category::selected', [ { id: this.currently_valid[category], name: category } ]);
        }
    },

    validate: function(category)
    {
        return 'undefined' !== typeof this.currently_valid[category];
    },

    reset: function()
    {
        this.category_prompt.twodal('promptVal', 'input', '');
        return this;
    }
});
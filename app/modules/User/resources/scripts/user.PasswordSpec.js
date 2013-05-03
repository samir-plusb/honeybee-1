honeybee.user.PasswordSpec = honeybee.core.BaseObject.extend({

    log_prefix: "PasswordSpec",

    password: null,

    length: null,

    lowercase_chars_cnt: null,

    uppercase_chars_cnt: null,

    numeric_chars_cnt: null,

    special_chars_cnt: null,

    special_chars: [
        '!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '.', '/', ':', 
        ';', '<', '=', '>', '?', '@', '[', '\\', ']', '^', '_', '`', '{', '|', '}', '~'
    ],

    init: function(options)
    {
        this.parent(options);
    },

    setPassword: function(password)
    {
        this.password = password;
        this.length = this.password.length;

        this.lowercase_chars_cnt = this.countLowercaseChars(this.password);
        this.uppercase_chars_cnt = this.countUppercaseChars(this.password);
        this.numeric_chars_cnt = this.countNumericChars(this.password);
        this.special_chars_cnt = this.countSpecialChars(this.password);

        this.fire('password::changed');
    },

    getPassword: function()
    {
        return this.password;
    },

    getLowercaseCharsCount: function()
    {
        return this.lowercase_chars_cnt;
    },

    getUppercaseCharsCount: function()
    {
        return this.uppercase_chars_cnt;
    },

    getNumericCharsCount: function()
    {
        return this.numeric_chars_cnt;
    },

    getSpecialCharsCount: function()
    {
        return this.special_chars_cnt;
    },

    getLength: function()
    {
        return this.length;
    },

    countUppercaseChars: function(password)
    {
        var upper_letters_cnt = 0;

        var upper_letters = password.match(/[A-Z]+/g) || [];
        var i;

        for (i = 0; i < upper_letters.length; i++)
        {
            upper_letters_cnt += upper_letters[i].trim().length;
        }

        return upper_letters_cnt;
    },

    countLowercaseChars: function(password)
    {
        var lower_letters_cnt = 0;

        var lower_letters = password.match(/[a-z]+/g) || [];
        var i;

        for (i = 0; i < lower_letters.length; i++)
        {
            lower_letters_cnt += lower_letters[i].trim().length;
        }

        return lower_letters_cnt;
    },

    countNumericChars: function(password)
    {
        var numerics_cnt = 0;

        var numerics = password.match(/\d+/g) || [];
        var i;

        for (i = 0; i < numerics.length; i++)
        {
            numerics_cnt += numerics[i].trim().length;
        }

        return numerics_cnt;
    },

    countSpecialChars: function(password)
    {
        var characters_cnt = 0;
        var special_chars_pattern = this.special_chars.join("").replace(
            /[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, 
            "\\$&"
        );

        var chars_exp = new RegExp('[' + special_chars_pattern + ']+', 'g');
        var characters = password.match(chars_exp) || [];
        var i;

        for (i = 0; i < characters.length; i++)
        {
            characters_cnt += characters[i].trim().length;
        }

        return characters_cnt;
    }
});

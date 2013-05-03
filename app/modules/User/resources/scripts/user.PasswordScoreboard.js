honeybee.user.PasswordScoreboard = honeybee.core.BaseObject.extend({

    log_prefix: "PasswordScoreboard",

    password_spec: null,

    length_score: null,

    lowercase_chars_score: null,

    uppercase_chars_score: null,

    numeric_chars_score: null,

    special_chars_score: null,

    bonus_score: null,

    total_score: null,

    init: function(password_spec, options)
    {
        this.parent(options);

        this.password_spec = password_spec;
        this.password_spec.on('password::changed', this.recalculate.bind(this));
    },

    recalculate: function()
    {
        this.length_score = this.calculateLengthScore();
        this.lowercase_chars_score = this.calculateLowercaseCharsScore();
        this.uppercase_chars_score = this.calculateUppercaseCharsScore();
        this.numeric_chars_score = this.calculateNumericCharsScore();
        this.special_chars_score = this.calculateSpecialCharsScore();
        this.bonus_score = this.calculateBonusScore();

        this.total_score = this.length_score + 
            this.lowercase_chars_score + 
            this.uppercase_chars_score + 
            this.numeric_chars_score + 
            this.special_chars_score + 
            this.bonus_score;
    },

    getLengthScore: function()
    {   
        return this.length_score;
    },

    getLowerCharsScore: function()
    {
        return this.lowercase_chars_score;
    },

    getUpperCharsScore: function()
    {
        return this.uppercase_chars_score;
    },

    getNumericCharsScore: function()
    {
        return this.numeric_chars_score;
    },

    getSpecialCharsScore: function()
    {
        return this.special_chars_score;
    },

    getBonusScore: function()
    {
        return this.bonus_score;
    },

    getTotalScore: function()
    {
        return this.total_score;
    },

    calculateLengthScore: function()
    {
        var points,
            length = this.password_spec.getLength();

        if (length >= 10)
        {
            points = 25;
        }
        else if (length < 10 && length >= 5)
        {
            points = 10;
        }
        else if (length < 5 && length > 0)
        {
            points = 5;
        }
        else
        {
            points = 0;
        }

        return points;
    },

    calculateLowercaseCharsScore: function()
    {
        var points,
            lower_cnt = this.password_spec.getLowercaseCharsCount();

        if (lower_cnt >= 3)
        {
            points = 10;
        }
        else if (lower_cnt > 0)
        {
            points = 5;
        }
        else
        {
            points = 0;
        }

        return points;
    },

    calculateUppercaseCharsScore: function()
    {
        var points,
            upper_cnt = this.password_spec.getUppercaseCharsCount();

        if (upper_cnt >= 3)
        {
            points = 10;
        }
        else if (upper_cnt > 0)
        {
            points = 5;
        }
        else
        {
            points = 0;
        }

        return points;
    },

    calculateNumericCharsScore: function()
    {
        var points,
            numerics_cnt = this.password_spec.getNumericCharsCount();

        if (numerics_cnt >= 3)
        {
            points = 20;
        }
        else if (numerics_cnt > 0) 
        {
            points = 10;
        }
        else
        {
            points = 0;
        }
        
        return points;
    },

    calculateSpecialCharsScore: function()
    {
        var points,
            characters_cnt = this.password_spec.getSpecialCharsCount();

        if (characters_cnt >= 3)
        {
            points = 25;
        }
        else if (characters_cnt > 0)
        {
            points = 10;
        }
        else
        {
            points = 0;
        }

        return points;
    },

    calculateBonusScore: function()
    {
        var bonus = 0;

        var letters_score = this.calculateLowercaseCharsScore() + this.calculateUppercaseCharsScore();
        var numerics_score = this.calculateNumericCharsScore();
        var special_chars_score = this.calculateSpecialCharsScore();
        
        if (letters_score >= 10 && numerics_score >= 10)
        {
            bonus += 2;
        }

        if (letters_score >= 10 && numerics_score >= 10 && special_chars_score >= 10)
        {
            bonus += 3;
        }

        if (letters_score === 20 && numerics_score >= 10 && special_chars_score >= 10)
        {
            bonus += 5;
        }

        return bonus;
    }
});

honeybee.user.PasswordRequirementsController = honeybee.core.BaseObject.extend({

    log_prefix: "PasswordRequirementsController",

    element: null,

    password_input: null,

    strength_scorebar: null,

    special_chars: [
        '!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '.', '/', ':', 
        ';', '<', '=', '>', '?', '@', '[', '\\', ']', '^', '_', '`', '{', '|', '}', '~'
    ],

    init: function(element, options)
    {
        this.parent(options);

        this.element = $(element);
        this.password_input = this.element.find('.input-password').first();

        var that = this;
        this.password_input.keyup(function()
        {
            that.onPasswordChanged($(this).val());
        }).focus(function()
        {
            that.password_input.popover('show');
            that.onPasswordChanged($(this).val());
        }).blur(function()
        {
            that.password_input.popover('hide');
        });

        this.initPopover();
        this.element.find('.btn-set-pwd').first().prop('disabled', true);
    },

    onPasswordChanged: function(password)
    {
        var scores = this.calculatePasswordStrength(password);
        var counts = this.countCriteriaOccurences(password);
        var class_name, count;

        for (class_name in counts)
        {
            count = counts[class_name];
            if (0 < count)
            {
                this.element.find('.'+class_name).first().addClass('accomplished');
            }
            else
            {
                this.element.find('.'+class_name).first().removeClass('accomplished');
            }
        }
        if (8 <= password.length)
        {
            this.element.find('.length-chars').first().addClass('accomplished');

            if (0 < counts['lowercase-chars'] && 0 < counts['numeric-chars'])
            {
                this.element.find('.btn-set-pwd').first().prop('disabled', false);
            }
            else
            {
                this.element.find('.btn-set-pwd').first().prop('disabled', true);
            }
        }
        else
        {
            this.element.find('.length-chars').first().removeClass('accomplished');
            this.element.find('.btn-set-pwd').first().prop('disabled', true);
        }

        var scorebar = this.element.find('.strength-scorebar');
        var states = ['progress-danger', 'progress-warning', 'progress-success'];
        var states_string = states.join(" ");

        scorebar.find('.bar').first().css('width', scores.total_score + '%');

        if (0 <= scores.total_score && 33 >= scores.total_score)
        {
            scorebar.removeClass(states_string);
            scorebar.addClass('progress-danger');
        }
        else if (33 < scores.total_score && 68 >= scores.total_score)
        {
            scorebar.removeClass(states_string);
            scorebar.addClass('progress-warning');
        }
        else if (68 < scores.total_score && 100 >= scores.total_score)
        {
            scorebar.removeClass(states_string);
            scorebar.addClass('progress-success');
        }
    },

    initPopover: function()
    {
        this.password_input.popover({
            trigger: 'manual',
            html: true,
            content: $(this.options.popover_tpl_selector).html(),
            title: this.options.popover_title,
            placement: this.options.popover_pos
        });
    },

    countCriteriaOccurences: function(password)
    {
        return {
            'lowercase-chars': this.getLowercaseLettersCount(password),
            'uppercase-chars': this.getUppercaseLettersCount(password),
            'special-chars': this.getCharacterCount(password),
            'numeric-chars': this.getNumericsCount(password)
        };
    },

    calculatePasswordStrength: function(password)
    {
        var len_score = this.getLengthScore(password);
        var letter_score = this.getLettersScore(password);
        var num_score = this.getNumericScore(password);
        var char_score = this.getCharacterScore(password);

        var scores = { 
            'length': len_score,
            'letters': letter_score,
            'numerics': num_score,
            'characters': char_score,
            'total_score': 0
        };

        var total_score = len_score + letter_score + num_score + char_score;

        if (20 <= letter_score && 10 <= num_score && 10 <= char_score)
        {
            total_score += 5;
        }
        
        if (10 <= letter_score && 10 <= num_score && 10 <= char_score)
        {
            total_score += 3;
        }

        if (10 <= letter_score && 10 <= num_score)
        {
            total_score += 2;
        }

        scores.total_score = total_score;

        return scores;
    },

    getLengthScore: function(password)
    {
        var points = 0;

        if (0 < password.length && 4 >= password.length)
        {
            points = 5;
        }
        else if (5 <= password.length && 9 >= password.length)
        {
            points = 10;
        }
        else if (9 < password.length && 14 >= password.length)
        {
            points = 25;
        }
        else if (14 < password.length && 20 >= password.length)
        {
            points = 45;
        }
        else if (20 < password.length)
        {
            points = 80;
        }

        return points;
    },

    getLettersScore: function(password)
    {
        var points = 0;

        var lower_cnt = this.getLowercaseLettersCount(password);
        var upper_cnt = this.getUppercaseLettersCount(password);

        if (0 < lower_cnt && 0 < upper_cnt)
        {
            points = 20;
        }
        else if(0 < lower_cnt || 0 < upper_cnt)
        {
            points = 10;
        }

        return points;
    },

    getNumericScore: function(password)
    {
        var points = 0;

        var numerics_cnt = this.getNumericsCount(password);

        if (0 < numerics_cnt && 3 > numerics_cnt)
        {
            points = 10;
        }
        else if(3 <= numerics_cnt)
        {
            points = 20;
        }

        return points;
    },

    getCharacterScore: function(password)
    {
        var points = 0;

        var characters_cnt = this.getCharacterCount(password);

        if (0 < characters_cnt && 2 > characters_cnt)
        {
            points = 10;
        }
        else if(2 <= characters_cnt)
        {
            points = 25;
        }

        return points;
    },

    getUppercaseLettersCount: function(password)
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

    getLowercaseLettersCount: function(password)
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

    getNumericsCount: function(password)
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

    getCharacterCount: function(password)
    {
        var characters_cnt = 0;

        var special_chars_pattern = this.special_chars.join("").replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");

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

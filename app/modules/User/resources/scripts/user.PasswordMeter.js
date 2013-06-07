honeybee.user.PasswordMeter = honeybee.core.BaseObject.extend({

    log_prefix: "PasswordMeter",

    element: null,

    password_input: null,

    password_repeat_input: null,

    submit_btn: null,

    strength_scorebar: null,

    password_spec: null,

    scoreboard: null,

    init: function(element, options)
    {
        this.parent(options);
        this.element = $(element);

        this.password_input = this.element.find('.input-password').first();
        this.password_repeat_input = this.element.find('.input-password-repeat').first();
        this.submit_btn = this.element.find('.btn-set-pwd').first();

        this.password_spec = new honeybee.user.PasswordSpec();
        this.scoreboard = new honeybee.user.PasswordScoreboard(this.password_spec);
        this.password_spec.on('password::changed', this.onPasswordUpdated.bind(this));

        this.initRequirmentsPopover();
        this.initInputEvents();

        this.submit_btn.prop('disabled', true);
    },

    initRequirmentsPopover: function()
    {
        this.password_input.popover({
            trigger: 'manual',
            html: true,
            content: $(this.options.popover.tpl_selector).html(),
            title: this.options.popover.title,
            placement: this.options.popover.pos
        });
    },

    initInputEvents: function()
    {
        var that = this;

        this.password_input.keyup(function()
        {
            var password = $(this).val();
            that.password_spec.setPassword(password);
        }).focus(function()
        {
            var password = $(this).val();
            that.password_input.popover('show');
            that.password_spec.setPassword(password);
        }).blur(function()
        {
            that.password_input.popover('hide');
        });

        this.password_repeat_input.keyup(function()
        {
            that.updateInputStates();
        });
    },

    onPasswordUpdated: function()
    {
        this.updateRequirementLists();
        this.updateProgressbar();
        this.updateInputStates();
    },

    updateRequirementLists: function()
    {
        var req = this.options.requirements;
        var lowercase_chars_list_item = $('.password_requirements.mandatory .min_lowercase_chars');
        var optional_list_item = $('.password_requirements-optional .min_lowercase_chars');
        if (this.password_spec.getLowercaseCharsCount() >= req.min_lowercase_chars && req.min_lowercase_chars > 0)
        {
            lowercase_chars_list_item.addClass('accomplished');
        }
        else if (req.min_lowercase_chars == 0)
        {
            lowercase_chars_list_item.css('display', 'none');
            optional_list_item.css('display', 'block');
            if (this.password_spec.getLowercaseCharsCount() > 0)
            {
                optional_list_item.addClass('accomplished');
            }
            else
            {
                optional_list_item.removeClass('accomplished');
            }
        }
        else
        {
            lowercase_chars_list_item.removeClass('accomplished');
        }

        var uppercase_chars_list_item = $('.password_requirements.mandatory .min_uppercase_chars');
        optional_list_item = $('.password_requirements-optional .min_uppercase_chars');
        if (this.password_spec.getUppercaseCharsCount() >= req.min_uppercase_chars && req.min_uppercase_chars > 0)
        {
            uppercase_chars_list_item.addClass('accomplished');
        }
        else if (req.min_uppercase_chars == 0)
        {
            uppercase_chars_list_item.css('display', 'none');
            optional_list_item.css('display', 'block');
            if (this.password_spec.getUppercaseCharsCount() > 0)
            {
                optional_list_item.addClass('accomplished');
            }
            else
            {
                optional_list_item.removeClass('accomplished');
            }
        }
        else
        {
            uppercase_chars_list_item.removeClass('accomplished');
        }

        var numeric_chars_list_item = $('.password_requirements.mandatory .min_decimal_numbers');
        optional_list_item = $('.password_requirements-optional .min_decimal_numbers');
        if (this.password_spec.getNumericCharsCount() >= req.min_decimal_numbers && req.min_decimal_numbers > 0)
        {
            numeric_chars_list_item.addClass('accomplished');
        }
        else if (req.min_decimal_numbers == 0)
        {
            numeric_chars_list_item.css('display', 'none');
            optional_list_item.css('display', 'block');
            if (this.password_spec.getNumericCharsCount() > 0)
            {
                optional_list_item.addClass('accomplished');
            }
            else
            {
                optional_list_item.removeClass('accomplished');
            }
        }
        else
        {
            numeric_chars_list_item.removeClass('accomplished');
        }

        var password_special_chars_list_item = $('li.special-chars');
        if (this.password_spec.getSpecialCharsCount() > 0)
        {
            password_special_chars_list_item.addClass('accomplished');
        }
        else
        {
            password_special_chars_list_item.removeClass('accomplished');
        }

        var length_list_item = $('.password_requirements.mandatory .min_string_length');
        if (this.password_spec.getLength() >= req.min_string_length && req.min_string_length > 0)
        {
            length_list_item.addClass('accomplished');
        }
        else
        {
            length_list_item.removeClass('accomplished');
        }   
    },

    updateProgressbar: function()
    {
        var class_name, count;
        var total_score = this.scoreboard.getTotalScore();
        var states = ['progress-danger', 'progress-warning', 'progress-success'];
        var states_string = states.join(" ");

        var scorebar = this.element.find('.strength-scorebar');
        scorebar.find('.bar').first().css('width', total_score + '%');

        if (total_score >= 68)
        {
            scorebar.removeClass(states_string);
            if (this.isPasswordValid())
            {
                scorebar.addClass('progress-success');
            }
            else
            {
                scorebar.addClass('progress-warning');
            }
        }
        else if (total_score < 68 && total_score > 33)
        {
            scorebar.removeClass(states_string);
            scorebar.addClass('progress-warning');
        }
        else
        {
            scorebar.removeClass(states_string);
            scorebar.addClass('progress-danger');
        }
    },

    updateInputStates: function()
    {
        if (this.isPasswordValid())
        {
            this.password_input.parents('.control-group').addClass('accepted');
        }
        else
        {
            this.password_input.parents('.control-group').removeClass('accepted');
        }

        if (this.isPasswordRepetitionValid())
        {
            this.password_repeat_input.parents('.control-group').addClass('accepted');
        }
        else
        {
            this.password_repeat_input.parents('.control-group').removeClass('accepted');
        }

        if (this.isPasswordValid() && this.isPasswordRepetitionValid())
        {
            this.submit_btn.prop('disabled', false);
        }
        else
        {
            this.submit_btn.prop('disabled', true);
        }
    },

    isPasswordValid: function()
    {
        return (
            this.password_spec.getLowercaseCharsCount() >= 2 && 
            this.password_spec.getUppercaseCharsCount() >= 2 && 
            this.password_spec.getLength() >= 8
        );
    },

    isPasswordRepetitionValid: function()
    {
        return (
            this.password_repeat_input.val().length > 0 && 
            this.password_repeat_input.val() === this.password_spec.getPassword()
        );
    }
});

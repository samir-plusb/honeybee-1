honeybee.user.PasswordMeter = honeybee.core.BaseObject.extend({

    log_prefix: "PasswordMeter",

    element: null,

    password_input: null,

    password_repeat_input: null,

    confirm_btn: null,

    strength_scorebar: null,

    password_spec: null,

    scoreboard: null,

    init: function(element, options)
    {
        this.parent(options);
        this.element = $(element);

        this.password_input = this.element.find('.input-password').first();
        this.password_repeat_input = this.element.find('.input-password-repeat').first();
        this.confirm_btn = this.element.find('.btn-set-pwd').first();

        this.password_spec = new honeybee.user.PasswordSpec();
        this.scoreboard = new honeybee.user.PasswordScoreboard(this.password_spec);
        this.password_spec.on('password::changed', this.onPasswordUpdated.bind(this));

        this.initRequirmentsPopover();
        this.initInputEvents();

        this.confirm_btn.prop('disabled', true);
    },

    initRequirmentsPopover: function()
    {
        this.password_input.popover({
            trigger: 'manual',
            html: true,
            content: $(this.options.popover_tpl_selector).html(),
            title: this.options.popover_title,
            placement: this.options.popover_pos
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
        var lowercase_chars_list_item = $('li.lowercase-chars');
        if (this.password_spec.getLowercaseCharsCount() > 0)
        {
            lowercase_chars_list_item.addClass('accomplished');
        }
        else
        {
            lowercase_chars_list_item.removeClass('accomplished');
        }

        var uppercase_chars_list_item = $('li.uppercase-chars');
        if (this.password_spec.getUppercaseCharsCount() > 0)
        {
            uppercase_chars_list_item.addClass('accomplished');
        }
        else
        {
            uppercase_chars_list_item.removeClass('accomplished');
        }

        var numeric_chars_list_item = $('li.numeric-chars');
        if (this.password_spec.getNumericCharsCount() > 0)
        {
            numeric_chars_list_item.addClass('accomplished');
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

        var length_list_item = $('li.length-chars');
        if (this.password_spec.getLength() >= 8)
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
            this.confirm_btn.prop('disabled', false);
        }
        else
        {
            this.confirm_btn.prop('disabled', true);
        }
    },

    isPasswordValid: function()
    {
        return (
            this.password_spec.getLowercaseCharsCount() > 0 && 
            this.password_spec.getNumericCharsCount() > 0 && 
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

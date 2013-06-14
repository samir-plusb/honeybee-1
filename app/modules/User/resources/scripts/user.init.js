(function(namespace)
{
    var initStickyControls = function()
    {
        var form_actions = $('.controller-edit .form-actions');
        var footer = $('.footer');
        var actions_height = form_actions.height();
        var documentHeight = $(document).height();

        var stick = function()
        {
            var max_bottom = footer.offset().top;
            var pos = form_actions.offset();
            var bottom = pos.top + actions_height;
            var viewport_edge = $(document).scrollTop() + $(window).height();
            var diff = viewport_edge - max_bottom;

            if (diff >= 0)
            {
                form_actions.removeClass('overlayed');
                form_actions.css('bottom', diff - footer.height() + 3);
            }
            else
            {
                form_actions.addClass('overlayed');
                form_actions.css('bottom', '-20px');
            }
        };

        $(document).scroll(function()
        {
            stick();
        });

        $('a[data-toggle="tab"]').on('shown', function(e)
        {
            stick();
        });

        stick();
        setInterval(stick, 1000);
    };

    var edit_wrapper = $('.controller-edit');
    var list_wrapper = $('.container-list-data');
    var tree_wrapper = $('.container-tree-data');
    var password_form = $('.form-password').first();

    if (1 === edit_wrapper.length)
    {
        honeybee.core.EditController.factory('.controller-edit');
        initStickyControls();
    }
    else if (1 === list_wrapper.length)
    {
        honeybee.list.ListController.create('.container-list-data', namespace).attach();
    }
    else if (1 === tree_wrapper.length)
    {
        honeybee.tree.TreeController.create('.container-tree-data', namespace).attach();
    }

    if(1 === password_form.length)
    {
        var options = JSON.parse($('#password_meter_options').val());
        new honeybee.user.PasswordMeter(password_form, options);
    }
})(honeybee.user);

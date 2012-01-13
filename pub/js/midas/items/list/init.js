(function() {
    var search_input = $('input[name="search_phrase"]');
    $('.reset-search').click(function(event)
    {
        event.preventDefault();
        search_input.val('');
        search_input.focus();
        $(this).hide();
    });
    search_input.focus(function()
    {
        $(this).select();
    });

    var error_dialog = $('#ajax-error');
    error_dialog.find('.modal-footer button').click(function()
    {
        error_dialog.modal('hide');
    });

    $("table").delegate(".title a", "click", function(event)
    {
        event.preventDefault();
        var grab_url = $(this).attr('data-checkout-url');
        var process_url = $(this).attr('href');

        $.getJSON(grab_url, function(resp)
        {
            if ('ok' === resp.state)
            {
                window.location.href = process_url;
            }
            else
            {
                error_dialog.find('.error-title').text(
                    "Taking ticket ownership failed!"
                );
                error_dialog.find('.error-text').text(resp.msg);
                error_dialog.modal('show');
            }
        });
    });

    $("table").delegate(".owner a", "click", function(event)
    {
        event.preventDefault();
        var link = $(this);
        var release_url = $(this).attr('href');
        $.getJSON(release_url, function(resp)
        {
            if ('ok' !== resp.state)
            {
                error_dialog.find('.error-title').text(
                    "Releasing ticket ownership failed!"
                );
                error_dialog.find('.error-text').text(resp.msg);
                error_dialog.modal('show');
            }
            else
            {
                link.fadeOut(250, function()
                {
                    link.replaceWith(
                        $('<span class="label">nobody</span>')
                    );
                });
            }
        });
    });
})();
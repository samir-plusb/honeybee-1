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

    $("table").delegate(".title a", "click", function(event)
    {
        event.preventDefault();

        $.getJSON($(this).attr('href'), function()
        {
            // @todo check response state and redirect to edit if we gained ownership.
            console.log(arguments);
        });
    });

})();
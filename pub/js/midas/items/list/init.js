$(function() {
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
});
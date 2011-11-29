$(function() {
    $('.reset-search').click(function(event)
    {
        event.preventDefault();
        $('input[name="search_phrase"]').val('');
        $(this).hide();
    });
});
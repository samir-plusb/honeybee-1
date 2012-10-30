
$('.module-list li').each(function(idx, item)
{
    $(item).mousemove(function(event)
    {
        var list_item = $(this);
        var pos = list_item.position();
        var hor_hit = event.pageX >= pos.left && event.pageX <= pos.left + list_item.outerWidth();
        var vert_hit = event.pageY >= pos.top && event.pageY <= pos.top + list_item.outerHeight();
        if (event.target === this && ! $(this).hasClass('hover'))
        {
            $(this).addClass('hover');
        }
        else if(hor_hit && vert_hit)
        {
            $(this).addClass('hover');
        }
    });

   $(item).mouseleave(function()
   {
       $(this).removeClass('hover');
   });
});

$('.module-list .nav li').each(function(idx, item)
{
   $(item).hover(function(event)
   {
           $(this).addClass('active');
   }, function()
   {
       $(this).removeClass('active');
   });
});
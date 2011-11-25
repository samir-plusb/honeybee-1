var controller = new midas.items.edit.EditController();
var view = new midas.items.edit.EditView(
    $(document.body)[0],
    { 'tabs_container': '.item-content' }
);
view.attach(controller);

(function()
{
    var items_panel = $('.content-items');
    var timer;
    
    $(document).scroll(function()
    {
        clearTimeout(timer);
        timer = setTimeout(function()
        {
            items_panel.animate({
                'top': $(document).scrollTop()
            }, 300, 'easeOutSine');
            /*
            items_panel.animate({ 
                'top': $(document).scrollTop(),
                'opacity': 0,
            }, 300, 'easeOutSine', function()
            {
                items_panel.animate({ 
                    'opacity': 1,
                }, 300, 'easeOutSine');
            });*/
        }, 100);
    });
})();
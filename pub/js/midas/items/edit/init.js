var controller = new midas.items.edit.EditController();
var view = new midas.items.edit.EditView(
    $(document.body)[0],
    { 'tabs_container': '.item-content' }
);
view.attach(controller);

 $(document).scroll(function()
{
    if (16 < $(this).scrollTop())
    {
        $('.topmenu-container').first().addClass('decent');
    }
    else
    {
        $('.topmenu-container').first().removeClass('decent');
    }
});
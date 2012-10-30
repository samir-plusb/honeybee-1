(function(namespace)
{
    var edit_wrapper = $('.controller-edit');
    var list_wrapper = $('.container-list-data');
    
    if (1 === edit_wrapper.length)
    {
        midas.core.EditController.factory('.controller-edit');
    }
    else if (1 === list_wrapper.length)
    {
        midas.list.ListController.create('.container-list-data', namespace).attach();
    }
})(midas.events);

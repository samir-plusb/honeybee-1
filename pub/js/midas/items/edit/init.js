var controller = new midas.items.edit.EditController();
var view = new midas.items.edit.EditView(
    $(document.body)[0],
    { 'tabs_container': '.item-content' }
);
view.attach(controller);
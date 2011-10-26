$(document).ready(function() {

    var controller = new midas.items.edit.EditController();

    var view = new midas.items.edit.EditView(
        this,
        $(document.body)[0],
        { 'tabs_container': '.item-content' }
    );

    view.attachController(controller);
});
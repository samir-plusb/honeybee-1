honeybee.tree.TreeController = honeybee.core.BaseObject.extend({

    log_prefix: "TreeController",

    viewmodel: null,

    options: null,

    init: function(options)
    {
        this.parent();
        this.options = options;
    },

    attach: function()
    {
        this.viewmodel = new honeybee.tree.TreeViewModel(
            this, 
            {}
        );
        ko.applyBindings(this.viewmodel);
    },

    loadData: function(data)
    {
        this.viewmodel.list_items.removeAll();
        this.viewmodel.initItems(data);
    }

});

honeybee.tree.TreeController.create = function(element, namespace)
{
    element = $(element);

    if (0 === element.length)
    {
        throw "Unable to find element to create controller from. Looked for: " + element;
    }
    var controller_class = element.attr('data-controller');
    if (! controller_class || ! namespace[controller_class])
    {
        throw "Unable to resolve controller implementor: " + controller_class;
    }
    var options = element.attr('data-controller-options') || "{}";
    var controller = new namespace[controller_class](JSON.parse(options));
    return controller;
};


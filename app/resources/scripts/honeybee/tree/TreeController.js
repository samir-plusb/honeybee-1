honeybee.tree.TreeController = honeybee.core.BaseObject.extend({

    log_prefix: "TreeController",

    options: null,

    tree: {},

    init: function(options)
    {
        this.parent();
        this.options = options;
        this.domElement = options.domElement;
        this.renderTarget = this.domElement.find('.render-tree');

        this.loadData();

        this.renderTreeNode(this.tree.rootNode, this.renderTarget)
        this.bindDragEvents();
    },

    attach: function()
    {
    },

    bindDragEvents: function()
    {
        var that = this;

        this.renderTarget.find('li').bind('dragstart', function(ev)
        {
            ev.stopPropagation();
            ev.originalEvent.dataTransfer.setData('Text', $(this).attr('id'));
            console.log('drag start');
        }).bind('dragover', function(ev)
        {
            ev.preventDefault();
        })
        .bind('drop', function(ev)
        {
            ev.preventDefault();
            that.moveNode($('#'+ev.originalEvent.dataTransfer.getData('Text')), $(this));
            ev.stopPropagation();

            console.log('dropped', ev.originalEvent.dataTransfer.getData('Text'), 'on', $(this).attr('id'));
        });
    },

    renderTreeNode: function(node, domContext)
    {
        var childList, childContent, i;

        domContext.append($('<span></span>').addClass('node-label').text(node.label));

        if (node.hasOwnProperty('children') && node.children.length > 0)
        {
            childList = $('<ul></ul>').addClass('children');
            domContext.append(childList);

            for (i in node.children)
            {
                if (node.children.hasOwnProperty(i))
                {
                    childElement = $('<li></li>').addClass('child').attr('id', node.children[i].identifier).attr('draggable', true);
                    this.renderTreeNode(node.children[i], childElement);

                    childList.append(childElement);
                }
            }
        }
    },

    loadData: function()
    {
        var dataContainer = this.domElement.find('.tree-data-json');
        this.tree = JSON.parse(dataContainer.text().trim());
    },

    moveNode: function(from, to)
    {
        var children = to.children('.children');
        if (children.length == 0)
        {
            console.log('create children ul');
            children = $('<ul></ul>').addClass('children');
            to.append(children);
        }

        console.log('moving', from, to);
        children.append(from);
    },

    saveData: function()
    {
        this.tree.name = 'tree';
        $.ajax({
            url: this.options.saveCompleteTreeUrl,
            type: 'POST',
            data: {
                structure: JSON.stringify(this.tree)
            },
            headers: {
                Accept: "application/json"
            }
        }).done(function(response)
        {
            console.log(response);

        }).fail(function(response)
        {
            console.log(response.responseText);
        });
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
    options = options === null ? {} : JSON.parse(options);
    options.domElement = element;
    var controller = new namespace[controller_class](options);

    return controller;
};


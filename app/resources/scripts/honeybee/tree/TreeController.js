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

        this.renderTreeNode(this.tree.rootNode, this.renderTarget);
        this.refreshCss(this.renderTarget);
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
        }).bind('dragover', function(ev)
        {
            ev.preventDefault();
        })
        .bind('drop', function(ev)
        {
            ev.preventDefault();
            that.moveNode($('#'+ev.originalEvent.dataTransfer.getData('Text')), $(this));
            ev.stopPropagation();
        });
    },

    renderTreeNode: function(node, domContext)
    {
        var childList, childContent, i;

        domContext.attr('id', node.identifier);
        domContext.append($('<span></span>').addClass('node-label').text(node.label));

        console.log('rendering', node.identifier);

        if (node.hasOwnProperty('children') && node.children.length > 0)
        {
            childList = $('<ul></ul>').addClass('children');
            domContext.append(childList);

            for (i in node.children)
            {
                if (node.children.hasOwnProperty(i))
                {
                    var childElement = $('<li></li>').addClass('child').attr('draggable', true);
                    this.renderTreeNode(node.children[i], childElement);

                    childList.append(childElement);
                }
            }
        }
    },

    refreshCss: function()
    {
        var even = false;
        var depth = 0;
        //encapsule in a function to avoid bloating mamory usage with "var that = this;" on every recursion
        var traverseAndRefresh = function(domContext)
        {
            even = !even;
            domContext.removeClass('odd even').addClass(even ? 'even' : 'odd');
            domContext.children('.node-label').css('padding-left', depth*30);
            depth = depth + 1;
            domContext.children('ul').children('li').each(function(i, element)
            {
                traverseAndRefresh($(element));
            });
            depth = depth - 1;
        };

        traverseAndRefresh(this.renderTarget);
    },

    loadData: function()
    {
        var dataContainer = this.domElement.find('.tree-data-json');

        this.tree = JSON.parse(dataContainer.text().trim());

        console.log('loaded', JSON.stringify(this.tree));
    },

    moveNode: function(from, to)
    {
        if ($.contains(from, to))
        {
            //we can't move a node into one of its children. that would probably destroy the fabric of space-time
            return;
        }
        var children = to.children('.children');
        if (children.length === 0)
        {
            children = $('<ul></ul>').addClass('children');
            to.append(children);
        }

        children.append(from);

        this.refreshCss();

        this.rebuildJson();
    },

    rebuildJson: function()
    {
        var buildJsonNode = function(domContext)
        {
            var parent = domContext.parent();
            var children = [];
            domContext.children('.children').children('.child').each(function(i, element)
            {
                children.push(buildJsonNode($(element)));
            });
            var node = {
                identifier: domContext.attr('id'),
                label: domContext.children('.node-label').text(),
                parent: parent.hasClass('children') ? parent.parent().attr('id') : null,
                children: children
            };

            return node;
        };

        this.tree = {
            rootNode: buildJsonNode(this.renderTarget)
        };

        this.saveData();
    },

    saveData: function()
    {
        this.tree.name = 'tree';
        console.log('saving', JSON.stringify(this.tree));
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
            //console.log(response.responseText);
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


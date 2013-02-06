honeybee.tree.TreeController = honeybee.core.BaseObject.extend({

    log_prefix: "TreeController",

    options: null,

    dropMode: null,

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
        var lastNode;

        var clearDragCss = function(element)
        {
            var classes = 'drop-before drop-inside drop-after';
            $(element).removeClass(classes).parentsUntil('#'+that.tree.rootNode.identifier).removeClass(classes);
        };

        this.renderTarget.find('li').bind('dragstart', function(ev)
        {
            ev.stopPropagation();
            ev.originalEvent.dataTransfer.setData('Text', $(this).attr('id'));
        }).bind('dragenter', function(ev)
        {
            ev.stopPropagation();
            lastNode = this;
        }).bind('dragover', function(ev)
        {
            ev.preventDefault();
            ev.stopPropagation();
            var dragY = ev.originalEvent.pageY;

            var elementY = $(this).offset().top;
            var height = $(this).height();

            var borderAreaHeight = 0.3;

            if (dragY < elementY + height*borderAreaHeight) //dragged over the top 20% of the target
            {
                that.dropMode = 'before';
            }
            else if (dragY > elementY + height*(1-borderAreaHeight))
            {
                that.dropMode = 'after';
            }
            else
            {
                that.dropMode = 'inside';
            }

            clearDragCss(this);
            $(this).addClass('drop-'+that.dropMode);

        }).bind('dragleave', function(ev)
        {
            clearDragCss(lastNode);
            clearDragCss(this);
        }).bind('drop', function(ev)
        {
            ev.preventDefault();
            ev.stopPropagation();

            clearDragCss(lastNode);
            clearDragCss(this);
            that.moveNode($('#'+ev.originalEvent.dataTransfer.getData('Text')), $(this));
        });
    },

    renderTreeNode: function(node, domContext)
    {
        var childList, childContent, i;

        domContext.attr('id', node.identifier);
        domContext.append($('<span></span>').addClass('node-label').text(node.label));

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
        //encapsule in a function to avoid bloating mamory usage with "var that = this;" on every recursion
        var traverseAndRefresh = function(domContext)
        {
            even = !even;
            domContext.removeClass('odd even').addClass(even ? 'even' : 'odd');
            domContext.children('ul').children('li').each(function(i, element)
            {
                traverseAndRefresh($(element));
            });
        };

        traverseAndRefresh(this.renderTarget);
    },

    loadData: function()
    {
        var dataContainer = this.domElement.find('.tree-data-json');

        this.tree = JSON.parse(dataContainer.text().trim());
    },

    moveNode: function(from, to)
    {
        var children;

        if ($.contains(from.get(0), to.get(0)))
        {
            //we can't move a node into one of its children. that would probably destroy the fabric of space-time, which wouldn't be so nice.
            this.logDebug("can't place the node inside itself");
            return;
        }

        if(from.is(to))
        {
            //placing an element next to itself doesn't make any sense. Seriously, why would anyone do that?
            this.logDebug("can't place an element next to itself.");
            return;
        }

        if (this.dropMode === 'before')
        {
            from.insertBefore(to);
        }
        else if (this.dropMode === 'after')
        {
            from.insertAfter(to);
        }
        else
        {
            children = to.children('.children');
            if (children.length === 0)
            {
                children = $('<ul></ul>').addClass('children');
                to.append(children);
            }

            children.append(from);
        }

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


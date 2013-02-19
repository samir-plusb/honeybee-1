honeybee.sidebar.SidebarTreeController = honeybee.core.BaseObject.extend({

    log_prefix: "SidebarTreeController",

    tree: {},

    moduleName: null,

    init: function(options)
    {
        this.domElement = options.domElement;
        this.renderTarget = this.domElement.find('.render-tree');

        this.loadData();

        this.moduleName = this.domElement.attr('data-module');
        console.log(this.moduleName);

        this.renderTreeNode(this.tree.rootNode, this.renderTarget);
        this.refreshCss(this.renderTarget);
        this.bindDropEvents();
        this.bindToggleEvents();
        this.bindClickEvents();
    },

    bindClickEvents: function()
    {
        var that = this;
        this.renderTarget.find('.node-label').bind('click', function(ev)
        {
            honeybee.core.events.fireEvent('filterBy', {
                field: that.moduleName,
                value: $(this).parent('.child').attr('id')
            });

            that.renderTarget.find('.node-label').removeClass('highlighted');
            $(this).addClass('highlighted');
        });
    },

    bindDropEvents: function()
    {
        var that = this;
        this.renderTarget.find('li').bind('drop', function(ev)
        {
            ev.stopPropagation();
            ev.preventDefault();
            that.renderTarget.find('.child').removeClass('drop-inside');

            var data = ev.originalEvent.dataTransfer.getData('text/plain');

            honeybee.core.events.fireEvent('itemDroppedOnItem', {
                sourceId: data,
                targetId: $(this).attr('id')
            });
        }).bind('dragover', function(ev)
        {
            ev.stopPropagation();
            ev.preventDefault();

            $(this).addClass('drop-inside');
        }).bind('dragleave', function(ev)
        {
            ev.stopPropagation();
            ev.preventDefault();

            $(this).removeClass('drop-inside');
        });
    },

    bindToggleEvents: function()
    {
        this.renderTarget.find('.node-toggle').bind('click', function(ev)
        {
            $(this).parentsUntil('.child').parent().toggleClass('closed');
            $(this).toggleClass('icon-chevron-down icon-chevron-right');
        });
    },

    renderTreeNode: function(node, domContext)
    {
        var childList, childContent, i, toggle, label;

        domContext.attr('id', node.identifier);
        if (node.identifier !== this.tree.rootNode.identifier)
        {
            toggle = $('<i></i>').addClass('node-toggle icon-chevron-down');
            label = $('<span></span>').addClass('node-label').text(node.label).prepend(toggle);

            domContext.append(label);
        }

        if (node.hasOwnProperty('children') && node.children.length > 0)
        {
            childList = $('<ul></ul>').addClass('children');
            domContext.append(childList);

            for (i in node.children)
            {
                if (node.children.hasOwnProperty(i))
                {
                    var childElement = $('<li></li>').addClass('child');
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
            //domContext.removeClass('odd even').addClass(even ? 'even' : 'odd');
            var children = domContext.children('ul').children('li');

            if (children.length > 0)
            {
                domContext.addClass('expandable');
                domContext.children('ul').children('li').each(function(i, element)
                {
                    traverseAndRefresh($(element));
                });
            }
            else
            {
                domContext.removeClass('expandable open closed');
            }

        };

        traverseAndRefresh(this.renderTarget);
    },

    loadData: function()
    {
        var dataContainer = this.domElement.find('.tree-data-json');

        this.tree = JSON.parse(dataContainer.text().trim());
    }

});


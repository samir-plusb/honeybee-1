honeybee.sidebar.SidebarTreeController = honeybee.core.BaseObject.extend({

    log_prefix: "SidebarTreeController",

    tree: {},

    treeRelation: null,

    init: function(options)
    {
        this.domElement = options.domElement;

        this.options = options || {};

        this.renderTarget = this.domElement.find('.render-tree');

        this.loadData();
        this.treeRelation = JSON.parse(this.domElement.attr('data-tree-relation'));

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
                field: that.treeRelation.referenceField + '.id',
                value: $(this).parent('.child').attr('id')
            });

            that.renderTarget.find('li.highlighted').removeClass('highlighted');
            $(this).parent('li').addClass('highlighted');
        });

        honeybee.core.events.on('clearFilter', function() 
        {
            that.renderTarget.find('li.highlighted').removeClass('highlighted');
        });

        that.renderTarget.find('.move-inside').bind('click', function()
        {
            honeybee.core.events.fireEvent('reference::targetSelected', {
                id: $(this).parents('.child').first().attr('id'),
                module: that.treeRelation.treeModule,
                reference_field: that.treeRelation.referenceField
            }); 
        });

        honeybee.core.events.on('reference::startTargetSelection', function(data) 
        {
            if (that.treeRelation.treeModule !== data.module) 
            {
                return;
            }

            that.renderTarget.find('.move-target').show();
        });

        honeybee.core.events.on('reference::cancelTargetSelection', function(data) 
        {
            that.renderTarget.find('.move-target').hide();
        });
    },

    bindDropEvents: function()
    {
        var that = this;
        this.renderTarget.find('.child').bind('drop', function(ev)
        {
            ev.stopPropagation();
            ev.preventDefault();
            that.renderTarget.find('.child').removeClass('drop-inside');

            var data = ev.originalEvent.dataTransfer.getData('text/plain');

            honeybee.core.events.fireEvent('itemDroppedOnItem', {
                sourceId: data,
                id: $(ev.target).parent('.child').attr('id'),
                module: that.treeRelation.treeModule,
                reference_field: that.treeRelation.referenceField,
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
        var that = this;
        this.renderTarget.find('.node-toggle').bind('click', function(ev)
        {
            var toggle = $(ev.target);
            var child = toggle.parentsUntil('.child').parent();
            child.toggleClass('closed');
            if (child.hasClass('expandable'))
            {
                toggle.toggleClass('hb-icon-minus hb-icon-plus');
            }
            ev.preventDefault();
            ev.stopPropagation();
        });

    },

    refreshCss: function()
    {
        var even = false;
        //encapsule in a function to avoid bloating mamory usage with "var that = this;" on every recursion
        var traverseAndRefresh = function(domContext)
        {
            even = !even;
            //domContext.removeClass('odd even').addClass(even ? 'even' : 'odd');
            var children = domContext.children('.children').children('.child');

            if (children.length > 0)
            {
                domContext.addClass('expandable');
                if (domContext.hasClass('closed'))
                {
                    domContext.find('.node-toggle').toggleClass('hb-icon-plus hb-icon-minus');
                }
                domContext.children('ul').children('li').each(function(i, element)
                {
                    traverseAndRefresh($(element));
                });
            }
            else
            {
                domContext.removeClass('expandable open closed');
                domContext.find('.node-toggle').html('〉');
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

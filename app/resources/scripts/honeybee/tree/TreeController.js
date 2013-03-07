honeybee.tree.TreeController = honeybee.list.ListController.extend({

    log_prefix: "TreeController",

    options: null,

    dropMode: null,

    tree: {},

    alerts: null,

    workflow_handler: null,

    init: function(options)
    {
        //this.parent();
        this.options = options;
        this.domElement = options.domElement;
        this.renderTarget = this.domElement.find('.render-tree');

        this.loadData();

        this.refreshCss(this.renderTarget);
        this.bindDragEvents();
        this.bindToggleEvents();

        this.initKnockoutProperties();

        this.bindActions();

        this.workflow_handler = new honeybee.list.WorkflowHandler(
            this.options.workflow_urls || {}
        );
    },

    initKnockoutProperties: function()
    {
        this.alerts = ko.observableArray([ ]);
        ko.applyBindings(this, this.domElement[0]);
    },

    attach: function()
    {
        this.confirm_dialog = new honeybee.list.ListController.ConfirmDialog('.dialog-confirm');
    },

    bindActions: function()
    {
        var that = this;
        this.domElement.find('.container-actions .honeybee-action').bind('click', function(ev)
        {
            var action = $(this).attr('data-action');
            that.proceed(true, null, action);
        });

        this.renderTarget.find('.child').each(function(index, element) {
            element = $(element);
            var documentData = JSON.parse(element.attr('data-document'));
            element.find('.honeybee-action-proceed').bind('click', function(ev){
                var confirm_text;
                var gate = $(this).attr('data-gate');
                $.each(documentData.workflow.gates, function(i, value){
                    if (gate === value.name) {
                        confirm_text = value.prompt;
                    }
                });

                that.proceed(false, documentData, gate, confirm_text);
            });
            element.find('.honeybee-action-edit').bind('click', function(ev){
                that.run(false, documentData); 
            });
        });
    },

    getSelectedItems: function()
    {
        var checkboxes = this.renderTarget.find('.child input:checkbox[checked=checked]');
        var items = [];

        checkboxes.each(function(index, element)
        {
            var parentNode = $(element).parent().parent();
            items.push(JSON.parse(parentNode.attr('data-document')));
        });

        return items;
    },

    bindToggleEvents: function()
    {
        this.renderTarget.find('.node-toggle').bind('click', function(ev)
        {
            $(this).parentsUntil('.child').parent().toggleClass('closed');
            $(this).toggleClass('icon-minus icon-plus');
        });
    },

    bindDragEvents: function()
    {
        var that = this;
        var lastNode;

        var clearDragCss = function(element)
        {
            var classes = 'drop-before drop-inside drop-after';
            that.renderTarget.find('.child').removeClass(classes);
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

            var borderAreaHeight = 8;

            if (dragY < elementY + borderAreaHeight) 
            {
                that.dropMode = 'before';
            }
            else if (dragY > elementY + height - borderAreaHeight)
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

        this.tree.rootNode = buildJsonNode(this.renderTarget);

        this.saveData(); //doesn't work on server side yet. 
    },

    saveData: function()
    {
        var that = this;

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
            //console.log(response);
            that.addAlert({
                type: 'success',
                message: 'Gespeichert'
            });

        }).fail(function(response)
        {
            //console.log(response.responseText);
            that.addAlert({
                type: 'error',
                message: 'Speichern fehlgeschlagen!'
            });
        });
    },

    addAlert: function(alert)
    {
        var that = this;
        this.alerts.push(alert);
        setTimeout(function()
        {
            if (-1 !== that.alerts.indexOf(alert)) // if alert is still there.
            {
                that.removeAlert(alert);
            }
        }, 10000);
    },

    removeAlert: function(alert)
    {
        this.alerts.remove(alert);
    },

    showAlert: function(elem, idx, alert) 
    { 
        var that = this;
        if (elem.nodeType === 1) 
        {
            $(elem).animate({
               opacity: 1
            }, { duration: 600, queue: false });
            $(elem).animate({
               'margin-top': '0px'
            }, { duration: 400, queue: false });
        }
    },

    hideAlert: function(elem, idx, alert)
    {
        if (elem.nodeType === 1) 
        {
            $(elem).animate({
               opacity: 0
            }, { duration: 300, queue: false });
            $(elem).animate({
               'margin-top': '-55px'
            }, { duration: 600, queue: false, complete: function() 
            {
                $(elem).remove();
            } });
        }
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


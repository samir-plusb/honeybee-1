honeybee.sidebar.SidebarController = honeybee.core.BaseObject.extend({

    log_prefix: "SidebarController",

    options: null,

    currentWidth: null,

    init: function(options)
    {
        this.parent();
        this.options = options;
        this.domElement = options.domElement;
        this.siblingWrapper = this.domElement.siblings('.wrapper');

        this.domElement.height(this.domElement.parent().height() - parseInt(this.domElement.css('margin-top'), 10) - 37);
        this.domElement.children('.slots').height(this.domElement.height());

        this.bindEdgeEvents();
    },

    bindEdgeEvents: function()
    {
        var edge = this.domElement.children('.edge-handles');
        var that = this;

        var lastX = null;

        var moveHandler = function (ev)
        {
            var dX = ev.originalEvent.pageX - lastX;
            that.currentWidth = that.domElement.width() + dX;
            that.setWidth(that.currentWidth);
            lastX = ev.originalEvent.pageX;
        };

        edge.find('.expand').bind('click', function(ev)
        {
            if (that.currentWidth)
            {
                that.setWidth(that.currentWidth);
            }
            that.domElement.removeClass('sidebar-hidden');
        });
        edge.find('.collapse').bind('click', function(ev)
        {
            that.currentWidth = that.domElement.width();
            that.domElement.addClass('sidebar-hidden');
            that.setWidth(0);
        });

        edge.find('.drag').bind('mousedown', function(ev) {
            if (that.domElement.hasClass('sidebar-hidden'))
            {
                return;
            }

            lastX = ev.originalEvent.pageX;
            $(document).bind('mousemove', moveHandler).
                bind('mouseup', function()
                {
                    $(document).unbind('mousemove', moveHandler);
                });
        });
    },

    setWidth: function(width)
    {
        var widthBefore = this.domElement.width();
        this.domElement.css('width', width + 'px');

        if (this.domElement.width() !== widthBefore)
        {
            this.siblingWrapper.css('margin-left', this.domElement.width());
        }
    },

    attach: function()
    {
    }

});

honeybee.sidebar.SidebarController.create = function(element, namespace)
{
    jqElement = $(element);

    if (0 === jqElement.length)
    {
        throw "Unable to find element to create controller from. Looked for: " + element;
    }
    var controller_class = jqElement.attr('data-controller');
    if (! controller_class || ! namespace[controller_class])
    {
        throw "Unable to resolve controller implementor: " + controller_class;
    }

    var options = jqElement.attr('data-controller-options') || "{}";
    options = options === null ? {} : JSON.parse(options);
    options.domElement = jqElement;
    var controller = new namespace[controller_class](options);

    return controller;
};



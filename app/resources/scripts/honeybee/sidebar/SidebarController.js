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
        this.domSlotsElement = this.domElement.find('.slots');
        this.domDataList = $('.data-list');
        this.domNavbar = $('.navbar');
        this.domFooter = $('.footer');
        this.domSidebarPushElement = this.domElement.find('.push');

        this.bindEdgeEvents();

        var that = this;
        var messageEventHandler = function(event)
        {
            if (0 === that.options.origin.indexOf(event.origin))
            {
                var msg_data = JSON.parse(event.data);
                if (msg_data.event_type === 'list-loaded')
                {
                    that.adjustHeight();
                }
            }
        }
        window.addEventListener('message', messageEventHandler, false);
    },

    adjustHeight: function()
    {
        var viewportHeight = $(window).height() - +this.domFooter.outerHeight() - +this.domNavbar.outerHeight() - 17;

        var list_height = +this.domDataList.outerHeight() + +this.domDataList.position().top - +this.domNavbar.outerHeight() + this.domSidebarPushElement.outerHeight();

        if (list_height < viewportHeight)
        {
            list_height = viewportHeight; // for short lists we adjust the sidebar height to the viewport space that is left
        }

        this.domElement.height(list_height);
        this.domSlotsElement.height(list_height);
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
        throw "[SidebarController] Unable to find element to create controller from. Looked for: " + element;
    }
    var controller_class = jqElement.attr('data-controller');
    if (! controller_class || ! namespace[controller_class])
    {
        throw "[SidebarController] Unable to resolve controller implementor: " + controller_class;
    }

    var options = jqElement.attr('data-controller-options') || "{}";
    options = options === null ? {} : JSON.parse(options);
    options.domElement = jqElement;
    var controller = new namespace[controller_class](options);

    return controller;
};



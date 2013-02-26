honeybee.navigation.MainNavigationController = honeybee.core.BaseObject.extend({

    log_prefix: "MainNavigationController",

    options: null,

    currentWidth: null,
    initialWidth: null,
    availableWidth: null,
    hiddenWidths: [],

    init: function(options)
    {
        this.parent();
        this.options = options;
        this.domElement = options.domElement;

        var that = this;

        $(window).on('resize', function(ev)
        {
            that.handleResizeEvent(ev);
        });

        this.initialWidth = this.domElement.outerWidth();
        this.currentWidth = this.initialWidth;

        this.domElement.parent().children().css('outline', '1px solid red');
        this.overflowElement = $('<ul></ul>').addClass('overflow-nav');

        this.overflowElement.insertAfter(this.domElement);
        this.resizeToFit();
    },

    handleResizeEvent: function (ev)
    {
        this.availableWidth = this.getAvailableWidth();
        this.resizeToFit();
    },

    resizeToFit: function ()
    {
        var lastElement, hiddenWidth;

        if (this.currentWidth > this.availableWidth)
        {
            this.hiddenWidths.push(this.hideNavElement());
            this.currentWidth = this.domElement.outerWidth();
        }
        else
        {
            hiddenWidth = this.hiddenWidths.pop();
            if (this.currentWidth + hiddenWidth < this.availableWidth)
            {
                this.showNavElement();
            }
            else
            {
                this.hiddenWidths.push(hiddenWidth);
            }
            this.currentWidth = this.domElement.width();
        }
    },

    getAvailableWidth: function()
    {
        var width = this.domElement.parent().width();
        var that = this;
        this.domElement.siblings().each(function(index, element)
        {
            if (element !== that.domElement[0])
            {
                width = width - $(element).outerWidth(true);
            }
        });

        return width;
    },

    hideNavElement: function()
    {
        var width;
        element = this.domElement.children().last();
        width = element.width();
        element.prependTo(this.overflowElement);

        return width;
    },

    showNavElement: function(element)
    {
        var width;
        element = this.overflowElement.children().first();
        width = element.width();
        element.appendTo(this.domElement);

        return width;
    }

});

honeybee.navigation.MainNavigationController.create = function(element, namespace)
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



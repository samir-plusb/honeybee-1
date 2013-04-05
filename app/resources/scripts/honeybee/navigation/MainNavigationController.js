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
        this.domElement = $(options.domElement);

        var that = this;

        this.initialWidth = this.domElement.outerWidth();
        this.currentWidth = this.initialWidth;

        //this.domElement.parent().children().css('outline', '1px solid red');

        this.domElement.append($('<li id="more-menu" class="dropdown more"><a data-toggle="dropdown" class="dropdown-toggle" role="button" href="#" id="drop-more"> Mehr...<b class="caret"></b></a><ul aria-labelledby="drop-more" role="menu" class="dropdown-menu" id="overflow-modules"></ul></li>'));
        this.overflowElement = $('#more-menu');
        this.overflowListElement = $('#overflow-modules');
        this.overflowElement.hide();

        this.handleResizeEvent();

        var resizeTimer;
        $(window).resize(function(ev)
        {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(that.handleResizeEvent(ev), 100);
        });

        this.replaceAvatarImage();
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
        element = this.domElement.children('li.dropdown').not('.more').last();
        width = element.width();

        element.removeClass('dropdown').addClass('dropdown-submenu pull-left');
        element.find('a > b.caret').hide();
        element.prependTo(this.overflowListElement);

        if (this.overflowListElement.children().length > 0)
        {
            this.overflowElement.show();
        }

        return width;
    },

    showNavElement: function(element)
    {
        var width;
        element = this.overflowListElement.children().first();
        width = element.width();

        element.addClass('dropdown').removeClass('dropdown-submenu pull-left');
        element.find('a > b.caret').show();
        element.insertBefore(this.overflowElement);

        if (this.overflowListElement.children().length == 0)
        {
            this.overflowElement.hide();
        }

        return width;
    },

    replaceAvatarImage: function()
    {
        var that = this;
        var online = window.navigator.onLine;
        if (online)
        {
            $('.avatar-image-wrapper').each(function(index, item)
            {
                var item = $(item);
                var url = item.data('honeybee-avatar-url');
                if (!url)
                {
                    return;
                }
                var img = new Image();
                img.onload = function()
                {
                    $(img).hide().appendTo(item).fadeIn({duration: 1500});
                }
                img.className = 'avatar-image';
                img.src = url;
            });
            
        }
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



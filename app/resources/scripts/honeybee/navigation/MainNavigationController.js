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
        this.minimumWidthNeeded = this.overflowElement.outerWidth();
        this.overflowElement.hide();

        this.loginText = $('#fat-menu > p');
        this.loginTextWidth = this.loginText.outerWidth();

        this.handleResizeEvent();

        var resizeTimer;
        $(window).resize(function(ev)
        {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function()
            {
                that.handleResizeEvent();
            }, 100);
        });

        this.replaceAvatarImage();
    },

    handleResizeEvent: function (ev)
    {
        this.resizeToFit();
    },

    resizeToFit: function ()
    {
        var lastElement, addedWidth, removedWidth;
        var counter = 0;

        this.availableWidth = this.getAvailableWidth();
        this.currentWidth = this.domElement.outerWidth();

        if (this.currentWidth >= this.availableWidth)
        {
            this.loginText.hide();
        }

        this.availableWidth = this.getAvailableWidth();
        this.currentWidth = this.domElement.outerWidth();

        if (this.currentWidth > this.availableWidth)
        {
            while (this.currentWidth > this.availableWidth)
            {
                counter++;
                removedWidth = this.hideNavElement();
                this.currentWidth = this.domElement.outerWidth();
                if (removedWidth === 0 || counter > 50) // magic constant for maximum number of modules :-D
                {
                    break;
                }
                this.hiddenWidths.push(removedWidth);
            }
        }
        else
        {
            addedWidth = this.hiddenWidths.pop() || 0;
            while (this.currentWidth + addedWidth + 10 < this.availableWidth)
            {
                counter++;
                addedWidth = this.showNavElement();
                this.currentWidth = this.domElement.outerWidth();
                this.availableWidth = this.getAvailableWidth();
                addedWidth = this.hiddenWidths.pop() || 0;
                if (addedWidth === 0 || counter > 50) // magic constant for maximum number of modules :-D
                {
                    break;
                }
            }

            if (counter === 0)
            {
                this.hiddenWidths.push(addedWidth);
            }

        }

        if (this.getAvailableWidth() > (this.currentWidth + this.loginTextWidth + 10))
        {
            this.loginText.show();
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
        element = this.domElement.children('li.dropdown').not('.more').last();
        var width = element.outerWidth() || 0;
        if (width === 0)
        {
            return 0;
        }

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
        element = this.overflowListElement.children().first();
        element.addClass('dropdown').removeClass('dropdown-submenu pull-left');
        element.find('a > b.caret').show();
        element.insertBefore(this.overflowElement);

        if (this.overflowListElement.children().length == 0)
        {
            this.overflowElement.hide();
        }

        return element.outerWidth() || 0;
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
        throw "[NavigationController] Unable to find element to create controller from. Looked for: " + element;
    }
    
    var controller_class = element.attr('data-controller');
    if (! controller_class || ! namespace[controller_class])
    {
        throw "[NavigationController] Unable to resolve controller implementor: " + controller_class;
    }

    var options = element.attr('data-controller-options') || "{}";
    options = options === null ? {} : JSON.parse(options);
    options.domElement = element;
    var controller = new namespace[controller_class](options);

    return controller;
};



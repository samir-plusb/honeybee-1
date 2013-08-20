honeybee.widgets.Widget = honeybee.core.BaseObject.extend({

    // #########################
    // #     property defs     #
    // #########################
    log_prefix: "Widget",

    element: null,

    options: null,

    init: function(element, options, ready_callback)
    {
        this.parent();
        ready_callback = ready_callback || function() {};
        // basic member initialization
        this.element = element;
        this.options = $.extend(true, {}, options);
        // render the widget
        var tpl = this.getTemplate();
        var that = this;
        if (tpl && 0 <= tpl.indexOf('.html'))
        {
            this.element.load(tpl, null, function()
            {
                that.initGui();
                ready_callback();
            });
            return;
        }
        else if (tpl)
        {
            this.element.html(tpl);
        }
        that.initGui();
        ready_callback();
    },

    initGui: function()
    {
        // setup knockout properties
        this.initKnockoutProperties();
        // then bind this instance to the (rendered)widget gui.
        if (this.options.autobind)
        {
            ko.applyBindings(this, this.element[0]);
        }
    },

    /**
     * Override this method to init your knockout bindings.
     */
    initKnockoutProperties: function() { },

    /**
     * Override this method to provide your widget's template,
     * which will be rendered inside the widgets container.
     */
    getTemplate: function()
    {
        throw "Your widget does not implement a getTemplate method! Make sure to do so and return a valid html string.";
    }
});
/**
 * @todo Test for instanceof honeybee.widgets.Widget
 * @todo Move all widgets to the honeybee.widgets namespace by default.
 */
honeybee.widgets.Widget.factory = function(element, type_key, namespace, ready_callback)
{
    ready_callback = ready_callback || function(){};
    // resolve hyphen separated class keys to real class names
    var implementor = type_key.replace(
        /(\-[a-z])/g,
        function($1)
        {
            return $1.toUpperCase().replace('-','');
        }
    );
    implementor = implementor.charAt(0).toUpperCase() + implementor.slice(1);

    namespace = namespace || honeybee.widgets;
    element = $(element);
    var widget_class = namespace[implementor];
    if (! widget_class)
    {
        throw "Unable to find widget implementor: " + implementor + " inside the given namespace: " + namespace.toString();
    }
    // create type key to query options
    var dash_case_impl_name = implementor.replace(
        /([A-Z])/g,
        function($1) { return "-" + $1.toLowerCase(); }
    );
    var opt_attr_name = 'data' + dash_case_impl_name + '-options';
    var options;
    try
    {
        // merge options ...
        options = $.extend(
            {},
            widget_class.DEFAULT_OPTIONS || {},
            JSON.parse(element.attr(opt_attr_name) || "{}")
        );
    }
    catch(err)
    {
        console.log(element, err);
        return;
    }
    // ... and create instance.
    return new widget_class(element, options, ready_callback);
};
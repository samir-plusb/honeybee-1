midas.core.Behaviour = {
    prefix: 'jsb_',

    handlers: {},

    handler_instances: {},

    event_listeners: {},

    register: function(key, handler_function)
    {
        this.handlers[key] = handler_function;
    },

    call: function(key, dom_element)
    {
        if (typeof this.handlers[key] === 'undefined')
        {
            throw new Error('The handler ' + key + ' is not defined!');
        }

        var input_element = $('input', dom_element).first();

        if (input_element)
        {
            var value = JSON.parse(input_element.val());
            this.handler_instances[key] = new this.handlers[key](dom_element, value);
        }
        else
        {
            this.handler_instances[key] = new this.handlers[key](dom_element);
        }

        if ('function' === typeof this.handler_instances[key].addEvent)
        {
            this.handler_instances[key].addEvent('event_fired', JsBehaviourToolkit.dispatchEvent);
        }
    },

    apply: function(dom_element)
    {
        var dom_elements = $('.' + this.prefix, dom_element);
        var dom_elements_length = dom_elements.length;

        var dom_element;
        var key;
        for (var i = 0; i < dom_elements_length; i++)
        {
            dom_element = dom_elements[i];
            key = dom_element.attr('class').match(/jsb_([^\s]+)/)[1];
            this.call(key, dom_element);
            dom_element.removeClass(this.prefix);
            dom_element.removeClass(this.prefix + '' + key);
        }
    }
};

$(document).ready(function() {
    midas.core.Behaviour.apply(window.document);
});
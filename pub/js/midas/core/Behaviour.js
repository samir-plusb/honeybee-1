Midas.Behaviour = {
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

        var input_element = $('input:first', dom_element);

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

        this.fireEvent('behaviour_applied', [{'behaviour_name': key, 'behaviour': this.handler_instances[key]}]);
    },

    apply: function(dom_element)
    {
        var dom_elements = $(dom_element).getElements('.' + this.prefix);
        var dom_elements_length = dom_elements.length;

        for (var i = 0; i < dom_elements_length; i++)
        {
            var dom_element = dom_elements[i];
            var key = dom_element.get('class').match(/jsb_([^\s]+)/)[1];
            this.call(key, dom_element);
            dom_element.removeClass(this.prefix);
            dom_element.removeClass(this.prefix + '' + key);
        }

    }
};

$(document).ready(function() {
    Mida.Behaviour.apply(window.document);
});
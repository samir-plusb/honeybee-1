honeybee.core.events = {};

(function(events, undefined) {

    var handlers = {};

    var getNewId = (function()
    {
        var nextId = -1;
        return function()
        {
            nextId++;
            return nextId;
        };
    })();

    events.on = function (eventName, handler, callLimit)
    {
        var entry;

        if (typeof handlers[eventName] === "undefined")
        {
            handlers[eventName] = [];
        }

        entry = {
            handler: handler,
            callCount: 0,
            callLimit: callLimit 
        };
        
        handlers[eventName].push(entry);

        return function()
        {
            events.off(eventName, handler);
        };
    };

    events.once = function (eventName, handler)
    {
        return this.on(eventName, handler, 1);
    };

    events.off = function (eventName, handler)
    {
        var keepList = [];
        if (eventName in handlers)
        {
            handlers[eventName].forEach(function(element, index, arr)
            {
                if (element.handler !== handler)
                {
                    keepList.push(element);
                }
            }, this);
            handlers[eventName] = keepList;       
        }
    };

    events.removeAllHandlers = function (eventName)
    {
        handlers[eventName] = [];
    };

    events.fireEvent = function (eventName, data)
    {
        if (typeof handlers[eventName] === "undefined")
        {
            return;
        }
        
        handlers[eventName].forEach(function(element, index, arr)
        {
            var executeHandler = element.handler;
            element.callCount++;
            if (typeof element.callLimit !== "undefined" && element.callCount >= element.callLimit)
            {
                events.off(eventName, element.handler);
            }

            executeHandler(data);
        });
    };

})(honeybee.core.events);

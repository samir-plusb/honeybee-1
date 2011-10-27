midas.core.Module = (function()
{
    // Used to verify that our module_def constructor
    // is being called from inside this scope.
    var SECRET_TOKEN = function() {};

    /**
     * Return if the passed argument is a function or not.
     */
    var is_func = function(fn)
    {
        return (typeof fn == "function");
    };

    var module = function() {};

    /**
     *
     */
    module.create = function(module_def)
    {
        /**
         * The constructor called upon new instances of our class.
         * As we do not wnat to invoke the '_initialize' (sugar) method,
         * when running inside an extend call.
         *
         * @param init_token
         */
        var new_module = function(init_token)
        {
            if (init_token === SECRET_TOKEN)
            {
                return;
            }

            if (is_func(this.init))
            {
                // When invoked without a matching init_token,
                // we do our '_initialize' constructor method thingy.
                this.init.apply(this, arguments);
            }
        };

        // Create a new instance of our base definition
        // and pass in our 'init_token' to prevent funky stuff from happening.
        new_module.prototype = new this(SECRET_TOKEN);

        // Then copy our new attributes to our fresh class instance.
        for (attribute_name in module_def) (function(attribute_value, parent_attribute_value)
        {
            if (! is_func(attribute_value) || ! is_func(parent_attribute_value))
            {
                // clone objects or define they can eariliest be defined inside init?
                new_module.prototype[attribute_name] = attribute_value;
            }
            else
            {
                // Add some 'parent' method call sugar.
                new_module.prototype[attribute_name] = function()
                {
                    this.parent = parent_attribute_value;
                    return attribute_value.apply(this, arguments);
                };
            }
        })(module_def[attribute_name], new_module.prototype[attribute_name]);

        // Then setup our constructor and add the extend method for further inheritance.
        new_module.prototype.constructor = new_module;
        new_module.extend = this.extend || this.create;

        return new_module;
    };

    return module;
})();
/**
 * This file sets up and documents the different midas namespace sections.
 */
(function(env)
{
    env.midas = {
        /**
         * The core package holds all midas core objects, such as Module, Behaviour and Logger.
         */
        core: {},
        /**
         * The view package holds code related to the different views of the system.
         * Mostly you will have a subnamespace for each view that holds all the dedicated components.
         */
        views: {}
    };
})(window);
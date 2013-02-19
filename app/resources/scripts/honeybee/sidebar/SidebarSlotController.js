honeybee.sidebar.SidebarSlotController = honeybee.core.BaseObject.extend({

    log_prefix: "SidebarSlotController",

    init: function(options)
    {
        this.domElement = options.domElement;

        this.bindToggleEvents();
    },

    bindToggleEvents: function()
    {
    }
});


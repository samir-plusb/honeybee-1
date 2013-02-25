(function(namespace, $, undefined)
{
    if (0 < $('.sidebar').length)
    {
        namespace.SidebarController.create('.sidebar', honeybee.sidebar);
        namespace.SidebarController.create('.sidebar-slot', honeybee.sidebar);
        namespace.SidebarController.create('.sidebar-tree', honeybee.sidebar);
    }
})(honeybee.sidebar, $);

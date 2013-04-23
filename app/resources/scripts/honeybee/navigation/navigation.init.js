(function()
{
    var navigation_container = $('header .nav[role="navigation"]');
    if (1 === navigation_container.length)
    {
        honeybee.navigation.MainNavigationController.create(navigation_container, honeybee.navigation);
    }
})();

// asset-item-tpl
/**
 * @class
 * @augments midas.core.BaseObject
 * @description The AssetList provides rendering of and access to a collection of assets that belong to
 * the currently displayed import item inside the edit view.
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.AssetList = midas.core.BaseObject.extend(
/** @lends midas.items.edit.AssetList.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'AssetList',

    /**
     * The html list element that is used to render the content items collection.
     * @type HTMLUlElement
     */
    element: null,

    /**
     * An object holdingour current asset data.
     * @type {Object}
     */
    assets: null,

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {HTMLElement} element The HTMLUlElement that serves as the ui base for our asset list.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(element, options)
    {
        this.parent(options);
        this.element = $(element);

        if (0 < this.element.find('li').length)
        {
            this.show();
        }
    },

    hydrate: function(assets)
    {
        this.clear();

        for (var i = 0; i < assets.length; i++)
        {
            this.element.append(
                ich['asset-item-tpl'](assets[i])
            );
        }
    },

    clear: function()
    {
        this.element.empty();
    },

    hide: function()
    {
        this.options.tab.hide();
    },

    show: function()
    {
        console.log(this.options.tab);
        this.options.tab.show();
    }
});

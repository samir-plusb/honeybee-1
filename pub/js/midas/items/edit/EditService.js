/**
 * @class
 * @augments midas.items.edit.EditService
 * @description <p>The EditService module...</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.EditService = midas.core.BaseObject.extend(
/** @lends midas.items.edit.EditService.prototype */
{
    log_prefix: 'EditService',

    init: function(options)
    {
        this.parent(options);
    },

    removeHyphens: function(text)
    {
        var newString = text.replace(/\-/img, "");
        return newString.replace(/\s{2,99}/img, ' ');
    },

    removeLineFeeds: function(text)
    {
        var newString = text.replace(/\n/img, " ");
        return newString.replace(/\s{2,99}/img, ' ');
    },

    extractUrls: function(text)
    {
        // correct uris
        var urlPattern = /\b(?:https?|ftp):\/\/[a-z0-9-+&@#\/%?=~_|!:,.;]*[a-z0-9-+&@#\/%=~_|]/gim;
        // just host (www.) without scheme (http:// or https://)
        var pseudoUrlPattern = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
        // Email addresses
        //var emailAddressPattern = /\w+@[a-zA-Z_]+?(?:\.[a-zA-Z]{2,6})+/gim;

        var urls = text.match(urlPattern) || [];
        $.merge(urls, text.match(pseudoUrlPattern) || []);

        return urls;
    },

    extractLocation: function(text, callback)
    {

    },

    extractDate: function(text, callback)
    {

    },

    testUrl: function(url, callback)
    {

    }
})
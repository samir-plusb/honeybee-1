/**
 * @class
 * @augments midas.core.BaseObject
 * @description <p>The EditService module...</p>
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.edit.EditService = midas.core.BaseObject.extend(
/** @lends midas.items.edit.EditService.prototype */
{
    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: 'EditService',

    routing: null,

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(routing, options)
    {
        this.parent(options);
        this.routing = routing;
    },

    /**
     * @description Removes all hyphens from the given text.
     * @param {String} text
     * @return {String}
     */
    removeHyphens: function(text)
    {
        var newString = text.replace(/\-/img, "");
        return newString.replace(/\s{2,99}/img, ' ');
    },

    /**
     * @description Removes all linebreaks from the given text.
     * @param {String} text
     * @return {String}
     */
    removeLineFeeds: function(text)
    {
        var newString = text.replace(/\n/img, " ");
        return newString.replace(/\s{2,99}/img, ' ');
    },

    /**
     * @description Extracts all urls from the given text.
     * @param {String} text
     * @return {Array}
     */
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

    /**
     * @description Searches and extracts a location from the given text.
     * @param {String} text
     * @param {Function} callback Invoked when the location is available or search completed without results.
     */
    extractLocation: function(text, callback)
    {
        text += ' Berlin'; // help the api localize stuff in berlin.
        $.getJSON(this.routing.getRoute('api_extract_location'), {geo_text: text}, function(data)
        {
            var location_count = data.location.items_count;
            var found_locations = [];
            for (var i = 0; i < location_count; i++)
            {
                var loc = data.location[i];

                if (loc && "Außerhalb Berlins" != loc['administrative district'])
                {
                    found_locations.push(loc);
                }
            }
            callback(found_locations);
        });
    },

    addSpacesAfterDots: function(text)
    {
        var urls = this.extractUrls(text);
        var max = urls.length || 0;
        var tokens = {};
        for (var i = 0; i < max; i++)
        {
            var token = 'URL-{'+(i+1)+'}';
            tokens[token] = urls[i];
            text = text.replace(urls[i], token);
        }
        text = text.replace(/\b\.\b/ig, '. ')
        for (var cur_token in tokens)
        {
            text = text.replace(cur_token, tokens[cur_token]);
        }
        return text;
    },

    /**
     * @description Searches and extracts a date from the given text.
     * @param {String} text
     * @param {Function} callback Invoked when the date is available or search completed without results.
     */
    extractDate: function(text, callback)
    {
        $.getJSON(this.routing.getRoute('api_extract_date'), {date_text: text}, function(data)
        {
            callback(data.date);
        });
    },

    /**
     * @description Validates a given url.
     * @param {String} text
     * @param {Function} callback Invoked when the validation has completed.
     */
    validateUrl: function(url, callback)
    {
        $.getJSON(this.routing.getRoute('api_validate_url'), {url: url}, function(result)
        {
            callback(result);
        });
    }
})
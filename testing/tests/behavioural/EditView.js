(function()
{

var zombie = require("zombie");
var vows = require("vows");
var assert = require("assert");
var util = require("util");

// The url we are testing.
var test_url = process.env.BASE_HREF + "index.php/items/edit";

// Test helper for loading page markup.
var loadPage = function(url, callback, debug)
{
    var browser = new zombie.Browser({debug: debug || false});
    browser.runScripts = true;
    var that = this;
    browser.visit(
        test_url,
        function(err, browser, status)
        {
            callback.apply(that, [err, browser]);
        }
    );
};

var openContextMenuWithRandomText = function(browser, start, max, end_min)
{
    end_min = end_min || max - start;
    var end = Math.floor(Math.random() * (max - start + 1)) + start;
    // Define our selection range.
    var range = {start: start, end: end_min > end ? end_min : end};
    // Set text selection inside the item-content textarea.
    browser.evaluate("$('.item-content textarea').focus();");
    browser.evaluate("$('.item-content textarea').caret("+range.start+", "+range.end+");");
    // Then open the context menu.
    browser.evaluate(
        "$('.item-content textarea').trigger('mousedown',{button:2}).trigger('mouseup');"
    );

    return range;
};

// Add the test suite.
vows.describe('EditView').addBatch({
    'When the edit-view': {

        'loads from a given url': {
            topic: function()
            {
                loadPage(test_url, this.callback);
            },
            'it should initialize without errors.': function(e, browser)
            {
                assert.isNull(e, "An error occured while loading the edit view markup.");
            },
            'it should have a tab-handler setup on the import-item\'s content.': function(e, browser)
            {
                assert.isDefined(
                    browser.querySelector('.ui-tabs-nav'),
                    "The import item tab plugin has not been initialized."
                );
            },
            'it should have a tag-handler setup for the content-item\'s tag field.': function(e, browser)
            {
                assert.isDefined(
                    browser.querySelector('.ui-autocomplete-input'),
                    "The import item taghandler plugin has not been initialized."
                );
            }
        },
        'submits an empty form': {
            topic: function(err, browser)
            {
                var that = this;
                var mandatory_fields = [
                    'select[name="category"]',
                    'select[name="priority"]',
                    'select[name="location[relevance]"]',
                    'input[name="title"]',
                    'textarea[name="text"]',
                    'input[name="source"]',
                    'ul.tagHandlerContainer'
                ];
                loadPage(test_url, function(err, browser, status)
                {
                    browser.fire(
                        "click",
                        browser.querySelector('.action-store'),
                        function()
                        {
                            that.callback(browser, mandatory_fields);
                        }
                    );
                });
            },
            'it should mark all mandatory fields as invalid.': function(browser, mandatory_fields)
            {
                for (var i = 0; i < mandatory_fields.length; i++)
                {
                    assert.match(
                        browser.querySelector(mandatory_fields[i]).getAttribute('class'),
                        /ui-state-error/
                    );
                }
            }
        },
        'submits a form with all mandatory fields and the date[from] field provided': {
            topic: function(err, browser)
            {
                var that = this;
                loadPage(test_url, function(err, browser, status)
                {
                    // Fill all trivial form inputs.
                    var values = {
                        'title': 'This is a test title.',
                        'text': 'This is a test text.',
                        'source': 'test-source'
                    };
                    for (var name in values)
                    {
                        browser.fill(name, values[name]);
                    }
                    // Then set our select values.
                    browser.select('priority', '1');
                    browser.select('category', 'Kiezleben');
                    browser.select('location[relevance]', '1');
                    // Open the date[from] datepicker and pick our desired date.
                    var select_day = "16";
                    browser.evaluate("$('input[name=\"date[from]\"]').focus();");
                    var dates = browser.querySelectorAll('#ui-datepicker-div .ui-datepicker-calendar td');
                    for (var i = 0; i < dates.length; i++)
                    {
                        var date = dates[i];
                        var link = date.childNodes[0];
                        if (date && 1 <= link.childNodes.length && select_day == link.childNodes[0].nodeValue)
                        {
                            browser.fire('click', date);
                        }
                    }
                    // Open the taghandler autocomplete and add the first tag from the suggestion list.
                    browser.evaluate("$('li[class=\"tagInput\"] input').focus();");
                    browser.evaluate("$('.ui-menu-item a').first().mouseover();");
                    browser.evaluate("$('.ui-menu-item a').first().click();");
                    // Then click save.
                    browser.fire("click", browser.querySelector('.action-store'), that.callback);
                });
            },
            'the date[till] field should have the same value as the date[from] field.': function(browser)
            {
                assert.equal(
                    browser.querySelector('input[name="date[from]"]').nodeValue,
                    browser.querySelector('input[name="date[till]"]').nodeValue
                );
            },
            'it should create a new content item.': function(browser)
            {
                assert.isDefined(
                    browser.querySelector('.content-items .content-item')
                );
            }
        },
        'opens the import-item context-menu and the "Set Title" menu-item is clicked': {
            topic: function(err, browser)
            {
                var that = this;
                loadPage(test_url, function(err, browser, status)
                {
                    var range = openContextMenuWithRandomText(browser, 0, 100, 30);
                    var item = browser.querySelector('#content-data-menu-default li:nth-child(3)');
                    browser.fire('click', item, function() {that.callback(browser, range);});
                });
            },
            'the title should be set to the textarea selection.': function(browser, range)
            {
                assert.equal(
                    browser.querySelector('input[name="title"]').value,
                    browser.querySelector('.item-content textarea').value.substr(range.start, range.end - range.start)
                );
            }
        },
        'opens the import-item context-menu and the "Append Title" menu-item is clicked': {
            topic: function(err, browser)
            {
                var that = this;
                loadPage(test_url, function(err, browser, status)
                {
                    var range = openContextMenuWithRandomText(browser, 5, 30, 15);
                    var item = browser.querySelector('#content-data-menu-default li:nth-child(4)');
                    var old_title = browser.querySelector('input[name="title"]').value;
                    browser.fire('click', item, function() {that.callback(browser, range, old_title);});
                });
            },
            'the textarea selection should be appended to the current title.': function(browser, range, old_title)
            {
                assert.equal(
                    browser.querySelector('input[name="title"]').value,
                    old_title + browser.querySelector('.item-content textarea').value.substr(range.start, range.end - range.start)
                );
            }
        },
        'opens the import-item context-menu and the "Set Textbody" menu-item is clicked': {
            topic: function(err, browser)
            {
                var that = this;
                loadPage(test_url, function(err, browser, status)
                {
                    var range = openContextMenuWithRandomText(browser, 10, 400, 200);
                    var item = browser.querySelector('#content-data-menu-default li:nth-child(5)');
                    browser.fire('click', item, function() {that.callback(browser, range);});
                });
            },
            'the text input should be set to the textarea selection.': function(browser, range)
            {
                assert.equal(
                    browser.querySelector('textarea[name="text"]').value,
                    browser.querySelector('.item-content textarea').value.substr(range.start, range.end - range.start)
                );
            }
        },
        'opens the import-item context-menu and the "Append To Textbody" menu-item is clicked': {
            topic: function(err, browser)
            {
                var that = this;
                loadPage(test_url, function(err, browser, status)
                {
                    var range = openContextMenuWithRandomText(browser, 50, 200, 100);
                    var item = browser.querySelector('#content-data-menu-default li:nth-child(6)');
                    var old_text = browser.querySelector('textarea[name="text"]').value;
                    browser.fire('click', item, function() {that.callback(browser, range, old_text);});
                });
            },
            'the textarea selection should be appended to the current text input.': function(browser, range, old_text)
            {
                assert.equal(
                    browser.querySelector('textarea[name="text"]').value,
                    old_text + browser.querySelector('.item-content textarea').value.substr(range.start, range.end - range.start)
                );
            }
        },
        'opens the import-item context-menu and the "Extract Url" menu-item is clicked': {
            topic: function(err, browser)
            {
                var that = this;
                loadPage(test_url, function(err, browser, status)
                {
                    var range = openContextMenuWithRandomText(browser, 50, 120, 100);
                    var item = browser.querySelector('#content-data-menu-default li:nth-child(7)');
                    browser.fire('click', item, function() {that.callback(browser, range);});
                });
            },
            'the textarea selection should be searched for an url and the result inserted into the url field.': function(browser, range)
            {
                assert.equal(
                    browser.querySelector('input[name="url"]').value,
                    'http://www.heise.de'
                );
            }
        },
        'opens the import-item context-menu and the "Remove Hyphens" menu-item is clicked': {
            topic: function(err, browser)
            {
                var that = this;
                loadPage(test_url, function(err, browser, status)
                {
                    var range = openContextMenuWithRandomText(browser, 0, 400, 200);
                    var item = browser.querySelector('#content-data-menu-default li:nth-child(10)');
                    browser.fire('click', item, function() {that.callback(browser, range);});
                });
            },
            'the textarea selection should be searched for hyphens and all ocurrences removed.': function(browser, range)
            {
                assert.equal(
                    browser.querySelector('.item-content textarea').value.substr(range.start, range.end - range.start).indexOf('-'),
                    -1
                );
            }
        },
        'opens the import-item context-menu and the "Remove Linebreaks" menu-item is clicked': {
            topic: function(err, browser)
            {
                var that = this;
                loadPage(test_url, function(err, browser, status)
                {
                    var range = openContextMenuWithRandomText(browser, 0, 400, 200);
                    var item = browser.querySelector('#content-data-menu-default li:nth-child(11)');
                    browser.fire('click', item, function() {that.callback(browser, range);});
                });
            },
            'the textarea selection should be searched for linebreaks and all ocurrences removed.': function(browser, range)
            {
                assert.equal(
                    browser.querySelector('.item-content textarea').value.substr(range.start, range.end - range.start).indexOf("\n"),
                    -1
                );
            }
        },
        'opens the import-item context-menu and the "Set As Startdate" menu-item is clicked': {
            topic: function(err, browser)
            {
                var that = this;
                loadPage(test_url, function(err, browser, status)
                {
                    var range = openContextMenuWithRandomText(browser, 50, 400, 250);
                    var item = browser.querySelector('#content-data-menu-default li:nth-child(8)');
                    browser.fire('click', item, function() {that.callback(browser, range);});
                });
            },
            'the textarea selection should be searched for date information and the resutl formatted and set as date[from].': function(browser, range)
            {
                assert.equal(
                    browser.querySelector('input[name="date[from]"]').value,
                    '12.01.2011'
                );
            }
        },
        'opens the import-item context-menu and the "Set As Enddate" menu-item is clicked': {
            topic: function(err, browser)
            {
                var that = this;
                loadPage(test_url, function(err, browser, status)
                {
                    var range = openContextMenuWithRandomText(browser, 50, 400, 250);
                    var item = browser.querySelector('#content-data-menu-default li:nth-child(9)');
                    browser.fire('click', item, function() {that.callback(browser, range);});
                });
            },
            'the textarea selection should be searched for date information and the result formatted and set as date[till].': function(browser, range)
            {
                assert.equal(
                    browser.querySelector('input[name="date[till]"]').value,
                    '12.01.2011'
                );
            }
        },
        '"list" button is clicked': {
            topic: function(err, browser)
            {
                var that = this;
                loadPage(test_url, function(err, browser, status)
                {
                    var content_items_container = browser.querySelector('.slide-panel');
                    browser.fire("click", browser.querySelector('.action-list'), that.callback);
                });
            },
            'the content-item list should slide into sight.': function(browser)
            {
                var content_items_container = browser.querySelector('.slide-panel');
                //util.log(util.inspect(content_items_container.style));
            }
        }
    }
}).export(module);

})();
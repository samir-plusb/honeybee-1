/*
// Container div:
var container = $(".graph-container").first()[0];
// First data series:
var d1 = [[0, 3], [4, 8], [8, 5], [9, 13]];
// Second data series:
var d2 = [];
// A couple flotr configuration options:
var options = {
    xaxis: {
    minorTickFreq: 4
    },
    grid: {
    minorVerticalLines: true
    }
};

var graph;
// Generated second data set:
for (var i = 0; i < 14; i += 0.5)
{
    d2.push([i, Math.sin(i)]);
}

// Draw the graph:
graph = Flotr.draw(
    container,  // Container element
    [ d1, d2 ], // Array of data series
    options     // Configuration options
);
*/

var view = new midas.items.stats.StatsView($(document.body));
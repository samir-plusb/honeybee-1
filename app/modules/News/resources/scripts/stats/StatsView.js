/**
 * @class
 * @augments midas.core.BaseView
 * @description The StatsView module manages all behaviour for the system's Items/StatsuccessView.
 * @author <a href="mailto:tschmittrink@gmail.com">Thorsten Schmit-Rink</a>
 * @version $Id:$
 */
midas.items.stats.StatsView = midas.core.BaseView.extend(
/** @lends midas.items.list.StatsView.prototype */
{
    // -----------
    // --------------- PROPERTIES
    // -----------

    /**
     * The prefix to use when logging messages from this class.
     * @type String
     */
    log_prefix: "StatsView",

    tables: null,

    // -----------
    // --------------- CONSTRUCTION / GUI INITIALIZING
    // -----------

    /**
     * @description 'Magic' method called during our prototype's constructor execution.
     * @param {HTMLElement} element The view's layout root.
     * @param {Object} options An optional object containing options that are used to configure runtime behaviour.
     */
    init: function(element, options)
    {
        this.parent(element, options);
        this.tables = this.layout_root.find('.stats-list table');
        this.registerEvents();
    },

    registerEvents: function()
    {
        var that = this;

        for (var i = 0; i < this.tables.length; i++)
        {
            var table = $(this.tables[i]);

            table.find('.graph-trigger').click(function(event)
            {
                var tab = $(this).parent().parents('table');
                var district = tab.find('.col-district h3').text();
                var container = tab.next(".graph-container").first();
                if (! container[0].graph && ! container[0].isBusy)
                {
                    container.isBusy = true;
                    var trigger = $(this);
                    trigger.parent().find('img').show();
                    var url = tab.attr('data-stats-uri');
                    $.getJSON(url, function(resp)
                    {
                        trigger.parent().find('img').fadeOut();
                        container.animate({'height': '15em'}, 500, function()
                        {
                            container[0].graph = that.drawGraph(
                                container,
                                resp.data[district]
                            );
                            container[0].isBusy = false;
                            container[0].isShowing = true;
                        });
                    });
                }
                else
                {
                    if (container[0].isShowing)
                    {
                        container.animate({'height': 0}, 200);
                        container[0].isShowing = false;
                    }
                    else
                    {
                        container.animate({'height': '15em'}, 200);
                        container[0].isShowing = true;
                    }
                }
            });
        }
    },

    drawGraph: function(container, data)
    {
        var d1 = [],
        d2 = [],
        max = 10,
        cur_date = new Date(),
        options, graph, i, x, o;

        cur_date.setDate((new Date()).getDate() - (data.lastDays.length - 1));
        cur_date.setUTCHours(0, 0, 0, 0);
        for (i = data.lastDays.length - 1; i >= 0; i--) {
            x = cur_date.getTime();
            max = Math.max(max, data.lastDays[i]);
            d1.push([ x, data.lastDays[i] ]);
            d2.push([ x, 5 ]);
            cur_date.setDate(cur_date.getDate() + 1);
        }

        options = {
            xaxis: {
                mode: "time",
                labelsAngle: 45,
                noTicks: data.lastDays.length,
                showMinorLabels: true
            },
            yaxis: {
                min: 0,
                max: max,
                tickDecimals: 0,
                title: "Anzahl Items"
            },
            selection: {
                mode: "x"
            },
            grid: {
                minorVerticalLines: true
            },
            HtmlText: false,
            title: "Die letzten 20 Tage"
        };

        function drawGraph(opts)
        {
            o = Flotr._.extend(Flotr._.clone(options), opts || {});
            var days_data = {
                data: d1,
                lines: {
                    show: true,
                    fill: false,
                    color: "#08AAEF",
                    lineWidth: 3
                }
            };
            var optimal_data = {
                data: d2,
                lines: {
                    show: true,
                    fillColor: ["#46A546", "#fff"],
                    fill: true,
                    fillOpacity: 0.3,
                    color: "#46A546",
                    lineWidth: 1,
                    shadowSize: 0
                }
            };
            return Flotr.draw(container[0], [ optimal_data, days_data ], o);
        }

        graph = drawGraph();

        Flotr.EventAdapter.observe(container[0], "flotr:select", function(area)
        {
            graph = drawGraph({
                xaxis: {
                    min: area.x1,
                    max: area.x2,
                    mode: "time",
                    labelsAngle: 45
                },
                yaxis: {
                    min: 0,
                    max: Math.max(area.y2, 10),
                    tickDecimals: 0,
                    title: "Anzahl Items"
                }
            });
        });
        Flotr.EventAdapter.observe(container[0], "flotr:click", function()
        {
            graph = drawGraph();
        });

        return graph;
    }
});
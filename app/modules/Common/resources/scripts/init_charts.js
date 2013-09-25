(function(memory_chart_el, job_count_chart_el)
{
    if (!memory_chart_el || !job_count_chart_el) {
        return;
    }
    // vars that control the amount of data pulled on each queue-stats request
    var poll_limit = 500;
    var poll_offset = 0;
    // vars that hold the data that is passed to flotr2
    var spinner_memory_stats = [];
    var worker_memory_stats = {};
    var spinner_job_count_stats = [];
    var worker_job_count_stats = {};
    // vars that control starting/stopping graph updates (needed during zooming)
    var memory_graph_paused = false;
    var memory_chart_handlers;
    var job_count_graph_paused = false;
    var job_count_chart_handlers;

    var render_queue_stats = function(data)
    {
        // prepare our chart data from the given rows,
        // that have been pulled from orignating stats-file.
        $.each(data, function(idx, stats_row) {
            poll_offset++;

            var total_jobs = 0;
            // traverse workers stats and preprare chart data per worker-pid
            if (stats_row.worker_stats) {
                $.each(stats_row.worker_stats, function(pid, worker_stats) {
                    total_jobs += worker_stats.started_jobs;
                    // prepare memory(worker) chart data
                    if(!worker_memory_stats[pid]) {
                        worker_memory_stats[pid] = [];
                    }
                    worker_memory_stats[pid].push([
                        stats_row.timestamp,
                        Math.round(worker_stats.memory / (1024*1024), 2)
                    ]);
                    // prepare total-jobs(worker) chart data
                    if (!worker_job_count_stats[pid]) {
                        worker_job_count_stats[pid] = [];
                    }
                    worker_job_count_stats[pid].push([
                        stats_row.timestamp,
                        worker_stats.successful_jobs + worker_stats.failed_jobs
                    ]);
                });
            }
            // prepare memory(spinner) chart data
            spinner_memory_stats.push([
                stats_row.timestamp,
                Math.round(stats_row.memory / (1024*1024), 2)
            ]);
            // prepare total-jobs(spinner) chart data
            spinner_job_count_stats.push([stats_row.timestamp, total_jobs]);

            if (idx > 0) {
                // @todo calculate execution average based on the delta resulting from
                // the previous stat data: spinner_job_count_stats[idx - 1][1]
            }
        });
        // render job-count chart
        if (!job_count_graph_paused) {
            // setup zoom support once on first invocation
            renderJobCountChart(job_count_chart_el);
            if (!job_count_chart_handlers) {
                job_count_chart_handlers = {
                    'flotr:select': function (area) {
                        job_count_graph_paused = true;
                        // repaint graph with new area
                        graph = renderJobCountChart(job_count_chart_el, {
                            xaxis: { min: area.x1, max: area.x2 },
                            yaxis: { min:area.y1, max: area.y2 }
                        });
                    },
                    'flotr:click': function () {
                        job_count_graph_paused = false;
                        renderJobCountChart(job_count_chart_el);
                    },
                    'flotr:mousedown': function() {
                        job_count_graph_paused = true;
                    },
                    'flotr:mouseup': function() {
                        job_count_graph_paused = false;
                    }
                };
                for (event in job_count_chart_handlers) {
                    Flotr.EventAdapter.observe(job_count_chart_el, event, job_count_chart_handlers[event]);
                }
            }
        }
        // render memory chart
        if (!memory_graph_paused) {
            renderMemoryChart(memory_chart_el);
            // setup zoom support once on first invocation
            if (!memory_chart_handlers) {
                memory_chart_handlers = {
                    'flotr:select': function (area) {
                        memory_graph_paused = true;
                        // repaint graph with new area
                        graph = renderMemoryChart(memory_chart_el, {
                            xaxis: { min: area.x1, max: area.x2 },
                            yaxis: { min:area.y1, max: area.y2 }
                        });
                    },
                    'flotr:click': function () {
                        memory_graph_paused = false;
                        renderMemoryChart(memory_chart_el);
                    },
                    'flotr:mousedown': function() {
                        memory_graph_paused = true;
                    },
                    'flotr:mouseup': function() {
                        memory_graph_paused = false;
                    }
                };
                for (event in memory_chart_handlers) {
                    Flotr.EventAdapter.observe(memory_chart_el, event, memory_chart_handlers[event]);
                }
            }
        }
        // trigger next data loading and corresponding repaint after 1 second.
        setTimeout(function() {
            load_data(poll_offset, poll_limit, function(new_data)
            {
                render_queue_stats(new_data);
            });
        }, 1000);
    }

    var renderJobCountChart = function(container, options)
    {
        options = options || {};
        // prepare (flotr)chart data
        var chart_data = [];
        if (spinner_job_count_stats.length > 0) {
            chart_data.push({label: 'spinner', data: spinner_job_count_stats});
        }
        $.each(worker_job_count_stats, function(pid, stats) {
            chart_data.push({label: 'worker ' + pid, data: stats});
        });
        // prepare (flotr)chart options
        var chart_options = {
            title: 'Throughput monitor',
            subtitle : 'Renders the total number of executed jobs over time.',
            selection : { mode : 'xy', fps : 30 },
            HtmlText: false,
            legend : {
              position : 'nw',
              backgroundColor : '#D2E8FF'
            },
            xaxis: {
                title: 'Time',
                mode: 'time',
                timeFormat: '%H:%M:%S',
                timeMode: 'local',
                min: (options.xaxis && options.xaxis.min) ? options.xaxis.min : null,
                max: (options.xaxis && options.xaxis.max) ? options.xaxis.max : null
            },
            yaxis: {
                title: 'Executed Jobs',
                tickDecimals: 0,
                min: (options.yaxis && options.yaxis.min) ? options.yaxis.min : 0,
                max: (options.yaxis && options.yaxis.max) ? options.yaxis.max : null
            }
        };
        // render chart with flotr2
        Flotr.draw(container, chart_data, chart_options);
    }

    var renderMemoryChart = function(container, options)
    {
        options = options || {};
        // prepare (flotr)data
        var chart_data = [];
        if (spinner_memory_stats.length > 0) {
            chart_data.push({label: 'spinner', data: spinner_memory_stats});
        }
        $.each(worker_memory_stats, function(pid, stats) {
            chart_data.push({label: 'worker ' + pid, data: stats});
        });
        // prepare (flotr)chart options
        var chart_options = {
            title: 'Memory Monitor',
            subtitle : 'Visualizes memory usage over time.',
            selection : { mode : 'xy', fps : 30 },
            HtmlText: false,
            legend : {
              position : 'nw',            // Position the legend 'south-east'.
              backgroundColor : '#D2E8FF' // A light blue background color.
            },
            xaxis: {
                title: 'Time',
                mode: 'time',
                timeFormat: '%H:%M:%S',
                timeMode: 'local',
                min: (options.xaxis && options.xaxis.min) ? options.xaxis.min : null,
                max: (options.xaxis && options.xaxis.max) ? options.xaxis.max : null
            },
            yaxis: {
                title: 'Memory (in MB)',
                tickDecimals: 2,
                min: (options.yaxis && options.yaxis.min) ? options.yaxis.min : 0,
                max: (options.yaxis && options.yaxis.max) ? options.yaxis.max : 100
            }
        };
        // render chart with flotr2
        Flotr.draw(container, chart_data, chart_options);
    };

    var load_data = function(offset, limit, callback)
    {
        var url = $.url();
        $.getJSON(url.attr('path') + '?offset=' + offset + '&limit=' + limit, callback);
    }

    var chart_container = $('.memory-graph');
    if (chart_container.length > 0) {
        load_data(poll_offset, poll_limit, function(data)
        {
            render_queue_stats(data);
        });
    }
})($('.memory-graph')[0], $('.job-count-graph')[0]);

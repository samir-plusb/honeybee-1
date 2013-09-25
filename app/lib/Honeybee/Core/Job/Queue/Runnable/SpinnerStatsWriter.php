<?php

namespace Honeybee\Core\Job\Queue\Runnable;

use Honeybee\Core\Config\IConfig;
use Honeybee\Core\Config\ArrayConfig;

class SpinnerStatsWriter
{
    protected $config;

    public function __construct(IConfig $config)
    {
        $this->config = $config;
    }

    public function write(SpinnerStats $stats)
    {
        $this->updateStatsLog($stats);
        $this->updateStatsDisplay($stats);
    }

    protected function updateStatsLog(SpinnerStats $stats)
    {
        $data_filepath = $this->config->get('stats_log_file');
        $data = $stats->toArray();
        $data['timestamp'] = round(microtime(true) * 1000);

        file_put_contents($data_filepath, json_encode($data) . PHP_EOL, FILE_APPEND);
    }

    protected function updateStatsDisplay(SpinnerStats $stats)
    {
        $output_lines = array_merge(
            $this->prepareSpinnerStatus($stats),
            array(PHP_EOL),
            $this->prepareWorkerStatus($stats)
        );

        $status_filepath = $this->config->get('stats_display_file');
        file_put_contents($status_filepath, implode(PHP_EOL, $output_lines));
    }

    protected function prepareSpinnerStatus(SpinnerStats $stats)
    {
        $now = new \DateTime();
        $stats_array = $stats->toArray();
        $uptime = $now->diff(new \DateTime($stats_array['start_time']));

        $output_lines = array();
        $output_lines[] = '# SPINNER STATS #';
        $output_lines[] = "    Started at: " . $stats_array['start_time'];
        $output_lines[] = "        Uptime: " . $uptime->format('%d days %H hours %I minutes %s seconds');
        $output_lines[] = "        Memory: " . $stats_array['memory'];
        $output_lines[] = "Executing Jobs: " . $stats_array['executing_jobs'];
        $output_lines[] = "        Memory: " . $stats_array['memory'];

        return $output_lines;
    }

    protected function prepareWorkerStatus(SpinnerStats $stats)
    {
        $now = new \DateTime();
        $output_lines = array('# WORKER STATS #');

        foreach ($stats->getWorkerStats() as $worker_pid => $worker_stats) {
            $worker_started = $worker_stats['start_time'];
            if (isset($worker_stats['start_time']['date'])) {
                $worker_started = $worker_stats['start_time']['date'];
            }
            $uptime = $now->diff(new \DateTime($worker_started));
            $output_lines[] = "Worker [" . $worker_pid . "]";
            $output_lines[] = "     Started at: " . $worker_started;
            $output_lines[] = "         Uptime: " . $uptime->format('%d days %H hours %I minutes %s seconds');
            $output_lines[] = "         Memory: " . $worker_stats['memory'];
            $output_lines[] = "   Started Jobs: " . $worker_stats['started_jobs'];
            $output_lines[] = "Successful Jobs: " . $worker_stats['successful_jobs'];
            $output_lines[] = "    Failed Jobs: " . $worker_stats['failed_jobs'];
            $output_lines[] = "     Fatal Jobs: " . $worker_stats['fatal_jobs'];
        }

        return $output_lines;
    }
}
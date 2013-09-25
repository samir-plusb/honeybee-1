<?php

class Common_Queue_Stats_Queue_StatsSuccessView extends CommonBaseView
{
    public function executeHtml(\AgaviRequestDataHolder $request_data)
    {
        $this->setAttribute('_title', 'Queue Monitoring');
        $this->setupHtml($request_data);
        $this->setBreadcrumb();
    }

    public function executeJson(\AgaviRequestDataHolder $request_data)
    {
        $limit = $request_data->getParameter('limit', 10);
        $offset = $request_data->getParameter('offset', 0);

        $stats_file = realpath(AgaviConfig::get('core.app_dir') . '/../queue_stats.log.json');
        $stats = array();

        if (is_readable($stats_file)) {
            $lines = file($stats_file);
            foreach ($lines as $cur_offset => $line) {
                $line = trim($line);
                if (empty($line) || $cur_offset > $offset + $limit) {
                    break;
                }
                if ($cur_offset >= $offset) {
                    $stats[] = json_decode($line, true);
                }
            }
        }

        return json_encode($stats);
    }

    protected function setBreadcrumb()
    {
        $breadcrumbs = array(
            array(
                'text' => 'Information on the current status of the honeybee jobqueue spinner.',
                'info' => '',
                'icon' => 'hb-icon-chart'
            )
        );

        $this->getContext()->getUser()->setAttribute('modulecrumb', null, 'honeybee.breadcrumbs');
        $this->getContext()->getUser()->setAttribute('breadcrumbs', $breadcrumbs, 'honeybee.breadcrumbs');
    }
}

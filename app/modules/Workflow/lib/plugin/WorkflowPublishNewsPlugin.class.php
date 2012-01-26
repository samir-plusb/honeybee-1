<?php

/**
 * This plugin takes care of publishing news to the various subscribers.
 *
 * @package Workflow
 * @author tay
 * @version $Id$
 *
 */
class WorkflowPublishNewsPlugin extends WorkflowBasePlugin
{
    const PLUGIN_ID = 'publish_news';

    const GATE_ARCHIV = 'archiv';

    public function getPluginId()
    {
        return self::PLUGIN_ID;
    }

    /**
     * (non-PHPdoc)
     * @see WorkflowBasePlugin::process()
     */
    protected function doProcess()
    {
        $now = new DateTime();
        $workflowItem = $this->ticket->getWorkflowItem();
        foreach ($workflowItem->getContentItems() as $contentItem)
        {
            $contentItem->applyValues(array(
                'publishDate' => $now->format(DATE_ISO8601)
            ));
        }
        $supervisor = Workflow_SupervisorModel::getInstance();
        $supervisor->getItemPeer()->storeItem($workflowItem);
        $result = new WorkflowPluginResult();

        if (TRUE === AgaviConfig::get('news_workflow.sync2fe', FALSE))
        {
            $curl = ProjectCurl::create();
            curl_setopt($curl, CURLOPT_URL, 'http://bo-proto.h1960801.stratoserver.net/news/api/import');
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($workflowItem->toArray()));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=UTF-8',
                'Accept: application/json; charset=UTF-8',
                'X-Requested-With: XMLHttpRequest'
            ));
            curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
            $resp = curl_exec($curl);
            if (($err = curl_error($curl)))
            {
                $result->setState(WorkflowPluginResult::STATE_ERROR);
                $result->setMessage('An error occured while publishing item: Result:' . $result . ', Error: ' . $err);
            }
            else
            {
                $result->setState(WorkflowPluginResult::STATE_EXPECT_INPUT);
                $result->setMessage('Successfully published item to frontend.');
            }
        }
        $result->freeze();
        return $result;
    }

    /**
     * Returns whether the plugin is executable at the current app/session state.
     *
     * @return boolean
     */
    protected function mayProcess()
    {
        return TRUE;
    }
}

?>

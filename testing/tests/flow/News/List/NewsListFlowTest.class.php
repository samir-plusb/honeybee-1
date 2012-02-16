<?php

/**
 * @agaviRoutingInput de/items/list
 * @agaviRequestMethod Read
 * @agaviIsolationDefaultContext web
 */
class NewsListFlowTest extends AgaviFlowTestCase
{
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        $_SERVER['argv'] = array($this->getDispatchScriptName(), $this->getRoutingInput());
        parent::__construct($name, $data, $dataName);
    }

    public function testListWithoutParams()
    {
        $this->dispatch();
        $matcher = array(
            'tag'        => 'form' // the login form, gotta find a solution for this :(
        );

        $this->assertResponseHasTag($matcher, 'Missing data table on page.');
    }
}

?>

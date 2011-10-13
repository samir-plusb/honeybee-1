<?php
/**
 * tests the class ProjectEventProxy
 *
 * @package Testing
 * @subpackage Models
 * @author shrink0r
 * @version $Id$
 *
 */
class ProjectEventProxyTest extends AgaviPhpUnitTestCase
{
    const EVENT_TEST_PROXY = 'midas.testing.events.test_proxy';

    private $eventProxy;

    private $eventReceived;

    private $eventCount;

    protected function setUp()
    {
        parent::setUp();

        $this->eventProxy = ProjectEventProxy::getInstance();
        $this->eventReceived = FALSE;
        $this->eventCount = 0;
    }

    public function testSubscribe()
    {
        $this->eventProxy->subscribe(
            self::EVENT_TEST_PROXY,
            array($this, 'eventCallbackMock')
        );

        $this->eventProxy->publish(
            new ProjectEvent(self::EVENT_TEST_PROXY)
        );

        $this->eventProxy->publish(
            new ProjectEvent(self::EVENT_TEST_PROXY)
        );

        $this->assertEquals(TRUE, $this->eventReceived);
        $this->assertEquals(2, $this->eventCount);
    }

    public function testUnsubscribe()
    {
        $this->eventProxy->subscribe(
            self::EVENT_TEST_PROXY,
            array($this, 'eventCallbackMock')
        );

        $this->eventProxy->publish(
            new ProjectEvent(self::EVENT_TEST_PROXY)
        );

        $this->eventProxy->unsubscribe(
            self::EVENT_TEST_PROXY,
            array($this, 'eventCallbackMock')
        );

        $this->eventProxy->publish(
            new ProjectEvent(self::EVENT_TEST_PROXY)
        );

        $this->assertEquals(TRUE, $this->eventReceived);
        $this->assertEquals(1, $this->eventCount);
    }

    public function eventCallbackMock(IEvent $event)
    {
        $this->eventReceived = TRUE;
        $this->eventCount++;
    }
}

?>
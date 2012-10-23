<?php

class ShofiDataImportReport
{
    const DEBUG = 1;

    const TRACE = 2;

    const INFO = 4;

    const WARN = 8;

    const ERROR = 16;

    const FATAL = 32;

    protected $incidents = array();

    protected $createCounter = 0;

    protected $updateCounter = 0;

    protected $matchCounter = 0;

    protected $newSourceCategories = array();

    protected $unmappedSourceCategories = array();

    protected $enableLogging;

    protected $logger;

    public function __construct($enableLogging = FALSE)
    {
        $this->enableLogging = $enableLogging;
        $this->logger = AgaviContext::getInstance()->getLoggerManager()->getLogger('debug');
    }

    public function onItemUpdated(ShofiWorkflowItem $shofiItem)
    {
        $this->updateCounter++;
        $this->addIncident(
            sprintf("Updated the (shofi) workflow item with id: %s and name: %s",
                $shofiItem->getIdentifier(), 
                $shofiItem->getCoreItem()->getName()
            ),
            self::INFO
        );
    }

    public function onItemCreated(ShofiWorkflowItem $shofiItem)
    {
        $this->createCounter++;
        $this->addIncident(
            sprintf("Created a new (shofi) workflow item with id: %s and name: %s",
                $shofiItem->getIdentifier(), 
                $shofiItem->getCoreItem()->getName()
            ),
            self::INFO
        );
    }

    public function onItemMatched(ShofiWorkflowItem $shofiItem, ShofiWorkflowItem $matchedItem, $distance)
    {
        $this->matchCounter++;
        $this->addIncident(
            sprintf(
                "An incoming place called '%s' matched against an existing item '%s', id: %s, with a distance of %s meters.",
                $shofiItem->getCoreItem()->getName(),
                $matchedItem->getCoreItem()->getName(),
                $matchedItem->getIdentifier(),
                $distance
            ),
            self::INFO
        );
    }

    public function onSourceCategoryRegistered($sourceCategory)
    {
        $this->newSourceCategories[] = $sourceCategory;
        $this->addIncident(
            "Registered a new source categery named: " . $sourceCategory,
            self::INFO
        );
    }

    public function onSourceCategoryUnmapped($sourceCategory)
    {
        $this->unmappedSourceCategories[] = $sourceCategory;
        $this->addIncident(
            "Encountered a source-categery that has not been edited: " . $sourceCategory,
            self::WARN
        );
    }

    public function addIncident($message, $type)
    {
        $this->checkTypeArgument($type);
        if (! isset($this->incidents[$type]))
        {
            $this->incidents[$type] = array();
        }
        $this->incidents[$type][] = array(
            'timestamp' => microtime(),
            'type' => $type,
            'message' => $message
        );
        echo $message . PHP_EOL;
        $this->logger->log(new AgaviLoggerMessage($message), AgaviLogger::DEBUG);
    }

    public function getItemsCreatedCount()
    {
        return $this->createCounter;
    }

    public function getItemsUpdatedCount()
    {
        return $this->updateCounter;
    }

    public function getItemsMatchedCount()
    {
        return $this->matchCounter;
    }

    public function getNewSourceCategories()
    {
        return $this->newSourceCategories;
    }

    public function getIncidents($type)
    {
        $this->checkTypeArgument($type);
        return isset($this->incidents[$type]) ? $this->incidents[$type] : array();
    }

    protected function checkTypeArgument($type)
    {
        static $supportedIncidents = array(
            self::DEBUG, self::TRACE, self::INFO, self::WARN, self::ERROR, self::FATAL
        );
        if (! in_array($type, $supportedIncidents))
        {
            throw new InvalidArgumentException("Invalid incident type given: " . print_r($type, TRUE));
        }
    }
}

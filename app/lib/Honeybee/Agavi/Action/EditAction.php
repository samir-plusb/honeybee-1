<?php

namespace Honeybee\Agavi\Action;

use Dat0r\Core\Runtime\Document;
use Dat0r\Core\Runtime\Error;
use Honeybee\Core\Workflow\Plugin;

class EditAction extends BaseAction
{
    public function executeRead(\AgaviRequestDataHolder $requestData)
    {
        $module = $this->getModule();
        $document = $requestData->getParameter('document', NULL);

        if (! $document)
        {
            $document = $module->createDocument();
        }
        $this->setAttribute('module', $module);
        $this->setAttribute('document', $document);

        $this->setContainerPluginState(Plugin\Result::STATE_EXPECT_INPUT);

        return 'Input';
    }

    public function executeWrite(\AgaviRequestDataHolder $requestData)
    {
        $view = 'Success';

        $module = $this->getModule();
        $this->setAttribute('module', $module);

        try
        {
            $module->getService()->save(
                $requestData->getParameter('document')
            );
        }
        catch(Document\InvalidValueException $error)
        {
            $this->setAttribute('errors', array($error->__toString()));
            $view = 'Error';
        }
        catch(Document\MandatoryValueMissingException $error)
        {
            $translationManager = $this->getContext()->getTranslationManager();
            $fieldName = $translationManager->_(
                $error->getFieldName(),
                $this->getModule()->getOption('prefix') . '.list'
            );

            $errorMsg = $translationManager->_(
                "Missing value for field '%s'.",
                $this->getModule()->getOption('prefix') . '.errors',
                null,
                array($fieldName)
            );
            $this->setAttribute('errors', array($errorMsg));
            $view = 'Error';
        }
        catch(Error\BadValueException $error)
        {
            $this->setAttribute('errors', array($error->__toString()));
            $view = 'Error';
        }
        catch(\Exception $error)
        {
            $this->setAttribute('errors', array($error->getMessage()));
            $view = 'Error';
        }

        if ($view === 'Success')
        {
            $this->setContainerPluginState(Plugin\Result::STATE_EXPECT_INPUT);
        }
        else
        {
            $this->setContainerPluginState(Plugin\Result::STATE_ERROR);
        }

        return $view;
    }

    public function getCredentials()
    {
        return sprintf(
            '%s::%s',
            $this->getModule()->getOption('prefix'),
            $this->getContainer()->getRequestMethod()
        );
    }

    public function handleWriteError(\AgaviRequestDataHolder $parameters)
    {
        $errors = array();

        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $errMsg)
        {
            $errors[] = $errMsg['message'];
        }

        $this->setContainerPluginState(Plugin\Result::STATE_ERROR);

        $this->setAttribute('errors', $errors);

        return 'Error';
    }

    protected function setContainerPluginState($state, $message = '')
    {
        $pluginResult = $this->getContainer()->getAttribute(
            Plugin\InteractivePlugin::ATTR_RESULT,
            Plugin\InteractivePlugin::NS_PLUGIN_ATTRIBUTES
        );

        if ($pluginResult)
        {
            $pluginResult->setState($state);
            if (!empty($message))
            {
                $pluginResult->setMessage($message);
            }
        }
    }
}

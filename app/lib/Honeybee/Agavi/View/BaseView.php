<?php

namespace Honeybee\Agavi\View;

/**
 * Base view for all the application's views.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class BaseView extends \AgaviView
{
    /**
     * Name of the default layout to use for slots.
     */
    const DEFAULT_SLOT_LAYOUT_NAME = 'slot';

    /**
     * Holds a reference to the current routing object. May be a more concrete
     * instance like an \AgaviConsoleRouting or \AgaviWebRouting.
     *
     * @var \AgaviRouting
     */
    protected $routing;

    /**
     * Holds a reference to the current request object. May be a more concrete
     * instance like an \AgaviConsoleRequest or \AgaviWebRequest.
     *
     * @var \AgaviRequest
     */
    protected $request;

    /**
     * Holds a reference to the translation manager.
     *
     * @var \AgaviTranslationManager
     */
    protected $translation_manager;

    /**
     * Holds a reference to the user for the current session.
     *
     * @var \AgaviUser
     */
    protected $user;

    /**
     * Holds a reference to the current agavi controller.
     *
     * @var \AgaviController
     */
    protected $controller;

    /**
     * Initialize the view and set default member variables available in all
     * views.
     *
     * @param \AgaviExecutionContainer $container
     */
    public function initialize(\AgaviExecutionContainer $container)
    {
        parent::initialize($container);

        $this->controller = $this->getContext()->getController();
        $this->routing = $this->getContext()->getRouting();
        $this->request = $this->getContext()->getRequest();
        $this->translation_manager = $this->getContext()->getTranslationManager();
        $this->user = $this->getContext()->getUser();
    }

    /**
     * @todo use twig or introduce different escaping strategies for html/js/html_attributes/css etc.
     *
     * @return string htmlspecialchars encoded string
     */
    public function escape($string)
    {
        if (!defined('ENT_SUBSTITUTE'))
        {
            define('ENT_SUBSTITUTE', 8);
        }

        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * @return boolean whether the current execution container is a slot or not.
     */
    public function isSlot()
    {
        return $this->getContainer()->getParameter('is_slot', false);
    }

    /**
     * Sets the given data on the given form. This replaces all data that may
     * exist in the request for that form.
     *
     * @see http://mivesto.de/agavi/agavi-faq.html#validation_8 for more info.
     *
     * @param string $form_id id of the html form that should be populated
     * @param array $data data to set for the given form (html input element names and values)
     *
     * @return void
     */
    protected function populateForm($form_id, $data)
    {
        $populate = $this->request->getAttribute('populate', 'org.agavi.filter.FormPopulationFilter', array());
        $populate[$form_id] = new AgaviParameterHolder($data);
        $this->request->setAttribute('populate', $populate, 'org.agavi.filter.FormPopulationFilter');
    }

    /**
     * Adds the validation report to the current request to retain error messages
     * when forwarding (like <code>return $this->createForwardContainer('Foo', 'Bar');</code>).
     *
     * Background: The \AgaviFormPopulationFilter gets the validation report
     * from the \AgaviExecutionContainer of the initial request (the main agavi
     * action called) and uses that report to fill forms with data taken from
     * the request (form fields and values).
     *
     * If your forms are not populated automatically, check that you have an
     * id and an action attribute on the form element and call this method just
     * before forwarding internally to another agavi action.
     *
     * @return void
     */
    public function addValidationReportToRequestForFpf()
    {
        $this->request()->setAttribute('validation_report', $this->getContainer()->getValidationManager()->getReport(), 'org.agavi.filter.FormPopulationFilter');
    }

    /**
     * Return any reported validation error messages from the validation manager.
     *
     * @return array
     */
    protected function getErrorMessages()
    {
        $errors = array();

        foreach ($this->getContainer()->getValidationManager()->getErrorMessages() as $errMsg)
        {
            $errors[] = $errMsg['message'];
        }

        foreach ($this->getAttribute('errors', array()) as $error)
        {
            $errors[] = $error;
        }

        return $errors;
    }

    /**
     * Convenience method to configure the layout and some defaults like a page
     * title when using the html output type.
     *
     * @param \AgaviRequestDataHolder $request_data
     * @param string $layout_name layout name from output_types.xml file
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function setupHtml(\AgaviRequestDataHolder $request_data, $layout_name = null) // @codingStandardsIgnoreEnd
    {
        if ($layout_name === null && $this->getContainer()->getParameter('is_slot', false))
        {
            $layout_name = self::DEFAULT_SLOT_LAYOUT_NAME;
        }
        else
        {
            if (!$this->hasAttribute('_title'))
            {
                $this->setAttribute('_title', 'Honeybee');
            }
        }

        $this->loadLayout($layout_name);
    }

    /**
     * Handles non-existing methods. This includes mainly the not implemented
     * handling of certain output types.
     *
     * @param string $method_name
     * @param array $arguments
     *
     * @throws \AgaviViewException with different messages
     */
    public function __call($method_name, $arguments)
    {
        if (preg_match('~^(execute|setup)([A-Za-z_]+)$~', $method_name, $matches))
        {
            $this->throwOutputTypeNotImplementedException();
        }

        throw new \AgaviViewException(
            sprintf(
                'The view "%1$s" does not implement an "%2$s()" method. Please ' .
                'implement "%1$s::%2$s()" or handle this situation in one of the base views (e.g. "%3$s").',
                get_class($this),
                $method_name,
                get_class()
            )
        );
    }

    /**
     * Convenience method for throwing an exception that notifies the caller
     * about the not implemented output type handling method for this view.
     * This method is called via __call() overrider in this class.
     *
     * @throws \AgaviViewException
     */
    protected function throwOutputTypeNotImplementedException()
    {
        throw new \AgaviViewException(
            sprintf(
                'The view "%1$s" does not implement an "execute%3$s()" method to serve the ' .
                'output type "%2$s". Please implement "%1$s::execute%3$s()" or handle this ' .
                'situation in one of the base views. Handling in a module\'s or application\'s base ' .
                'view may include throwing 40x errors or displaying further explanations about ' .
                'how to react as a user or developer in that case.',
                get_class($this),
                $this->container->getOutputType()->getName(),
                ucfirst(strtolower($this->container->getOutputType()->getName())),
                get_class()
            )
        );
    }

    /**
     * If developers try to use the execute method in views instead of creating
     * an output type specific handler they will get a fatal error. If they call
     * this method directly we try to help them with an exception.
     *
     * @param \AgaviRequestDataHolder $request_data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public final function execute(\AgaviRequestDataHolder $request_data) // @codingStandardsIgnoreEnd
    {
        throw new \AgaviViewException(
            sprintf(
                'There should be no "execute()" method in "%1$s". Views deal ' .
                'with output types and should therefore implement specific ' .
                '"execute<OutputTypeName>()" methods. It is recommended that ' .
                'you either implement "execute%3$s()" for the current output type ' .
                '"%2$s" and all other supported output types in each of your views ' .
                'or implement more general fallbacks in the module\'s or applications\'s base views (e.g. "%4$s").',
                get_class($this),
                $this->container->getOutputType()->getName(),
                ucfirst(strtolower($this->container->getOutputType()->getName())),
                get_class()
            )
        );
    }
}

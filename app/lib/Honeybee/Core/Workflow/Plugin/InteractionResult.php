<?php

namespace Honeybee\Core\Workflow\Plugin;

/**
 * The InteractionResult class extends the Workflow\Plugin\Result to add in a response member,
 * that holds the reponse returned by actions that are run spefically in the context of InteractivePlugin execution.
 *
 * @author tay
 */
class InteractionResult extends Result
{
    /**
     * Holds the response that was returned by the action run by the plugin.
     *
     * @var AgaviResponse
     */
    private $response;

    /**
     * Returns the response that was returned by the action run by the plugin.
     *
     * @return AgaviResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Sets the plugin results action response.
     *
     * @param AgaviResponse $response
     */
    public function setResponse(\AgaviResponse $response)
    {
        $this->verifyMutability();
        $this->response = $response;
    }

    /**
     * Return a list of properties that shall be exlcuded by our toArray and fromArray methods.
     *
     * @return array
     */
    protected function getPropertyBlacklist()
    {
        return array_merge(
            parent::getPropertyBlacklist(),
            array('response')
        );
    }
}

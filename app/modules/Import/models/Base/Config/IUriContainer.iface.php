<?php

interface IUriContainer
{
    /**
     * @param string $configUri
     */
    public function __construct($configUri);
    
    /**
     * @return array
     */
    public function getUriParts();
    
    /**
     * @return string
     */
    public function getUri();
}

?>

<?php
 
### Hack for GData with GoDaddy
### June 15, 2007
### http://groups.google.com/group/google-calendar-help-dataapi/browse_thread/thread/8f13fb00cb518535/6edd8ca8aa4a7ca8?#6edd8ca8aa4a7ca8
 
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Http
 * @subpackage Client_Adapter
 * @version    $Id: ProxyWithCurl.php 4599 2012-01-20 10:23:15Z tay $
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
 
require_once 'Zend/Uri/Http.php';
require_once 'Zend/Http/Client.php';
require_once 'Zend/Http/Client/Adapter/Socket.php';
require_once 'Zend/Http/Client/Adapter/Exception.php';
 
/**
 * HTTP Proxy-supporting Zend_Http_Client adapter class, based on the default
 * socket based adapter.
 *
 * Should be used if proxy HTTP access is required. If no proxy is set, will
 * fall back to Zend_Http_Client_Adapter_Socket behavior. Just like the
 * default Socket adapter, this adapter does not require any special extensions
 * installed.
 *
 * @category   Zend
 * @package    Zend_Http
 * @subpackage Client_Adapter
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Http_Client_Adapter_ProxyWithCurl extends Zend_Http_Client_Adapter_Socket
{   
    protected $_curl = null;  
    /**
     * Parameters array
     *
     * @var array
     */
    protected $config = array(
        'ssltransport' => 'ssl',
        'proxy_host'   => '',
        'proxy_port'   => 8080,
        'proxy_user'   => '',
        'proxy_pass'   => '',
        'proxy_auth'   => Zend_Http_Client::AUTH_BASIC
    );
 
    /**
     * Set the configuration array for the adapter
     *
     * @param Zend_Config | array $config
     */
    public function setConfig($config = array())
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();

        } elseif (! is_array($config)) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception(
                'Array or Zend_Config object expected, got ' . gettype($config)
            );
        }

        foreach ($config as $k => $v) {
            $this->config[strtolower($k)] = $v;
        }
    }
    
    /**
     * Connect to the remote server
     *
     * Will try to connect to the proxy server. If no proxy was set, will
     * fall back to the target server (behave like regular Socket adapter)
     *
     * @param string  $host
     * @param int     $port
     * @param boolean $secure
     * @param int     $timeout
     */
 
    public function connect($host, $port = 80, $secure = false) 
    { 
        // If no proxy is set, fall back to Socket adapter 
        if (! $this->config['proxy_host']) { 
            return parent::connect($host, $port, $secure); 
        } 
    } 
 
    /**
     * Send request to the proxy server
     *
     * @param string        $method
     * @param Zend_Uri_Http $uri
     * @param string        $http_ver
     * @param array         $headers
     * @param string        $body
     * @return string Request as string
     */
 
    public function write($method, $uri, $http_ver = '1.1', $headers = array(), $body = '') 
    { 
        // If no proxy is set, fall back to default Socket adapter 
        if (! isset($this->config['proxy_host'])) { 
            return parent::write($method, $uri, $http_ver, $headers, $body); 
        } 
 
        $host = $this->config['proxy_host']; 
        $port = $this->config['proxy_port']; 
 
        $this->_curl = curl_init(); 
        curl_setopt($this->_curl, CURLOPT_VERBOSE, 0); 
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($this->_curl, CURLOPT_HEADER, true); 
        curl_setopt($this->_curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); 
        curl_setopt($this->_curl, CURLOPT_PROXY,"http://" . $host . ':' . $port); 
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($this->_curl, CURLOPT_URL, $uri->__toString()); 
        curl_setopt($this->_curl, CURLOPT_TIMEOUT, 120); 
        
        // Save request method for later 
        $this->method = $method; 
 
        curl_setopt($this->_curl, CURLOPT_CUSTOMREQUEST, $method); 
 
        // Add Proxy-Authorization header 
        if ($this->config['proxy_user'] && ! isset($headers['proxy-authorization'])) { 
             $headers['proxy-authorization'] = Zend_Http_Client::encodeAuthHeader( 
                  $this->config['proxy_user'], $this->config['proxy_pass'], $this->config['proxy_auth'] 
             ); 
        } 
 
        $curlHeaders = array(); 
 
        // Add all headers to the curl header array 
        foreach ($headers as $k => $v) { 
            if (is_string($k)) $v = ucfirst($k) . ": $v"; 
            $curlHeaders[] = $v; 
        } 
        curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $curlHeaders); 
        
        if ($body != null) { 
            curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $body); 
        } 
 
        $this->result = curl_exec($this->_curl); 
        // HACK- The server returns chunked transfer encoding at times, and it's indicated in the header 
        // However, curl and ZF both attempt to decode, leading to exceptions being thrown 
        // There has to be a way to avoid the following line (or something like it), but haven't figured it out yet 
        $this->result = str_ireplace('Transfer-encoding:', 'Transfer-encoding-old:', $this->result);
                
        return $this->result; 
    } 
 
    public function read() 
    { 
        return $this->result; 
    } 
 
    /**
     * Destructor: make sure the socket is disconnected
     *
     */
    public function __destruct()
    {
        if ($this->socket) $this->close();
    } 
}
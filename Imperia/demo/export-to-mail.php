<?php
/**
 *
 * Demo responder to export_to_contentworker.pl
 *
 * <ul>
 * <li> Expects POST requests with parameter data containing a JSON array
 * <li> each array item contains info about imperia documents
 * <li> get the imperia documents from imperia in XML format
 * <li> parse the XML an extract title, heading and text information
 * <li> send mail to ContentWorker prototype
 * </ul>
 *
 * @author tay
 * @version $Id$
 *
 */
class DemoImperiaExportResponder
{
    /**
     * internal used CURL handle
     */
    protected $curlHandle;
    /**
     * @var array config file a assoziative array
     */
    protected $config;
    /**
     * @var DOMDocument parsed current imperia document
     */
    protected $dom;
    /**
     * @var DOMXPath XPATH handle of current imperia document
     */
    protected $xpath;
    /**
     * @var string template for a very simple html output
     */
    private $template = '<html>
	<head>
		<meta http-equiv="Content-Type" value="text/html; charset=utf8">
		<title>%1$s</title>
	</head>
	<body>
		<h3>%2$s</h3>
		<h1>%1$s</h1>
		<h2>%3$s</h2>
		%4$s
	</body>
</html>';

    /**
     * output a JSON formated error response and die
     *
     * @param string $message error message
     */
    protected function errorResponse ($message)
    {
        error_log(__FILE__.": $message");
        header('Content-Type', 'application/json');
        $resp = array('Error', $message);
        echo json_encode($resp);
        exit;
    }

    /**
     * Constuct a instance and read the config ini file
     *
     */
    public function __construct()
    {
        $config = parse_ini_file(dirname(__FILE__).'/config.denied.ini');
        foreach (array('mailto', 'user', 'password', 'export_url', 'login_url') as $key)
        {
            if (empty($config[$key]))
            {
                die("Config key not defined: $key\n");
            }
        }
        $this->config = $config;
    }


    /**
     *
     * initialize internal used CURL handle
     */
    protected function initCurlHandle()
    {
        if (! isset($this->curlHandle))
        {
            $cookieFile = '/var/tmp/cookie.' . md5(__FILE__) . '.txt';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, 0);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

            $this->curlHandle = $ch;
        }
    }

    /**
     * get imperia document as XML
     *
     * uses config keys: user, password for transparent imperia login
     *
     * @param string $url
     * @return string repsonse from curl_exec
     */
    protected function getDocument($url)
    {
        curl_setopt($this->curlHandle, CURLOPT_URL, $url);
        curl_setopt($this->curlHandle, CURLOPT_HTTPGET, 1);
        $resp = curl_exec($this->curlHandle);
        if (200 != curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE))
        {
            $this->errorResponse('Can not load data: '.$url);
        }

        if (FALSE !== strpos($resp, '<title>Access Denied!</title>'))
        {
            $post = array
            (
                'my_imperia_login' => $this->config['user'],
                'my_imperia_pass' => $this->config['password'],
                'Target' => $url
            );
            curl_setopt($this->curlHandle, CURLOPT_POST, 1);
            curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, http_build_query($post));
            curl_setopt($this->curlHandle, CURLOPT_URL, $this->config['login_url']);
            $resp = curl_exec($this->curlHandle);
            if (200 != curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE) || FALSE !== strpos($resp, '<title>Access Denied!</title>'))
            {
                $this->errorResponse('Can not login to imperia');
            }
        }
        return $resp;
    }


    /**
     * parse XML string to internal used member variables
     *
     *  @see $dom
     *  @see $xpath
     *
     * @param string $response
     */
    protected function parseDocument($response)
    {
        libxml_clear_errors();
        $this->dom = new DOMDocument();
        if (! $this->dom->loadXML($response))
        {
            $errors = libxml_get_errors();
            $msg = array();
            foreach ($errors as $error)
            {
                $msg[] = sprintf('%d (%d,%d) %s', $error->code, $error->line, $error->column, $error->message);
            }
            $this->errorResponse('Xml parse errors: '.join(', ', $msg));
        }
        $this->xpath = new DOMXPath($this->dom);
        $nl = $this->xpath->query('/imperia/body/article');
        if ($nl->length != 1)
        {
            $this->errorResponse('Document structure is broken');
        }
    }

    /**
     * perform a simple xpath query expectecting exact one match
     *
     * @param string $xpath query string
     * @param DOMNode $contextNode optional context node used by xpath query
     * @return mixed string or FALSE on zero or multiple matches
     */
    protected function simpleXpath($xpath, DOMNode $contextNode = NULL)
    {
        if ($contextNode == NULL)
        {
            $contextNode = $this->dom;
        }
        $nl = $this->xpath->query($xpath, $contextNode);
        if (! $nl instanceof DOMNodeList || $nl->length != 1)
        {
            return FALSE;
        }
        return $nl->item(0)->nodeValue;
    }


    /**
     * extract core info from previous parsed document
     *
     * @see parseDocument()
     * @return array assoziative keys: title,subtitle, kicker, text
     */
    protected function evaluateDocument()
    {
        $title = $this->simpleXpath('//article/title');
        $kicker = $this->simpleXpath('//article/kicker');
        $subtitle = $this->simpleXpath('//article/subtitle');

        $nl = $this->xpath->query('//article//paragraph/text');
        for ($i = 0; $i < $nl->length; $i ++)
        {
            $paragraphs[] = $nl->item($i)->nodeValue;
        }

        $doc = array
        (
            'title' => $title,
            'subtitle' => $subtitle,
            'kicker' => $kicker,
            'text' => $paragraphs
        );
        return $doc;
    }

    /**
     * quote string for use in mail headers
     *
     * @param string $str utf8 string
     * @return string quoted output
     */
    protected function quoteMailHeader($str)
    {
        return '=?utf-8?q?' .
            preg_replace_callback(
                '/[\x00-\x1f\x80-\xff]/',
                create_function('$m','return sprintf(\'=%02X\',ord($m[0]));'),
                $str)
            . '?=';
    }

    /**
     * send email to ContentWorker prototype
     *
     * @see evaluateDocument()
     * @param array $info array generated by evaluateDocument()
     */
    protected function sendMail(array $info)
    {
        $text = "<p>\n" . join("\n</p>\n<p>\n", $info['text']) . "\n</p>\n";
        $body = sprintf($this->template,
            $info['title'],
            @$info['kicker'],
            @$info['subtitle'],
            $text);

        $headers = array
        (
            'From: bo@service.berlinonline.de',
            'CC: tom.anheyer@berlinonline.de',
            'Content-Type: Text/Plain; charset="utf-8"',
            'Content-Transfer-Encoding: base64'
        );

        mail(
            $this->config['mailto'],
            $this->quoteMailHeader($info['title']),
            base64_encode(strip_tags($body)),
            join("\n", $headers));
    }


    /**
     *
     * dispatch HTTP Request
     */
    public function dispatchRequest()
    {
        $data = json_decode(@$_POST['data'], true);
        if (! is_array($data))
        {
            $this->errorResponse('POST request with json formated data parameter expected');
        }

        $this->initCurlHandle();
        foreach ($data as $item)
        {
            if (empty($item['__imperia_node_id']))
            {
                errorResponse('each item must contain a key: __imperia_node_id');
            }
            $url = sprintf($this->config['export_url'], urlencode($item['__imperia_node_id']));
            $resp = $this->getDocument($url);

            $this->parseDocument($resp);
            $info = $this->evaluateDocument();
            if (! empty($info['title']) && ! empty($info['text']))
            {
                $this->sendMail($info);
            }
        }
    }
}

$responder = new DemoImperiaExportResponder();
$responder->dispatchRequest();

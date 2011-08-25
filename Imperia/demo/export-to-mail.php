<?php
/**
 *
 * Demo responder to export_to_contentworker.pl
 *
 *
 *
 * @author tay
 * @version $Id$
 *
 */
class DemoImperiaExportResponder
{
    /**
     *
     * Enter description here ...
     * @var unknown_type
     */
    protected $curlHandle;
    /**
     *
     * Enter description here ...
     * @var array
     */
    protected $config;
    /**
     *
     * Enter description here ...
     * @var DOMDocument
     */
    protected $dom;
    /**
     *
     * Enter description here ...
     * @var DOMXPath
     */
    protected $xpath;


    private $template = '<html>
	<head>
		<meta http-equiv="Content-Type" value="text/html; charset=utf8">
		<title>%$1s</title>
	</head>
	<body>
		<h3>%$2s</h3>
		<h1>%$1s</h1>
		<h2>%$3s</h2>
		%$3s
	</body>
</html>';

    /**
     *
     * Enter description here ...
     * @param unknown_type $messge
     */
    protected function errorResponse ($messge)
    {
        header('Content-Type', 'application/json');
        $resp = array('Error', $messge);
        echo json_encode($resp);
        exit;
    }

    /**
     *
     * Enter description here ...
     */
    public function __construct()
    {
        $config = parse_ini_file(dirname(__FILE__).'/config.denied.ini');
        foreach (array('mailto', 'user', 'password', 'export_url') as $key)
        {
            if (empty($config[$key]))
            {
                die("Config key not defined: $key\n");
            }
        }
    }


    /**
     *
     * Enter description here ...
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
     *
     * Enter description here ...
     * @param unknown_type $url
     */
    protected function getDocument($url)
    {
        curl_setopt($this->curlHandle, CURLOPT_URL, $url);
        curl_setopt($this->curlHandle, CURLOPT_HTTPGET, 1);
        $resp = curl_exec($this->curlHandle);
        if (200 != curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE))
        {
            $this->errorResponse('Can not load data from imperia for: '.$item['__imperia_node_id']);
        }

        if (FALSE !== strpos($resp, '<title>Access Denied!</title>'))
        {
            $post = array
            (
                'my_imperia_login' => $config['user'],
                'my_imperia_pass' => $config['password'],
                'Target' => $url
            );
            curl_setopt($this->curlHandle, CURLOPT_POST, 1);
            curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $post);
            $resp = curl_exec($this->curlHandle);
            if (200 != curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE) || FALSE !== strpos($resp, '<title>Access Denied!</title>'))
            {
                $this->errorResponse('Can not login to imperia');
            }
        }
        return $resp;
    }


    /**
     *
     * Enter description here ...
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
     *
     * Enter description here ...
     * @param unknown_type $xpath
     * @param DOMNode $contextNode
     */
    protected function simpleXpath($xpath, DOMNode $contextNode = NULL)
    {
        $nl = $this->xpath->query($xpath, $contextNode);
        if (! $nl instanceof DOMNodeList || $nl->length != 1)
        {
            return FALSE;
        }
        return $nl->item(0)->nodeValue;
    }


    /**
     *
     * Enter description here ...
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
     *
     * Enter description here ...
     * @param unknown_type $str
     */
    protected function quoteMailHeader($str)
    {
        return '=?utf-8?q?' .
            preg_replace_callback(
                '/[\x80-\xff]/',
                create_function('$m','return sprintf(\'=%02X\',$m[0]);'),
                $str)
            . '?=';
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $info
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
            'Content-Type: Text/Plain; charset="utf-8"',
            'Content-Transfer-Encoding: base64'
        );

        mail(
            $this->config['mailto'],
            $this->quoteMailHeader($info['title']),
            base64_encode(strip_tags($body)),
            join("\r\n", $headers));
    }


    /**
     *
     * Enter description here ...
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
            $url = sprintf($config['export_url'], urlencode($item['__imperia_node_id']));
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

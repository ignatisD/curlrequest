<?php
/**
 * Created by PhpStorm.
 * User: ignatisd
 * Date: 25/4/2018
 * Time: 10:20 πμ
 */

namespace Iggi;

/**
 * Class CurlRequest
 * @package Iggi
 */
class CurlRequest
{

    const GET = "GET";
    const POST = "POST";
    const PUT = "PUT";
    const PATCH = "PATCH";
    const DELETE = "DELETE";

    /** @var float */
    protected $started_at;
    /** @var resource|null */
    protected $curl = null;
    /** @var string */
    protected $method = "GET";

    /** @var boolean */
    public $userAgent = true;
    /** @var string */
    public $userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36';
    /** @var boolean */
    public $gzip = false;
    /** @var string|null */
    public $proxy = null;
    /** @var boolean */
    public $debug = false;
    /** @var string[]  */
    public $randomProxies = array();
    /** @var integer */
    public $timeout = 90;
    /** @var string */
    public $cookieFile = ""; // in memory cookies

    /** @var string */
    public $url = "";
    /** @var string[]  */
    public $headers = array();
    /** @var string|null */
    public $body = null;

    /** @var integer */
    protected $httpVersion = CURL_HTTP_VERSION_1_0;
    /** @var boolean */
    protected $cookieList = false;
    /** @var array */
    protected $request;
    /** @var array */
    protected $response;

    /**
     * CurlRequest constructor.
     * @param null $proxy
     */
    public function __construct($proxy = null)
    {
        $this->setProxy($proxy);
        $curlVersion = curl_version();
        if (version_compare(PHP_VERSION, "5.5", ">") && version_compare($curlVersion["version"], "7.14.1", ">")) {
            $this->cookieList = true;
        } else {
            if (!defined("CURLINFO_COOKIELIST")) {
                define("CURLINFO_COOKIELIST", 4194332);
            }
        }
    }

    /**
     * Close the connection and clear the resource on destruction
     */
    public function __destruct()
    {
        if ($this->curl) {
            curl_close($this->curl);
            $this->curl = null;
        }
    }

    /**
     * Enable/disable debug
     * @param false $state
     * @return $this
     */
    public function setDebug($state = false)
    {
        $this->debug = !empty($state);
        return $this;
    }

    /**
     * Set the request proxy
     * @param null $proxy
     * @param array $randomProxies
     * @return $this
     */
    public function setProxy($proxy = null, $randomProxies = array())
    {
        if (is_string($proxy) || is_null($proxy)) {
            $this->proxy = $proxy;
        }
        if (!empty($randomProxies) && is_array($randomProxies)) {
            $this->randomProxies = $randomProxies;
        } else {
            $this->randomProxies = array();
        }
        return $this;
    }

    /**
     * Set the request timeout
     * @param int $timeout
     * @return $this
     */
    public function setTimeout($timeout = 90)
    {
        if (is_integer($timeout) && $timeout > 0) {
            $this->timeout = $timeout;
        }
        return $this;
    }

    /**
     * Set the cookie file else the request will use in memory cookie
     * @param string $file
     * @return $this
     */
    public function setCookieFile($file = "")
    {
        $this->cookieFile = $file;
        return $this;
    }

    /**
     * Set whether the request will use gzip or not
     * @param bool $gzip
     * @return $this
     */
    public function setGzip($gzip = true)
    {
        $this->gzip = !empty($gzip);
        return $this;
    }

    /**
     * Set an external curl as this instance's curl resource
     * @param $curl
     * @return $this
     */
    public function setCurl($curl)
    {
        $this->curl = $curl;
        return $this;
    }

    /**
     * Get the protected curl resource
     * @return resource|null
     */
    public function getCurl()
    {
        return $this->curl;
    }

    /**
     * Initialize the request
     * @param $method
     * @param string $url
     * @return $this
     */
    public function init($method, $url = "")
    {
        $this->method = $method;
        $this->url = $url;
        $this->headers = array();
        $this->body = null;
        return $this;
    }

    /**
     * Setup a get request
     * @param string $url
     * @param array $headers
     * @return $this
     */
    public function get($url = "", $headers = array())
    {
        $this->method = self::GET;
        $this->url = $url;
        $this->headers = $headers;
        $this->body = null;
        return $this;
    }

    /**
     * Setup a post request
     * @param string $url
     * @param array $headers
     * @param null $body
     * @return $this
     */
    public function post($url = "", $headers = array(), $body = null)
    {
        $this->method = self::POST;
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
        return $this;
    }

    /**
     * Setup a put request
     * @param string $url
     * @param array $headers
     * @param null $body
     * @return $this
     */
    public function put($url = "", $headers = array(), $body = null)
    {
        $this->method = self::PUT;
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
        return $this;
    }

    /**
     * Setup a patch request
     * @param string $url
     * @param array $headers
     * @param null $body
     * @return $this
     */
    public function patch($url = "", $headers = array(), $body = null)
    {
        $this->method = self::PATCH;
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
        return $this;
    }

    /**
     * Setup a delete request
     * @param string $url
     * @param array $headers
     * @param null $body
     * @return $this
     */
    public function delete($url = "", $headers = array(), $body = null)
    {
        $this->method = self::DELETE;
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
        return $this;
    }

    /**
     * Set the request method
     * @param $method
     * @param null $body
     * @return $this
     */
    public function setMethod($method, $body = null)
    {
        $this->method = $method;
        if (in_array($method, array(self::POST, self::PUT, self::PATCH, self::DELETE))) {
            $this->setBody($body);
        }
        return $this;
    }

    /** Set the request headers
     * @param null $headers
     * @return $this
     */
    public function setHeaders($headers = null)
    {
        if (!is_null($headers)) {
            $this->headers = $headers;
        }
        return $this;
    }

    /**
     * Set the request body
     * @param null $body
     * @return $this
     */
    public function setBody($body = null)
    {
        if (!is_null($body)) {
            $this->body = $body;
        }
        return $this;
    }

    /**
     * Sets the request building blocks
     * @param $method
     * @param $url
     * @param null $headers
     * @param null $body
     * @return CurlRequest
     */
    public function setRequest($method, $url, $headers = null, $body = null)
    {
        return $this->init($method, $url)
            ->setHeaders($headers)
            ->setBody($body);
    }

    /**
     * Build the request
     * @param false $http1_0
     * @return $this
     */
    public function build($http1_0 = false)
    {
        if (empty($this->curl)) {
            $this->curl = curl_init();
        }
        curl_setopt($this->curl, CURLOPT_FILE, fopen('php://stdout', 'w'));
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt_array($this->curl, array(
                CURLOPT_URL => $this->url,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_CUSTOMREQUEST => $this->method,
                CURLOPT_COOKIEFILE => $this->cookieFile,
                CURLOPT_HEADER => 1,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTPHEADER => $this->headers
            )
        );
        if (!empty($this->cookieFile)) {
            curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookieFile);
        }
        if ($this->method == self::GET) {
            curl_setopt($this->curl, CURLOPT_HTTPGET, 1);
            curl_setopt($this->curl, CURLOPT_POST, false);
        }
        if ($http1_0) {
            $this->httpVersion = CURL_HTTP_VERSION_1_0;
            curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        } else {
            $this->httpVersion = CURL_HTTP_VERSION_1_1;
            curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        }

        if ($this->userAgent) {
            curl_setopt($this->curl, CURLOPT_USERAGENT, $this->userAgentString);
        }
        if ($this->gzip) {
            curl_setopt($this->curl, CURLOPT_ENCODING, "gzip");
        }
        if (!empty($this->body)) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->body);
        }
        if (!empty($this->randomProxies)) {
            $this->proxy = $this->randomProxies[mt_rand(0, count($this->randomProxies) - 1)];
        }
        if (!empty($this->proxy)) {
            curl_setopt($this->curl, CURLOPT_PROXY, $this->proxy);
        } else {
            curl_setopt($this->curl, CURLOPT_PROXY, ""); // explicitly disables proxy
        }
        if (!empty($this->debug)) {
            curl_setopt($this->curl, CURLOPT_VERBOSE, true);
        }
        $this->request = $this->toArray();
        return $this;
    }

    /**
     * Execute the request and return the response
     * @param bool $http1_0
     * @return CurlResponse
     */
    public function exec($http1_0 = false)
    {
        $this->started_at = microtime(true);
        $this->build($http1_0);
        $response = $this->parseResponse(curl_exec($this->curl));
        $this->response = $response->toArray();
        return $response;
    }

    /**
     * Download the response as a file or as a browser attachment or directly into the browser
     * @param string $filename
     * @param false $inline
     * @return false|string|null
     */
    public function download($filename = "", $inline = false)
    {
        if (!empty($filename) && !$inline) {
            return $this->saveFile($filename);
        }
        $this->exec();
        $response = $this->response;
        curl_close($this->curl);
        $this->curl = null;
        if ($response["error"]) {
            exit($response["error"]);
        }
        $headers = $response["headers"];
        $contentType = isset($headers["content-type"]) ? $headers["content-type"] : "text/plain";
        header("content-type: " . $contentType);
        if (!empty($filename)) {
            header("content-disposition: attachment; filename=$filename");
        }
        echo $response["body"];
        exit(0);
    }

    /**
     * Saves the request's response directly into a file in the temp system folder with a filename resembling the provided one
     * @param string $filename
     * @param string $ext
     * @return false|string|null
     */
    public function saveFile($filename = "", $ext = "")
    {
        $this->build();
        $file = $this->tmpname($filename, $ext);
        $handle = fopen($file, "w") or die("Unable to open file!");
        curl_setopt($this->curl, CURLOPT_FILE, $handle);
        curl_setopt($this->curl, CURLOPT_HEADER, 0);
        curl_exec($this->curl);
        $error = curl_error($this->curl);
        if ($error) {
            return null;
        }
        fclose($handle);
        return $file;
    }

    /**
     * Provides you with a filename in the system directory that is used for temporary files
     * @param string $name
     * @param string $ext
     * @return false|string
     */
    public function tmpname($name = "download", $ext = "")
    {
        if (!is_string($name) || strlen($name) < 3) {
            $name = 'download';
        } else {
            if (empty($ext)) {
                $temp = explode(".", $name, 2);
                $right = end($temp);
                if (strlen($right) === 3) {
                    $ext = $right;
                    $name = $temp[0];
                }
            }
            $name = preg_replace('/[^a-zA-ZΑ-Ωα-ω0-9_\-]/u', '', strip_tags($name));
        }
        if (strlen($name) < 3) {
            $name = 'download';
        }
        $name = tempnam(sys_get_temp_dir(), $name . "_");
        if (!empty($ext) && !preg_match("/\.$ext$/", $name)) {
            $name = $name . "." . $ext;
        }
        return $name;
    }

    /**
     * Array representation of the request
     * @return array
     */
    public function toArray()
    {
        return array(
            "method" => $this->method,
            "url" => $this->url,
            "headers" => $this->headers,
            "body" => $this->body,
            "proxy" => $this->proxy,
        );
    }

    /**
     * JSON string representation
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * HTTP code snippet
     * @return string
     */
    public function __toString()
    {
        $http = "HTTP/1.1";
        if ($this->httpVersion === CURL_HTTP_VERSION_1_0) {
            $http = "HTTP/1.0";
        }
        $url = parse_url($this->url);
        $port = isset($url['port']) ? ':' . $url['port'] : '';
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $httpRequest = "{$this->method} {$url['path']}{$query} {$http}";
        $httpRequest .= "\r\n";
        $httpRequest .= "Host: {$url['scheme']}://{$url['host']}{$port}"; // http://127.0.0.1:3001
        foreach ($this->headers as $header) {
            $httpRequest .= "\r\n";
            $httpRequest .= $header;
        }
        if (!empty($this->body)) {
            $httpRequest .= "\r\n";
            $httpRequest .= "\r\n";
            $httpRequest .= $this->body;
        }
        return $httpRequest;
    }

    /**
     * Parse the response to extract the response parts
     * @param string $response
     * @return CurlResponse
     */
    protected function parseResponse($response)
    {
        $header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $responseBody = substr($response, $header_size);
        $err = curl_error($this->curl);

        $cookies = array();
        preg_match_all('/^set-cookie:\s*([^;]*)/mi', $header, $matches);
        foreach ($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        $allCookies = array();
        if ($this->cookieList) {
            /**
             *  CURLINFO_COOKIELIST is available by curl version >= 7.14.1 and php >= 5.5
             */
            $activeCookies = curl_getinfo($this->curl, CURLINFO_COOKIELIST);
            foreach ($activeCookies as $activeCookie) {
                $parsedCookie = explode("\t", $activeCookie);
                $allCookies[] = $parsedCookie;
            }
        }
        $code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        $response = new CurlResponse($err, $header, $responseBody);
        $response
            ->setCode($code)
            ->setCookies($cookies, $allCookies)
            ->setTiming($this->started_at)
            ->setRequest($this->toArray());
        return $response;
    }

    /** Static methods */

    /**
     * Parse header string into http headers array
     * @param string $header
     * @return array
     */
    public static function parseHeaders($header = "")
    {
        $headers = array();

        foreach (explode("\r\n", $header) as $i => $line)
            if ($i === 0)
                $headers['http_code'] = $line;
            else {
                $temp = explode(': ', $line);
                if (empty($temp[0]) || empty($temp[1]))
                    continue;
                $headers[strtolower($temp[0])] = $temp[1];
            }

        return $headers;
    }

    /**
     * A simple GET request
     * @param $url
     * @param null $headers
     * @param null $proxy
     * @return CurlResponse
     */
    public static function sget($url, $headers = null, $proxy = null)
    {
        $curlRequest = new CurlRequest($proxy);
        return $curlRequest
            ->init(self::GET, $url)
            ->setHeaders($headers)
            ->exec();
    }

    /**
     * A simple POST request (not reusable)
     * @param $url
     * @param null $headers
     * @param null $body
     * @param null $proxy
     * @return CurlResponse
     */
    public static function spost($url, $headers = null, $body = null, $proxy = null)
    {
        $curlRequest = new CurlRequest($proxy);
        return $curlRequest
            ->init(self::POST, $url)
            ->setHeaders($headers)
            ->setBody($body)
            ->exec();
    }

    /**
     * A simple PUT request (not reusable)
     * @param $url
     * @param null $headers
     * @param null $body
     * @param null $proxy
     * @return CurlResponse
     */
    public static function sput($url, $headers = null, $body = null, $proxy = null)
    {
        $curlRequest = new CurlRequest($proxy);
        return $curlRequest
            ->init(self::PUT, $url)
            ->setHeaders($headers)
            ->setBody($body)
            ->exec();
    }

    /**
     * A simple PATCH request (not reusable)
     * @param $url
     * @param null $headers
     * @param null $body
     * @param null $proxy
     * @return CurlResponse
     */
    public static function spatch($url, $headers = null, $body = null, $proxy = null)
    {
        $curlRequest = new CurlRequest($proxy);
        return $curlRequest
            ->init(self::PATCH, $url)
            ->setHeaders($headers)
            ->setBody($body)
            ->exec();
    }

    /**
     * A simple DELETE request (not reusable)
     * @param $url
     * @param null $headers
     * @param null $body
     * @param null $proxy
     * @return CurlResponse
     */
    public static function sdelete($url, $headers = null, $body = null, $proxy = null)
    {
        $curlRequest = new CurlRequest($proxy);
        return $curlRequest
            ->init(self::DELETE, $url)
            ->setHeaders($headers)
            ->setBody($body)
            ->exec();
    }

    /**
     * A simple request where you may supply the method as param (not reusable)
     * @param $url
     * @param null $headers
     * @param null $body
     * @param null $proxy
     * @param string $method
     * @param bool $userAgent
     * @param false $gzip
     * @return CurlResponse
     */
    public static function custom($url, $headers = null, $body = null, $proxy = null, $method = self::GET, $userAgent = true, $gzip = false)
    {
        $curlRequest = new CurlRequest($proxy);
        $curlRequest
            ->init($method, $url)
            ->setHeaders($headers)
            ->setBody($body);
        $curlRequest->userAgent = $userAgent;
        $curlRequest->gzip = $gzip;
        return $curlRequest->exec();
    }
}
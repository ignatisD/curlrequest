<?php
/**
 * Created by PhpStorm.
 * User: Iggi
 * Date: 13/1/2019
 * Time: 11:38
 */
namespace Iggi;

/**
 * Class CurlResponse
 * @package Iggi
 */
class CurlResponse
{
    /**
     * @var array
     */
    public $cookies = array();
    /**
     * @var array
     */
    public $activeCookies = array();
    /**
     * @var integer
     */
    public $code;
    /**
     * @var string
     */
    public $body;
    /**
     * @var string[]
     */
    public $headers = array();
    /**
     * @var string
     */
    public $error;
    /**
     * @var string
     */
    public $timing;
    /**
     * @var array
     */
    public $request;

    /**
     * CurlResponse constructor.
     * @param string $error
     * @param string $header
     * @param string $body
     */
    public function __construct($error = "", $header = "", $body = "")
    {
        $this->error = $error;
        $this->parseHeaders($header);
        $this->body = $body;
        $this->request = array();
    }

    /**
     * Helper to parse the http header
     * @param string $header
     * @return $this
     */
    protected function parseHeaders($header = "")
    {
        $this->headers = array();

        foreach (explode("\r\n", $header) as $i => $line)
            if ($i === 0) {
                $this->code = $line;
            } else {
                $temp = explode(': ', $line);
                if(empty($temp[0]) || empty($temp[1])) {
                    continue;
                }
                $this->headers[strtolower($temp[0])] = $temp[1];
            }

        return $this;
    }

    /**
     * Retrieve a specific http header
     * @param string $key
     * @return string|null
     */
    public function header($key){
        if (isset($this->headers[$key])) {
            return $this->headers[$key];
        }
        return null;
    }

    /**
     * @return bool
     */
    public function ok(){
        return empty($this->error);
    }

    /**
     * @param integer $started
     * @return $this
     */
    public function setTiming($started){
        $this->timing = sprintf("%01.3f sec", (microtime(true) - $started));
        return $this;
    }

    /**
     * @param string[] $cookies
     * @param string[] $activeCookies
     * @return $this
     */
    public function setCookies($cookies = array(), $activeCookies = array()){
        $this->cookies = $cookies;
        $this->activeCookies = $activeCookies;
        return $this;
    }

    /**
     * @param integer $code
     * @return $this
     */
    public function setCode($code) {
        $this->code = $code;
        return $this;
    }

    /**
     * Sets the {@see CurlRequest} that resulted in this  {@see CurlResponse}
     * @param array $request
     * @return $this
     */
    public function setRequest($request = array()) {
        $this->request = $request;
        return $this;
    }

    /**
     * Try to decode the {@see CurlResponse} body as json
     * @return mixed|null
     */
    public function json() {
        try {
            return json_decode($this->body, true);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
        return null;
    }

    /**
     * Array representation of the {@see CurlResponse}
     * @return array
     */
    public function toArray() {
        return array(
            "code" => $this->code,
            "cookies" => $this->cookies,
            "activeCookies" => $this->activeCookies,
            "error" => $this->error,
            "headers" => $this->headers,
            "timing" => $this->timing,
            "body" => $this->body
        );
    }

    /**
     * Json string representation of the {@see CurlResponse}
     * @return false|string
     */
    public function toJson() {
        return json_encode($this->toArray());
    }

    /**
     * @return false|string
     */
    public function toString() {
        return $this->toJson();
    }
}
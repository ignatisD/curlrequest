<?php
/**
 * Created by PhpStorm.
 * User: Iggi
 * Date: 13/1/2019
 * Time: 11:38
 */
namespace Iggi;

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
     * @var array
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

    public function __construct($error = "", $header = "", $body = "")
    {
        $this->error = $error;
        $this->parseHeaders($header);
        $this->body = $body;
        $this->request = array();
    }
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

    public function header($key){
        if (isset($this->headers[$key])) {
            return $this->headers[$key];
        }
        return null;
    }
    public function ok(){
        return empty($this->error);
    }

    public function setTiming($started){
        $this->timing = sprintf("%01.3f sec", (microtime(true) - $started));
        return $this;
    }
    public function setCookies($cookies = array(), $activeCookies = array()){
        $this->cookies = $cookies;
        $this->activeCookies = $activeCookies;
        return $this;
    }

    public function setCode($code) {
        $this->code = $code;
        return $this;
    }

    public function setRequest(CurlRequest $request) {
        $this->request = $request->toArray();
        return $this;
    }

    public function json() {
        try {
            return json_decode($this->body, true);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
        return null;
    }

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
    public function toJson() {
        return json_encode($this->toArray());
    }
}
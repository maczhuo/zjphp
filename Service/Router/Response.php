<?php
namespace ZJPHP\Service\Router;

use Klein\Response as KleinResponse;
use Klein\Exceptions\ResponseAlreadySentException;

class Response extends KleinResponse
{
    public function send($override = false)
    {
        if ($this->sent && !$override) {
            throw new ResponseAlreadySentException('Response has already been sent');
        }

        // Send our response data
        $this->sendHeaders();
        $this->sendBody();

        // Lock the response from further modification
        $this->lock();

        // Mark as sent
        $this->sent = true;

        // If there running FPM, tell the process manager to finish the server request/response handling
        // PHP Console is not compatible with fastcgi_finish_request
        if (!class_exists('PC', false) && function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        return $this;
    }

    public function sendHeaders($cookies_also = true, $override = false)
    {
        if (headers_sent() && !$override) {
            return $this;
        }

        // Send our HTTP status line
        header($this->httpStatusLine());

        // Iterate through our Headers data collection and send each header
        foreach ($this->headers as $key => $value) {
            header($key .': '. $value, true);
        }

        if ($cookies_also) {
            $this->sendCookies($override);
        }

        return $this;
    }

    public function apiJson($object, $option = 0, $jsonp_prefix = null, $vary_accept = false)
    {
        $this->body('');
        $this->noCache();

        if ($option === 0) {
            $option = JSON_NUMERIC_CHECK;
        }

        $json = json_encode($object, $option);

        if (null !== $jsonp_prefix) {
            // Should ideally be application/json-p once adopted
            $this->header('Content-Type', 'text/javascript');
            $this->body("$jsonp_prefix($json);");
        } else {
            if (!empty($vary_accept)) {
                $this->header('Vary', 'Accept');
                if (strpos($vary_accept, 'application/json') !== false) {
                    $this->header('Content-Type', 'application/json');
                } else {
                    $this->header('Content-Type', 'text/plain');
                }
            } else {
                $this->header('Content-Type', 'application/json');
            }
            $this->body($json);
        }
    }
}

<?php

namespace App\Library\Http\Request;

use App\Library\Http\Response;
use App\Library\Exception;

class Request
{
    private string $baseUrl;
    private string $ContentType;
    private string $Method;

    public function setContentType(string $pContentType): Request
    {
        $this->ContentType = $pContentType;
        return $this;
    }

    public function setMethod(string $pMethod): Request
    {
        $this->Method = $pMethod;
        return $this;
    }

    public function setbaseUrl(string $pbaseUrl): Request
    {
        $this->baseUrl = $pbaseUrl;
        return $this;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getPostBody()
    {
        $result = [];
        foreach ($_POST as $key => $value) {
            $result[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
        }
        return $result;
    }

    public function httpRequest(string $pHttpMethod = "GET", $pParams = array(), $headers = array(), $env = "prod"): array
    {
        $curl = curl_init();
        try {
            $vUrl = $this->getBaseUrl();
            if (!isset($vUrl)) {
                throw new Exception(0);
            }
            if (!isset($pHttpMethod)) {
                throw new Exception(1);
            }
            if (!isset($pParams) || !is_array($pParams)) {
                throw new Exception(2);
            }
            $pParams = json_encode($pParams);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $pHttpMethod);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_URL, $vUrl);
            if (isset($this->ContentType)) {
                $headers["Content-type"] = "application/json";
            }
            if (isset($this->Method) && strtolower($this->Method) == "post") {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $pParams);
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($curl);
            $HttpStatusCode   = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $info   = curl_getinfo($curl);
            $error = curl_error($curl);
        } catch (Exception $e) {
            $HttpStatusCode = 500;
            $result = $e->getMessage();
        }
        return array(
            "status_code" => $HttpStatusCode,
            "result" => $result,
            "error" => $error,
            "curl_info" => $info
        );
    }

    public function loadController(string $pControllerName)
    {
    }
}

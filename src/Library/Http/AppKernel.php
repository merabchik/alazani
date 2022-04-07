<?php

namespace App\Library\Http;

use App\Controller;

/**
 * Written by my own hands only
 * @author MerabC <merabchik83@gmail.com>
 * @license for ScandiWeb only
 */
class AppKernel
{

    private $debug;
    private $server;
    private $path;
    private $controllerName;
    private $actionName;
    private $requestElements;
    private $requestParams = [];

    public function __construct(string $isDebugMode = "")
    {
        if ($isDebugMode) {
            error_reporting(E_ALL);
        } else {
            error_reporting(0);
        }
        $this->server = $_SERVER;
        $this->init();
        $this->debug = $isDebugMode;
        return $this;
    }

    /**
     * Initialize Application core engine
     *
     * @return void
     */
    private function init()
    {
        $this->requestElements["ip_addr"] = $this->server["REMOTE_ADDR"];
        $this->requestElements["query"] = $this->server["QUERY_STRING"];
        $this->requestElements["method"] = $this->server["REQUEST_METHOD"];
        $this->requestElements["request_uri"] = $this->server["REQUEST_URI"];
        $this->requestElements["path"] = $this->getPath();
        switch ($this->requestElements["method"]) {
            case "GET":
                $params = $_GET;
                $this->setParams($params);
                $this->getPath();
                $this->getControllerName();
                $this->getActionName();
                $this->loadController();
                break;
            case "POST":
                $params = $_POST;
                $this->setParams($params);
                break;
            case "PUT":
                break;
            case "PATCH":
                break;
            case "DELETE":
                break;
        }
    }

    /**
     * Get real path from URI
     *
     * @return string
     */
    private function getPath(): string
    {
        $vPath = $this->requestElements["request_uri"];
        $vPos = strpos($vPath, "?");
        if ($vPos) {
            $vPath = substr($vPath, 0, $vPos);
        }
        if ($vPath[0] == "/") {
            $vPath = ltrim($vPath, "/");
        }
        $this->path = $vPath;
        return $this->path;
    }

    /**
     * Get controller name for forming controller namespace and class name also, before called "getPath()" function
     *
     * @return string
     */
    private function getControllerName(): string
    {
        $vPath = $this->getPath();
        $vPos = strpos($vPath, "/");
        $vControllerName = substr($vPath, 0, $vPos);
        $this->controllerName = $vControllerName;
        return $this->controllerName;
    }

    /**
     * Get action function name, before called "getControllerName()" function
     *
     * @return string
     */
    private function getActionName(): string
    {
        $vCharsLength = strlen($this->controllerName) + 1;
        $vActionName = substr($this->path, $vCharsLength);
        $this->actionName = $vActionName;
        return $vActionName;
    }

    /**
     * Set HTTP request params
     *
     * @param array $pParams
     * @return void
     */
    private function setParams(array $pParams): void
    {
        $this->requestParams = $pParams;
    }

    /**
     * Get HTTP request params
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->requestParams;
    }

    /**
     * Get param
     *
     * @return mixed
     */
    public function getParam(string $pKey): string
    {
        $result = isset($this->requestParams[$pKey]) ? $this->requestParams[$pKey] : "";
        return $result;
    }

    /**
     * Load controller file with namespace then call function that's running for user end response
     *
     * @return void
     */
    private function loadController(): void
    {
        $namespace = '\App\Controller\\' . ucfirst($this->controllerName) . 'Controller';
        if ($this->controllerName !== null && class_exists($namespace)) {
            if (!$this->controllerName) {
                if (class_exists('\App\Controller\IndexController')) {
                    $this->controllerName = "index";
                } else {
                    $this->controllerName = null;
                }
            }
            $params = $this->getParams();
            if ($this->controllerName !== null) {
                $controller = new $namespace;
                $controller->setParams($params);

                if (!$this->actionName) {
                    if (is_callable('\App\Controller\\' . ucfirst($this->controllerName) . 'Controller'::class, "index")) {
                        $this->actionName = "index";
                    } else {
                        $this->actionName = null;
                    }
                }
                $action = $this->actionName;
            }
            if ($controller !== null && $action !== null) {
                if (isset($params)) {
                    $_params = [];
                    foreach ($params as $key => $value) {
                        $_params[$key] = $this->getParam($key);
                    }
                    $controller->{$action}($_params);
                } else {
                    $controller->{$action}();
                }
            }
        }
    }
}

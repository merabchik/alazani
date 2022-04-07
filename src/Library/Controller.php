<?php 
namespace App\Library;

class Controller {

    public function __construct()
    {
        
    }

    private $params = [];

    /**
     * Setparams
     *
     * @param array $pParams
     * @return void
     */
    public function setParams(array $pParams): void
    {
        $this->params = $pParams;
    }

    /**
     * Get param
     *
     * @return array
     */
    public function getParam(string $pKey): array
    {
        return $this->params[$pKey];
    }

    /**
     * Get params
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
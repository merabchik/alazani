<?php

namespace App\Controller;

use App\Library\Controller;
use App\Library\Http\Response;
use App\Library\Database;

class IndexController extends Controller
{

    private $db = null;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function index(array $params)
    {
        $result = [
            "status" => true
        ];
        Response::JSON($result);
    }
}

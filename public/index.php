<?php
use App\Library\Http\AppKernel;
require_once "./../vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../src/Config");
$env = $dotenv->load();
new AppKernel("true");
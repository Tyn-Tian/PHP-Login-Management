<?php

require_once __DIR__ . "/../vendor/autoload.php";

use LoginManagement\App\Router;
use LoginManagement\Controller\HomeController;

Router::add("GET", "/", HomeController::class, "index", []);

Router::run();
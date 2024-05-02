<?php

require_once __DIR__ . "/../vendor/autoload.php";

use LoginManagement\App\Router;
use LoginManagement\Config\Database;
use LoginManagement\Controller\HomeController;
use LoginManagement\Controller\UserController;

Database::getConnection("prod");

// Home
Router::add("GET", "/", HomeController::class, "index", []);

// Register
Router::add("GET", "/users/register", UserController::class, "register", []);
Router::add("POST", "/users/register", UserController::class, "postRegister", []);
Router::add("GET", "/users/login", UserController::class, "login", []);
Router::add("POST", "/users/login", UserController::class, "postLogin", []);
Router::add("GET", "/users/logout", UserController::class, "logout", []);

Router::run();
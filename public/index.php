<?php

require_once __DIR__ . "/../vendor/autoload.php";

use LoginManagement\App\Router;
use LoginManagement\Config\Database;
use LoginManagement\Controller\HomeController;
use LoginManagement\Controller\UserController;
use LoginManagement\Middleware\MustLoginMiddleware;
use LoginManagement\Middleware\MustNotLoginMiddleware;

Database::getConnection("prod");

// Home
Router::add("GET", "/", HomeController::class, "index", []);

// Register
Router::add("GET", "/users/register", UserController::class, "register", [MustNotLoginMiddleware::class]);
Router::add("POST", "/users/register", UserController::class, "postRegister", [MustNotLoginMiddleware::class]);
Router::add("GET", "/users/login", UserController::class, "login", [MustNotLoginMiddleware::class]);
Router::add("POST", "/users/login", UserController::class, "postLogin", [MustNotLoginMiddleware::class]);
Router::add("GET", "/users/logout", UserController::class, "logout", [MustLoginMiddleware::class]);
Router::add("GET", "/users/profile", UserController::class, "updateProfile", [MustLoginMiddleware::class]);
Router::add("POST", "/users/profile", UserController::class, "postUpdateProfile", [MustLoginMiddleware::class]);
Router::add("GET", "/users/password", UserController::class, "updatePassword", [MustLoginMiddleware::class]);
Router::add("POST", "/users/password", UserController::class, "postUpdatePassword", [MustLoginMiddleware::class]);

Router::run();
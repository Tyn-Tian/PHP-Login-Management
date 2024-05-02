<?php

namespace LoginManagement\Controller;

use LoginManagement\App\View;
use LoginManagement\Config\Database;
use LoginManagement\Exception\ValidationException;
use LoginManagement\Model\UserRegisterRequest;
use LoginManagement\Repository\UserRepository;
use LoginManagement\Service\UserService;

class UserController
{
    private UserService $userService;

    public function __construct()
    {
        $connection = Database::getConnection();
        $userRepository = new UserRepository($connection);
        $this->userService = new UserService($userRepository);
    }

    public function register(): void
    {
        View::render("Users/register", [
            "title" => "Register New User"
        ]);
    }

    public function postRegister(): void 
    {
        $request = new UserRegisterRequest();
        $request->id = $_POST["id"];
        $request->name = $_POST["name"];
        $request->password = $_POST["password"];

        try {
            $this->userService->register($request);
            View::redirect("/users/login");
        } catch (ValidationException $exception) {
            View::render("Users/register", [
                "title" => "Register New User",
                "error" => $exception->getMessage()
            ]);
        }
    }
}
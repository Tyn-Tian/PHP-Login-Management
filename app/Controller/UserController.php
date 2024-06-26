<?php

namespace LoginManagement\Controller;

use LoginManagement\App\View;
use LoginManagement\Config\Database;
use LoginManagement\Exception\ValidationException;
use LoginManagement\Model\UserLoginRequest;
use LoginManagement\Model\UserPasswordUpdateRequest;
use LoginManagement\Model\UserProfileUpdateRequest;
use LoginManagement\Model\UserRegisterRequest;
use LoginManagement\Repository\SessionRepository;
use LoginManagement\Repository\UserRepository;
use LoginManagement\Service\SessionService;
use LoginManagement\Service\UserService;

class UserController
{
    private UserService $userService;
    private SessionService $sessionService;

    public function __construct()
    {
        $connection = Database::getConnection();
        $userRepository = new UserRepository($connection);
        $sessionRepository = new SessionRepository($connection);
        $this->userService = new UserService($userRepository);
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
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

    public function login(): void
    {
        View::render("Users/login", [
            "title" => "Login User"
        ]);
    }

    public function postLogin(): void
    {
        $request = new UserLoginRequest();
        $request->id = $_POST["id"];
        $request->password = $_POST["password"];

        try {
            $response = $this->userService->login($request);
            $this->sessionService->create($response->user->id);
            View::redirect("/");
        } catch (ValidationException $exception) {
            View::render("Users/login", [
                "title" => "Login User",
                "error" => $exception->getMessage()
            ]);
        }
    }

    public function logout(): void
    {
        $this->sessionService->destory();
        View::redirect("/");
    }

    public function updateProfile()
    {
        $user = $this->sessionService->current();

        View::render("Users/profile", [
            "title" => "Update User Profile",
            "user" => [
                "id" => $user->id,
                "name" => $user->name
            ]
        ]);
    }

    public function postUpdateProfile()
    {
        $user = $this->sessionService->current();

        $request = new UserProfileUpdateRequest();
        $request->id = $user->id;
        $request->name = $_POST["name"];

        try {
            $this->userService->updateProfile($request);
            View::redirect("/");
        } catch (ValidationException $exception) {
            View::render("Users/profile", [
                "title" => "Update User Profile",
                "error" => $exception->getMessage(),
                "user" => [
                    "id" => $user->id,
                    "name" => $user->name
                ]
            ]);
        }
    }

    public function updatePassword()
    {
        $user = $this->sessionService->current();
        View::render("Users/password", [
            "title" => "Update User Password",
            "id" => $user->id
        ]);
    }

    public function postUpdatePassword()
    {
        $user = $this->sessionService->current();

        $request = new UserPasswordUpdateRequest;
        $request->id = $user->id;
        $request->oldPassword = $_POST["oldPassword"];
        $request->newPassword = $_POST["newPassword"];

        try {
            $this->userService->updatePassword($request);
            View::redirect("/");
        } catch (ValidationException $exception) {
            View::render("Users/password", [
                "title" => "Update User Password",
                "error" => $exception->getMessage(),
                "id" => $user->id
            ]);
        }
    }
}

<?php

namespace LoginManagement\Controller;

use LoginManagement\App\View;
use LoginManagement\Config\Database;
use LoginManagement\Repository\SessionRepository;
use LoginManagement\Repository\UserRepository;
use LoginManagement\Service\SessionService;

class HomeController
{
    private SessionService $sessionService;

    public function __construct()
    {
        $connection = Database::getConnection();
        $userRepository = new UserRepository($connection);
        $sessionRepository = new SessionRepository($connection);
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }

    function index(): void
    {
        $user = $this->sessionService->current();

        if ($user == null) {
            View::render("Home/index", [
                "title" => "PHP Login Management"
            ]);
        } else {
            View::render("Home/dashboard", [
                "title" => "Dashboard",
                "user" => [
                    "name" => $user->name
                ]
            ]);
        }
    }
}

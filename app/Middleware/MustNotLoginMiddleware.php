<?php

namespace LoginManagement\Middleware;

use LoginManagement\App\View;
use LoginManagement\Config\Database;
use LoginManagement\Repository\SessionRepository;
use LoginManagement\Repository\UserRepository;
use LoginManagement\Service\SessionService;

class MustNotLoginMiddleware implements Middleware
{
    private SessionService $sessionService;

    public function __construct()
    {
        $userRepository = new UserRepository(Database::getConnection());
        $sessionRepository = new SessionRepository(Database::getConnection());
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }

    public function before(): void
    {
        $user = $this->sessionService->current();

        if ($user != null) {
            View::redirect("/");
        }
    }
}
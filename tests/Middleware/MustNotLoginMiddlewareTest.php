<?php

namespace LoginManagement\App {
    function header(string $value)
    {
        echo $value;
    }
}

namespace LoginManagement\Middleware {

    use LoginManagement\Config\Database;
    use LoginManagement\Domain\Session;
    use LoginManagement\Domain\User;
    use LoginManagement\Repository\SessionRepository;
    use LoginManagement\Repository\UserRepository;
    use LoginManagement\Service\SessionService;
    use PHPUnit\Framework\TestCase;

    class MustNotLoginMiddlewareTest extends TestCase
    {
        private MustNotLoginMiddleware $middleware;
        private SessionRepository $sessionRepository;
        private UserRepository $userRepository;

        protected function setUp(): void
        {
            $this->middleware = new MustNotLoginMiddleware();
            putenv("mode=test");

            $this->sessionRepository = new SessionRepository(Database::getConnection());
            $this->userRepository = new UserRepository(Database::getConnection());

            $this->sessionRepository->deleteAll();
            $this->userRepository->deleteAll();
        }

        public function testBeforeGuest()
        {
            $this->middleware->before();
            $this->expectOutputString("");
        }

        public function testBeforeLoginuser()
        {
            $user = new User();
            $user->id = "testId";
            $user->name = "testName";
            $user->password = "testPassword";
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->middleware->before();
            $this->expectOutputRegex("[Location: /]");
        }
    }
}

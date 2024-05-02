<?php 

namespace LoginManagement\Service;

use LoginManagement\Config\Database;
use LoginManagement\Domain\Session;
use LoginManagement\Domain\User;
use LoginManagement\Repository\SessionRepository;
use LoginManagement\Repository\UserRepository;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

function setcookie(string $name, string $value) 
{
    echo "$name: $value";
}

class SessionServiceTest extends TestCase
{
    private SessionService $sessionService;
    private SessionRepository $sessionRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->sessionRepository = new SessionRepository(Database::getConnection());
        $this->userRepository = new UserRepository(Database::getConnection());
        $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();

        $user = new User();
        $user->id = "testId";
        $user->name = "testName";
        $user->password = "testPassword";

        $this->userRepository->save($user);
    }

    public function testCreate()
    {
        $session = $this->sessionService->create("testId");

        $this->expectOutputRegex("[X-TYN-SESSION: $session->id]");

        $result = $this->sessionRepository->findById($session->id);

        self::assertEquals("testId", $result->userId);
    }

    public function testDestory()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = "testId";

        $this->sessionRepository->save($session);

        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

        $this->sessionService->destory();

        $this->expectOutputRegex("[X-TYN-SESSION: ]");

        $result = $this->sessionRepository->findById($session->id);
        self::assertNull($result);
    }

    public function testCurrent()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = "testId";

        $this->sessionRepository->save($session);

        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

        $user = $this->sessionService->current();

        self::assertEquals($session->userId, $user->id);
    }
}
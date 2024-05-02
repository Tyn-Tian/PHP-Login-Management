<?php

namespace LoginManagement\Service;

use LoginManagement\Domain\Session;
use LoginManagement\Domain\User;
use LoginManagement\Repository\SessionRepository;
use LoginManagement\Repository\UserRepository;

class SessionService
{
    public static string $COOKIE_NAME = "X-TYN-SESSION";

    public function __construct(
        private SessionRepository $sessionRepository,
        private UserRepository $userRepository
    ) {
    }

    public function create(string $userId): Session
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = $userId;

        $this->sessionRepository->save($session);

        setcookie(self::$COOKIE_NAME, $session->id, time() + (60 * 60 * 24 * 30), "/");

        return $session;
    }

    public function destory()
    {
        $sessionId = $_COOKIE[self::$COOKIE_NAME] ?? "";
        $this->sessionRepository->deleteById($sessionId);

        setcookie(self::$COOKIE_NAME, '', 1, "/");
    }

    public function current(): ?User
    {
        $sessionId = $_COOKIE[self::$COOKIE_NAME] ?? "";

        $session = $this->sessionRepository->findById($sessionId);
        if ($session == null) {
            return null;
        }

        return $this->userRepository->findById($session->userId);
    }
}

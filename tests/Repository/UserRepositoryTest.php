<?php

namespace LoginManagement\Repository;

use LoginManagement\Config\Database;
use LoginManagement\Domain\User;
use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->userRepository = new UserRepository(Database::getConnection());
        $this->userRepository->deleteAll();
    }

    public function testRegisterSuccess()
    {
        $user = new User();
        $user->id = "testId";
        $user->name = "testName";
        $user->password = "testPassword";

        $result = $this->userRepository->save($user);

        self::assertEquals($user->id, $result->id);
        self::assertEquals($user->name, $result->name);
        self::assertEquals($user->password, $result->password);
    }

    public function testFindbyIdNotFound()
    {
        $result = $this->userRepository->findById("empty");
        self::assertNull($result);
    }
}
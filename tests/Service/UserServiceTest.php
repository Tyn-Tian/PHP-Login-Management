<?php

namespace LoginManagement\Service;

use LoginManagement\Config\Database;
use LoginManagement\Domain\User;
use LoginManagement\Exception\ValidationException;
use LoginManagement\Model\UserLoginRequest;
use LoginManagement\Model\UserRegisterRequest;
use LoginManagement\Repository\UserRepository;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepository $userRepository;

    protected function setUp(): void 
    {
        $connection = Database::getConnection();
        $this->userRepository = new UserRepository($connection);
        $this->userService = new UserService($this->userRepository);

        $this->userRepository->deleteAll();
    }

    public function testRegisterSuccess() 
    {
        $request = new UserRegisterRequest();
        $request->id = "testId";
        $request->name = "testName";
        $request->password = "testPassword";

        $response = $this->userService->register($request);


        self::assertEquals($request->id, $response->user->id);
        self::assertEquals($request->name, $response->user->name);
        self::assertNotEquals($request->password, $response->user->password);

        self::assertTrue(password_verify($request->password, $response->user->password));
    }

    public function testRegisterFailed()
    {
        $this->expectException(ValidationException::class);

        $request = new UserRegisterRequest();
        $request->id = "";
        $request->name = "";
        $request->password = "";

        $response = $this->userService->register($request);
    }

    public function testRegisterDuplicate()
    {
        $user = new User();
        $user->id = "testId";
        $user->name = "testName";
        $user->password = "testPassword";
        $this->userRepository->save($user);
        
        $this->expectException(ValidationException::class);

        $request = new UserRegisterRequest();
        $request->id = "testId";
        $request->name = "testName";
        $request->password = "testPassword";
        $this->userService->register($request);
    }

    public function testLoginNotFound()
    {
        $request = new UserLoginRequest();
        $request->id = "testId";
        $request->password = "testName";

        $this->expectException(ValidationException::class);

        $this->userService->login($request);
    }

    public function testLoginWrongPassword()
    {
        $user = new User();
        $user->id = "testId";
        $user->name = "testName";
        $user->password = password_hash("testPassword", PASSWORD_BCRYPT);

        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->id = "testIdSalah";
        $request->password = "testName";

        $this->userService->login($request);
    }

    public function testLoginSuccess()
    {
        $user = new User();
        $user->id = "testId";
        $user->name = "testName";
        $user->password = password_hash("testPassword", PASSWORD_BCRYPT);

        $request = new UserLoginRequest();
        $request->id = "testId";
        $request->password = "testName";

        $response = $this->userService->login($request);

        self::assertEquals($request->id, $response->user->id);
        self::assertTrue(password_verify($request->id, $response->user->id));
    }
}
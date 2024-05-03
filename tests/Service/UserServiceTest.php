<?php

namespace LoginManagement\Service;

use LoginManagement\Config\Database;
use LoginManagement\Domain\User;
use LoginManagement\Exception\ValidationException;
use LoginManagement\Model\UserLoginRequest;
use LoginManagement\Model\UserPasswordUpdateRequest;
use LoginManagement\Model\UserProfileUpdateRequest;
use LoginManagement\Model\UserRegisterRequest;
use LoginManagement\Repository\SessionRepository;
use LoginManagement\Repository\UserRepository;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

    protected function setUp(): void 
    {
        $connection = Database::getConnection();
        $this->sessionRepository = new SessionRepository($connection);
        $this->userRepository = new UserRepository($connection);
        $this->userService = new UserService($this->userRepository);

        $this->sessionRepository->deleteAll();
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

        $this->userService->register($request);
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
        $this->userRepository->save($user);

        $request = new UserLoginRequest();
        $request->id = "testId";
        $request->password = "testPassword";

        $response = $this->userService->login($request);

        self::assertEquals($request->id, $response->user->id);
        self::assertTrue(password_verify($request->password, $response->user->password));
    }

    public function testUpdateSuccess()
    {
        $user = new User();
        $user->id = "testId";
        $user->name = "testName";
        $user->password = password_hash("testPassword", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request = new UserProfileUpdateRequest();
        $request->id = $user->id;
        $request->name = "testNameChange";

        $this->userService->updateProfile($request);

        $result = $this->userRepository->findById($user->id);

        self::assertEquals($request->name, $result->name);
    }

    public function testUpdateValidationError()
    {
        $this->expectException(ValidationException::class);

        $request = new UserProfileUpdateRequest();
        $request->id = "";
        $request->name = "";

        $this->userService->updateProfile($request);
    }

    public function testUpdateNotFound()
    {
        $this->expectException(ValidationException::class);

        $request = new UserProfileUpdateRequest();
        $request->id = "testId";
        $request->name = "testName";

        $this->userService->updateProfile($request);
    }

    public function testUpdatePasswordSuccess()
    {
        $user = new User();
        $user->id = "testId";
        $user->name = "testName";
        $user->password = password_hash("testPassword", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request = new UserPasswordUpdateRequest();
        $request->id = $user->id;
        $request->oldPassword = "testPassword";
        $request->newPassword = "testPasswordChange";

        $this->userService->updatePassword($request);

        $result = $this->userRepository->findById($user->id);

        self::assertTrue(password_verify($request->newPassword, $result->password));
    }

    public function testUpdatePasswordValidationError()
    {
        $this->expectException(ValidationException::class);

        $request = new UserPasswordUpdateRequest();
        $request->id = "testId";
        $request->oldPassword = "testPassword";
        $request->newPassword = "testPasswordChange";

        $this->userService->updatePassword($request);
    }

    public function testUpdatePasswordWrongOldPassword()
    {
        $this->expectException(ValidationException::class);

        $user = new User();
        $user->id = "testId";
        $user->name = "testName";
        $user->password = password_hash("testPassword", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request = new UserPasswordUpdateRequest();
        $request->id = $user->id;
        $request->oldPassword = "testPasswordWrong";
        $request->newPassword = "testPasswordChange";

        $this->userService->updatePassword($request);
    }

    public function testUpdatePasswordNotFount()
    {
        $this->expectException(ValidationException::class);

        $request = new UserPasswordUpdateRequest();
        $request->id = "testId";
        $request->oldPassword = "testPassword";
        $request->newPassword = "testPasswordChange";

        $this->userService->updatePassword($request);
    }
}
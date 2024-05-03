<?php

namespace LoginManagement\Controller {

    require_once __DIR__ . "/../Helper/helper.php";

    use LoginManagement\Config\Database;
    use LoginManagement\Domain\Session;
    use LoginManagement\Domain\User;
    use LoginManagement\Repository\SessionRepository;
    use LoginManagement\Repository\UserRepository;
    use LoginManagement\Service\SessionService;
    use PHPUnit\Framework\TestCase;

    class UserControllerTest extends TestCase
    {
        private UserController $userController;
        private UserRepository $userRepository;
        private SessionRepository $sessionRepository;

        protected function setUp(): void
        {
            $this->userController = new UserController();

            $this->sessionRepository = new SessionRepository(Database::getConnection());
            $this->sessionRepository->deleteAll();

            $this->userRepository = new UserRepository(Database::getConnection());
            $this->userRepository->deleteAll();

            putenv("mode=test");
        }

        public function testRegister()
        {
            $this->userController->register();

            $this->expectOutputRegex("[Register New User]");
            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Password]");
        }

        public function testPostRegisterSuccess()
        {
            $_POST['id'] = 'testId';
            $_POST['name'] = 'testName';
            $_POST['password'] = 'testPassword';

            $this->userController->postRegister();

            $this->expectOutputRegex("[Location: /users/login]");
        }

        public function testPostRegisterValidationError()
        {
            $_POST["id"] = "";
            $_POST["name"] = "testName";
            $_POST["password"] = "testPassword";

            $this->userController->postRegister();

            $this->expectOutputRegex("[Register New User]");
            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id, Name, Password cannot blank]");
        }

        public function testPostRegisterDuplicate()
        {
            $user = new User();
            $user->id = "testId";
            $user->name = "testName";
            $user->password = "testPassword";

            $this->userRepository->save($user);

            $_POST["id"] = "testId";
            $_POST["name"] = "testName";
            $_POST["password"] = "testPassword";

            $this->userController->postRegister();

            $this->expectOutputRegex("[Register New User]");
            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[User Id already exists]");
        }

        public function testLogin()
        {
            $this->userController->login();

            $this->expectOutputRegex("[Login User]");
            $this->expectOutputRegex("[Login]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
        }

        public function testLoginSuccess() 
        {
            $user = new User();
            $user->id = "testId";
            $user->name = "testName";
            $user->password = password_hash("testPassword", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $_POST["id"] = "testId";
            $_POST["password"] = "testPassword";

            $this->userController->postLogin();

            $this->expectOutputRegex("[Location: /]");
            $this->expectOutputRegex("[X-TYN-SESSION: ]");
        }

        public function testLoginValidationError()
        {
            $_POST["id"] = "";
            $_POST["password"] = "";

            $this->userController->postLogin();

            $this->expectOutputRegex("[Login User]");
            $this->expectOutputRegex("[Login]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id and Password cannot blank]");
        }

        public function testLoginUserNotFound() 
        {
            $_POST["id"] = "testId";
            $_POST["password"] = "testPassword";

            $this->userController->postLogin();

            $this->expectOutputRegex("[Login User]");
            $this->expectOutputRegex("[Login]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id or password is wrong]");
        }

        public function testLoginWrongPassword()
        {
            $user = new User();
            $user->id = "testId";
            $user->name = "testName";
            $user->password = password_hash("testPassword", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $_POST["id"] = "testId";
            $_POST["password"] = "testPasswordSalah";

            $this->userController->postLogin();

            $this->expectOutputRegex("[Login User]");
            $this->expectOutputRegex("[Login]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id or password is wrong]");
        }

        public function testLogout()
        {
            $user = new User();
            $user->id = "testId";
            $user->name = "testName";
            $user->password = password_hash("testPassword", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->logout();

            $this->expectOutputRegex("[Location: /]");
            $this->expectOutputRegex("[X-TYN-SESSION: ]");
        }

        public function testUpdateProfile()
        {
            $user = new User();
            $user->id = "testId";
            $user->name = "testName";
            $user->password = password_hash("testPassword", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->updateProfile();

            $this->expectOutputRegex("[Update User Profile]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[$user->id]");
            $this->expectOutputRegex("[$user->name]");
        }

        public function testPostUpdateProfileSuccess()
        {
            $user = new User();
            $user->id = "testId";
            $user->name = "testName";
            $user->password = password_hash("testPassword", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST["name"] = "testNameChange";
            $this->userController->postUpdateProfile();

            $this->expectOutputRegex("[Location: /]");

            $result = $this->userRepository->findById($user->id);
            self::assertEquals("testNameChange", $result->name);
        }

        public function testPostUpdateProfileValidationError()
        {
            $user = new User();
            $user->id = "testId";
            $user->name = "testName";
            $user->password = password_hash("testPassword", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST["name"] = "";
            $this->userController->postUpdateProfile();

            $this->expectOutputRegex("[Update User Profile]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[$user->id]");
            $this->expectOutputRegex("[$user->name]");
            $this->expectOutputRegex("[Name cannot blank]");
        }

        public function testUpdatePassword()
        {
            $user = new User();
            $user->id = "testId";
            $user->name = "testName";
            $user->password = password_hash("testPassword", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->updatePassword();

            $this->expectOutputRegex("[Update User Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Old Password]");
            $this->expectOutputRegex("[New Password]");
            $this->expectOutputRegex("[$user->id]");
        }

        public function testPostUpdatePasswordSuccess()
        {
            $user = new User();
            $user->id = "testId";
            $user->name = "testName";
            $user->password = password_hash("testPassword", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST["oldPassword"] = "testPassword";
            $_POST["newPassword"] = "testPasswordChange";

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Location: /]");

            $result = $this->userRepository->findById($user->id);
            self::assertTrue(password_verify("testPasswordChange", $result->password));
        }

        public function testPostUpdatePasswordValidationError()
        {
            $user = new User();
            $user->id = "testId";
            $user->name = "testName";
            $user->password = password_hash("testPassword", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;
            
            $_POST["oldPassword"] = "";
            $_POST["newPassword"] = "";

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Update User Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Old Password]");
            $this->expectOutputRegex("[New Password]");
            $this->expectOutputRegex("[$user->id]");
            $this->expectOutputRegex("[Old password and new password cannot blank]");
        }

        public function testPostUpdatePasswordWrongOldPassword()
        {
            $user = new User();
            $user->id = "testId";
            $user->name = "testName";
            $user->password = password_hash("testPassword", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;
            
            $_POST["oldPassword"] = "testPasswordWrong";
            $_POST["newPassword"] = "testPasswordChange";

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Update User Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Old Password]");
            $this->expectOutputRegex("[New Password]");
            $this->expectOutputRegex("[$user->id]");
            $this->expectOutputRegex("[Old password is wrong]");
        }
    }
}

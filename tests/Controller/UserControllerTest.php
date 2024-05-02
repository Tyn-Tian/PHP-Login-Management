<?php

namespace LoginManagement\App {
    function header(string $value) 
    {
        echo $value;
    }
}

namespace LoginManagement\Controller {

    use LoginManagement\Config\Database;
    use LoginManagement\Domain\User;
    use LoginManagement\Repository\UserRepository;
    use PHPUnit\Framework\TestCase;

    class UserControllerTest extends TestCase 
    {
        private UserController $userController;
        private UserRepository $userRepository;

        public function setUp(): void
        {
            $this->userController = new UserController();

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
    }
}
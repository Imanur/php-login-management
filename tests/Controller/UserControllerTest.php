<?php

namespace Programmerphp\Loginmanagement\Controller {

    require_once __DIR__ . "/../Helper/Helper.php";

    use PHPUnit\Framework\TestCase;
    use Programmerphp\Loginmanagement\Config\Database;
    use Programmerphp\Loginmanagement\Domain\Session;
    use Programmerphp\Loginmanagement\Domain\User;
    use Programmerphp\Loginmanagement\Exception\ValidationException;
    use Programmerphp\Loginmanagement\Repository\SessionRepository;
    use Programmerphp\Loginmanagement\Repository\UserRepository;
    use Programmerphp\Loginmanagement\Service\SessionService;

    class UserControllerTest extends TestCase
    {
        protected UserController $userController;
        protected UserRepository $userRepository;
        protected SessionRepository $sesionRepository;

        protected function setUp(): void
        {
            $this->userController = new UserController();
            $connection = Database::getConnection();
            $this->userRepository = new UserRepository($connection);
            $this->sesionRepository = new SessionRepository($connection);

            $this->sesionRepository->deleteAll();
            $this->userRepository->deleteALl();
            putenv("mode=test");
        }

        public function testRegister()
        {
            $this->userController->register();
            $this->expectOutputRegex('[Register]');
            $this->expectOutputRegex('[Id]');
            $this->expectOutputRegex('[Name]');
            $this->expectOutputRegex('[Password]');
            $this->expectOutputRegex('[Register New User]');
        }

        public function testPostRegisterSuccess()
        {
            $_POST['id'] = 'iman';
            $_POST['name'] = 'iman';
            $_POST['password'] = 'iman';

            $this->userController->postRegister();

            $this->expectOutputRegex("[Location: /users/login]");
        }

        public function testPostRegisterValidationError()
        {
            $_POST['id'] = '';
            $_POST['name'] = '';
            $_POST['password'] = '';

            $this->userController->postRegister();

            $this->expectOutputRegex('[Register]');
            $this->expectOutputRegex('[Id]');
            $this->expectOutputRegex('[Name]');
            $this->expectOutputRegex('[Password]');
            $this->expectOutputRegex('[Register New User]');
            $this->expectOutputRegex('[Id, Name, Password can not blank]');
        }

        public function testPostRegisterDuplicate()
        {
            $user = new User();
            $user->id = 'iman';
            $user->name = 'Iman';
            $user->password = 'rahasia';

            $this->userRepository->save($user);

            $_POST['id'] = 'iman';
            $_POST['name'] = 'Iman';
            $_POST['password'] = 'rahasia';

            $this->userController->postRegister();

            $this->expectOutputRegex('[Register]');
            $this->expectOutputRegex('[Id]');
            $this->expectOutputRegex('[Name]');
            $this->expectOutputRegex('[Password]');
            $this->expectOutputRegex('[Register New User]');
            $this->expectOutputRegex('[User Id already exists]');
        }

        public function testLogin()
        {
            $this->userController->login();
            $this->expectOutputRegex('[Login]');
            $this->expectOutputRegex('[Id]');
            $this->expectOutputRegex('[Password]');
            $this->expectOutputRegex('[Login User]');
        }

        public function testLoginSuccess()
        {
            $user = new User();
            $user->id = 'iman';
            $user->name = 'Iman';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $_POST['id'] = 'iman';
            $_POST['password'] = 'rahasia';

            $this->userController->postLogin();

            $this->expectOutputRegex('[Location: /]');
            $this->expectOutputRegex('[X-PHP-SESSION: ]');
        }

        public function testLoginValidationError()
        {
            $_POST['id'] = '';
            $_POST['password'] = '';

            $this->userController->postLogin();

            $this->expectOutputRegex('[Login]');
            $this->expectOutputRegex('[Id]');
            $this->expectOutputRegex('[Password]');
            $this->expectOutputRegex('[Login User]');
            $this->expectOutputRegex('[Id, Password can not blank]');
        }

        public function testLoginUserNotFound()
        {
            $_POST['id'] = 'notfound';
            $_POST['password'] = 'notfound';

            $this->userController->postLogin();

            $this->expectOutputRegex('[Login]');
            $this->expectOutputRegex('[Id]');
            $this->expectOutputRegex('[Password]');
            $this->expectOutputRegex('[Login User]');
            $this->expectOutputRegex('[Id or password is wrong]');
        }

        public function testLoginWrongPassword()
        {
            $user = new User();
            $user->id = 'iman';
            $user->name = 'Iman';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $_POST['id'] = 'iman';
            $_POST['password'] = 'salah';

            $this->userController->postLogin();

            $this->expectOutputRegex('[Login]');
            $this->expectOutputRegex('[Id]');
            $this->expectOutputRegex('[Password]');
            $this->expectOutputRegex('[Login User]');
            $this->expectOutputRegex('[Id or password is wrong]');
        }

        public function testLogout()
        {
            $user = new User();
            $user->id = 'iman';
            $user->name = 'Iman';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->idUser = $user->id;
            $this->sesionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->logout();

            $this->expectOutputRegex('[Location: /]');
            $this->expectOutputRegex('[X-PHP-SESSION: ]');
        }

        public function testUpdateProfile()
        {
            $user = new User();
            $user->id = 'iman';
            $user->name = 'Iman';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->idUser = $user->id;
            $this->sesionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->updateProfile();

            $this->expectOutputRegex('[Profile]');
            $this->expectOutputRegex('[Id]');
            $this->expectOutputRegex('[iman]');
            $this->expectOutputRegex('[Name]');
            $this->expectOutputRegex('[Iman]');
            $this->expectOutputRegex('[Update Profile]');
        }

        public function testPostUpdateProfile()
        {
            $user = new User();
            $user->id = 'iman';
            $user->name = 'Iman';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->idUser = $user->id;
            $this->sesionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['name'] = "Nur";

            $this->userController->postUpdateProfile();
            $this->expectOutputRegex('[Location: /]');

            $result = $this->userRepository->findById('iman');

            self::assertEquals('Nur', $result->name);
        }

        public function testPostUpdateProfileValidationError()
        {
            $user = new User();
            $user->id = 'iman';
            $user->name = 'Iman';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->idUser = $user->id;
            $this->sesionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['name'] = "";

            $this->userController->postUpdateProfile();

            $this->expectOutputRegex('[Profile]');
            $this->expectOutputRegex('[Id]');
            $this->expectOutputRegex('[iman]');
            $this->expectOutputRegex('[Name]');
            $this->expectOutputRegex('[Update Profile]');
            $this->expectOutputRegex('[Name can not blank]');
        }

        public function testUpdatePassword()
        {
            $user = new User();
            $user->id = 'iman';
            $user->name = 'Iman';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->idUser = $user->id;
            $this->sesionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->updatePassword();

            $this->expectOutputRegex('[Password]');
            $this->expectOutputRegex('[Id]');
            $this->expectOutputRegex('[iman]');
        }

        public function testPostUpdatePasswordSuccess()
        {
            $user = new User();
            $user->id = 'iman';
            $user->name = 'Iman';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->idUser = $user->id;
            $this->sesionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = 'rahasia';
            $_POST['newPassword'] = 'nur';

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex('[Location: /]');

            $result = $this->userRepository->findById($user->id);

            self::assertTrue(password_verify('nur', $result->password));
        }

        public function testPostUpdatePasswordValidationError()
        {
            $user = new User();
            $user->id = 'iman';
            $user->name = 'Iman';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->idUser = $user->id;
            $this->sesionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = '';
            $_POST['newPassword'] = '';

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex('[Password]');
            $this->expectOutputRegex('[Id]');
            $this->expectOutputRegex('[iman]');
            $this->expectOutputRegex('[Old password, New password can not blank]');
        }

        public function testPostUpdatePasswordWrongOldPassword()
        {
            $user = new User();
            $user->id = 'iman';
            $user->name = 'Iman';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->idUser = $user->id;
            $this->sesionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = 'salah';
            $_POST['newPassword'] = 'nur';

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex('[Password]');
            $this->expectOutputRegex('[Id]');
            $this->expectOutputRegex('[iman]');
            $this->expectOutputRegex('[Old password is wrong]');
        }
    }
}

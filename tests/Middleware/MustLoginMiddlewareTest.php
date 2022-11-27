<?php

namespace Programmerphp\Loginmanagement\Middleware {

    require_once __DIR__ . "/../Helper/Helper.php";

    use PHPUnit\Framework\TestCase;
    use Programmerphp\Loginmanagement\Config\Database;
    use Programmerphp\Loginmanagement\Domain\Session;
    use Programmerphp\Loginmanagement\Domain\User;
    use Programmerphp\Loginmanagement\Repository\SessionRepository;
    use Programmerphp\Loginmanagement\Repository\UserRepository;
    use Programmerphp\Loginmanagement\Service\SessionService;

    class MustLoginMiddlewareTest extends TestCase
    {

        protected MustLoginMiddleware $middleware;
        protected UserRepository $userRepository;
        protected SessionRepository $sessionRepository;

        protected function setUp(): void
        {
            $this->middleware = new MustLoginMiddleware();
            putenv("mode=test");

            $connection = Database::getConnection();
            $this->userRepository = new UserRepository($connection);
            $this->sessionRepository = new SessionRepository($connection);

            $this->sessionRepository->deleteAll();
            $this->userRepository->deleteALl();
        }

        public function testBefore()
        {
            $this->middleware->before();

            $this->expectOutputRegex("[Location: /users/login]");
        }

        public function testBeforeGuest()
        {

            $user = new User();
            $user->id = 'iman';
            $user->name = 'Iman';
            $user->password = password_hash('rahasia', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->idUser = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;


            $this->middleware->before();

            $this->expectOutputString("");
        }
    }
}

<?php

namespace Programmerphp\Loginmanagement\Controller;

use PHPUnit\Framework\TestCase;
use Programmerphp\Loginmanagement\Config\Database;
use Programmerphp\Loginmanagement\Domain\Session;
use Programmerphp\Loginmanagement\Domain\User;
use Programmerphp\Loginmanagement\Repository\SessionRepository;
use Programmerphp\Loginmanagement\Repository\UserRepository;
use Programmerphp\Loginmanagement\Service\SessionService;

class HomeControllerTest extends TestCase
{
    protected HomeController $homeController;
    protected UserRepository $userRepository;
    protected SessionRepository $sessionRepository;

    protected function setUp(): void
    {
        $this->homeController = new HomeController();
        $connection = Database::getConnection();
        $this->sessionRepository = new SessionRepository($connection);
        $this->userRepository = new UserRepository($connection);

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteALl();
    }

    public function testGuest()
    {
        $this->homeController->index();

        $this->expectOutputRegex('[Login Management]');
    }

    public function testUserLogin()
    {
        $user = new User();
        $user->id = 'iman';
        $user->name = 'Iman';
        $user->password = 'rahasia';
        $this->userRepository->save($user);

        $session = new Session();
        $session->id = uniqid();
        $session->idUser = $user->id;
        $this->sessionRepository->save($session);

        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

        $this->homeController->index();

        $this->expectOutputRegex('[Hello Iman]');
    }
}

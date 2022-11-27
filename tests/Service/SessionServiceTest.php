<?php

namespace Programmerphp\Loginmanagement\Service;

require_once __DIR__ . "/../Helper/Helper.php";

use PHPUnit\Framework\TestCase;
use Programmerphp\Loginmanagement\Config\Database;
use Programmerphp\Loginmanagement\Domain\Session;
use Programmerphp\Loginmanagement\Domain\User;
use Programmerphp\Loginmanagement\Repository\SessionRepository;
use Programmerphp\Loginmanagement\Repository\UserRepository;


class SessionServiceTest extends TestCase
{
    protected SessionService $sessionService;
    protected SessionRepository $sessionRepository;
    protected UserRepository $userRepository;

    protected function setUp(): void
    {
        $connection = Database::getConnection();
        $this->sessionRepository = new SessionRepository($connection);
        $this->userRepository = new UserRepository($connection);
        $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteALl();

        $user = new User();
        $user->id = 'iman';
        $user->name = 'Iman';
        $user->password = 'rahasia';

        $this->userRepository->save($user);
    }

    public function testCreate()
    {

        $session = $this->sessionService->create('iman');

        $this->expectOutputRegex("[X-PHP-SESSION: $session->id]");

        $result = $this->sessionRepository->findById($session->id);

        self::assertEquals('iman', $result->idUser);
    }

    public function testDestroy()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->idUser = 'iman';

        $this->sessionRepository->save($session);

        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

        $this->sessionService->destroy();

        $this->expectOutputRegex("[X-PHP-SESSION: ]");

        $result = $this->sessionRepository->findById($session->id);
        self::assertNull($result);
    }

    public function testCurret()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->idUser = 'iman';

        $this->sessionRepository->save($session);

        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

        $user = $this->sessionService->current();

        self::assertEquals($session->idUser, $user->id);
    }
}

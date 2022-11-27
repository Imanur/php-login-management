<?php

namespace Programmerphp\Loginmanagement\Repository;

use PHPUnit\Framework\TestCase;
use Programmerphp\Loginmanagement\Config\Database;
use Programmerphp\Loginmanagement\Domain\Session;
use Programmerphp\Loginmanagement\Domain\User;

class SessionRepositoryTest extends TestCase
{
    protected SessionRepository $sessionRepository;
    protected UserRepository $userRepository;

    protected function setUp(): void
    {
        $connection = Database::getConnection();
        $this->userRepository = new UserRepository($connection);
        $this->sessionRepository = new SessionRepository($connection);

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteALl();

        $user = new User();
        $user->id = 'iman';
        $user->name = 'Iman';
        $user->password = 'rahasia';

        $this->userRepository->save($user);
    }

    public function testSaveSucces()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->idUser = 'iman';

        $this->sessionRepository->save($session);

        $result = $this->sessionRepository->findById($session->id);

        self::assertEquals($session->id, $result->id);
        self::assertEquals($session->idUser, $result->idUser);
    }

    public function testDeleteById()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->idUser = 'iman';

        $this->sessionRepository->save($session);

        $result = $this->sessionRepository->findById($session->id);

        self::assertEquals($session->id, $result->id);
        self::assertEquals($session->idUser, $result->idUser);

        $this->sessionRepository->deleteById($session->id);

        $result = $this->sessionRepository->findById($session->id);

        self::assertNull($result);
    }

    public function testFindByIdNotFound()
    {
        $result = $this->sessionRepository->findById('notfound');

        self::assertNull($result);
    }
}

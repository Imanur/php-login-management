<?php

namespace Programmerphp\Loginmanagement\Repository;

use PHPUnit\Framework\TestCase;
use Programmerphp\Loginmanagement\Config\Database;
use Programmerphp\Loginmanagement\Domain\User;

class UserRepositoryTest extends TestCase
{
    private UserRepository $userRepository;
    protected SessionRepository $sessionRepository;

    protected function setUp(): void
    {
        $connection = Database::getConnection();
        $this->sessionRepository = new SessionRepository($connection);
        $this->userRepository = new UserRepository($connection);

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();
    }

    public function testSaveSuccess()
    {
        $user = new User();
        $user->id = "iman";
        $user->name = "Iman";
        $user->password = "rahasia";

        $this->userRepository->save($user);

        $result = $this->userRepository->findById($user->id);

        self::assertEquals($user->id, $result->id);
        self::assertEquals($user->name, $result->name);
        self::assertEquals($user->password, $result->password);
    }

    public function testFindByIdNotFound()
    {
        $user = $this->userRepository->findById("notfound");
        self::assertNull($user);
    }

    public function testUpdate()
    {
        $user = new User();
        $user->id = "iman";
        $user->name = "Iman";
        $user->password = "rahasia";

        $this->userRepository->save($user);

        $user->name = "Joko";
        $this->userRepository->update($user);

        $result = $this->userRepository->findById($user->id);

        self::assertEquals($user->id, $result->id);
        self::assertEquals($user->name, $result->name);
        self::assertEquals($user->password, $result->password);
    }
}

<?php

namespace Programmerphp\Loginmanagement\Service;

use PHPUnit\Framework\TestCase;
use Programmerphp\Loginmanagement\Config\Database;
use Programmerphp\Loginmanagement\Domain\User;
use Programmerphp\Loginmanagement\Exception\ValidationException;
use Programmerphp\Loginmanagement\Model\UserLoginRequest;
use Programmerphp\Loginmanagement\Model\UserRegisterRequest;
use Programmerphp\Loginmanagement\Model\UserUpdatePasswordRequest;
use Programmerphp\Loginmanagement\Model\UserUpdateProfileRequest;
use Programmerphp\Loginmanagement\Repository\SessionRepository;
use Programmerphp\Loginmanagement\Repository\UserRepository;

class UserServiceTest extends TestCase
{
    protected UserRepository $userRepository;
    protected UserService $userService;
    protected SessionRepository $sessionRepository;

    protected function setUp(): void
    {
        $connection = Database::getConnection();
        $this->userRepository = $this->userRepository = new UserRepository($connection);
        $this->userService = new UserService($this->userRepository);
        $this->sessionRepository = new SessionRepository($connection);

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteALl();
    }

    public function testRegisterSuccess()
    {
        $request = new UserRegisterRequest();
        $request->id = 'iman';
        $request->name = 'Iman';
        $request->password = 'rahasia';

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
        $request->id = '';
        $request->name = '';
        $request->password = '';

        $this->userService->register($request);
    }

    public function testRegisterDuplicated()
    {
        $user = new User();
        $user->id = 'iman';
        $user->name = 'Iman';
        $user->password = 'rahasia';

        $this->userRepository->save($user);

        $this->expectException(ValidationException::class);

        $request = new UserRegisterRequest();
        $request->id = 'iman';
        $request->name = 'Iman';
        $request->password = 'rahasia';

        $this->userService->register($request);
    }

    public function testLoginNotFound()
    {
        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->id = 'iman';
        $request->password = 'iman';

        $this->userService->login($request);
    }

    public function testLoginWrongPassword()
    {
        $user = new User();
        $user->id = 'iman';
        $user->name = 'Iman';
        $user->password = password_hash('iman', PASSWORD_BCRYPT);

        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->id = 'iman';
        $request->password = 'salah';

        $this->userService->login($request);
    }

    public function testLoginSuccess()
    {
        $user = new User();
        $user->id = 'iman';
        $user->name = 'Iman';
        $user->password = password_hash('iman', PASSWORD_BCRYPT);

        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->id = 'iman';
        $request->password = 'iman';

        $response = $this->userService->login($request);

        self::assertEquals($request->id, $response->user->id);
        self::assertTrue(password_verify($request->password, $response->user->password));
    }

    public function testUpdateSuccess()
    {
        $user = new User();
        $user->id = 'iman';
        $user->name = 'Iman';
        $user->password = password_hash('iman', PASSWORD_BCRYPT);

        $this->userRepository->save($user);

        $request = new UserUpdateProfileRequest();
        $request->id = 'iman';
        $request->name = 'Nur';

        $this->userService->updateProfile($request);

        $result = $this->userRepository->findById($user->id);

        self::assertEquals($request->name, $result->name);
    }

    public function testUpdateValidationError()
    {
        $this->expectException(ValidationException::class);

        $request = new UserUpdateProfileRequest();
        $request->id = '';
        $request->name = '';

        $this->userService->updateProfile($request);
    }

    public function testUpdateNotFound()
    {
        $this->expectException(ValidationException::class);

        $request = new UserUpdateProfileRequest();
        $request->id = 'iman';
        $request->name = 'Nur';

        $this->userService->updateProfile($request);
    }

    public function testUpdatePasswordSuccess()
    {
        $user = new User();
        $user->id = 'iman';
        $user->name = 'Iman';
        $user->password = password_hash('iman', PASSWORD_BCRYPT);

        $this->userRepository->save($user);

        $request = new UserUpdatePasswordRequest();
        $request->id = 'iman';
        $request->oldPassword = 'iman';
        $request->newPassword = 'nur';

        $this->userService->updatePassword($request);

        $result = $this->userRepository->findById($user->id);

        self::assertTrue(password_verify($request->newPassword, $result->password));
    }

    public function testUpdatePasswordValidationError()
    {
        $this->expectException(ValidationException::class);

        $request = new UserUpdatePasswordRequest();
        $request->id = 'iman';
        $request->oldPassword = '';
        $request->newPassword = '';

        $this->userService->updatePassword($request);
    }

    public function testUpdatePasswordWrongOldPassword()
    {
        $this->expectException(ValidationException::class);

        $user = new User();
        $user->id = 'iman';
        $user->name = 'Iman';
        $user->password = password_hash('iman', PASSWORD_BCRYPT);

        $this->userRepository->save($user);

        $request = new UserUpdatePasswordRequest();
        $request->id = 'iman';
        $request->oldPassword = 'salah';
        $request->newPassword = 'nur';

        $this->userService->updatePassword($request);
    }

    public function testUpdatePasswordNotFound()
    {
        $this->expectException(ValidationException::class);

        $request = new UserUpdatePasswordRequest();
        $request->id = 'iman';
        $request->oldPassword = 'iman';
        $request->newPassword = 'nur';

        $this->userService->updatePassword($request);
    }
}

<?php

namespace Programmerphp\Loginmanagement\Service;

use Exception;
use Programmerphp\Loginmanagement\Config\Database;
use Programmerphp\Loginmanagement\Domain\User;
use Programmerphp\Loginmanagement\Exception\ValidationException;
use Programmerphp\Loginmanagement\Model\UserLoginRequest;
use Programmerphp\Loginmanagement\Model\UserLoginResponse;
use Programmerphp\Loginmanagement\Model\UserRegisterRequest;
use Programmerphp\Loginmanagement\Model\UserRegisterResponse;
use Programmerphp\Loginmanagement\Model\UserUpdatePasswordRequest;
use Programmerphp\Loginmanagement\Model\UserUpdatePasswordResponse;
use Programmerphp\Loginmanagement\Model\UserUpdateProfileRequest;
use Programmerphp\Loginmanagement\Model\UserUpdateProfileResponse;
use Programmerphp\Loginmanagement\Repository\UserRepository;


class UserService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(UserRegisterRequest $request): UserRegisterResponse
    {
        $this->validateUserRegisterRequest($request);

        $user = $this->userRepository->findById($request->id);

        if ($user != null) {
            throw new ValidationException('User Id already exists');
        }

        try {
            Database::beginTransaction();
            $user = new User();
            $user->id = $request->id;
            $user->name = $request->name;
            $user->password = password_hash($request->password, PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $response = new UserRegisterResponse();
            $response->user = $user;

            Database::commitTransaction();
            return $response;
        } catch (Exception $exception) {
            Database::rollbackTransaction();
            throw $exception;
        }
    }

    private function validateUserRegisterRequest(UserRegisterRequest $request)
    {
        if ($request->id == null && trim($request->id) == '' && $request->name == null && trim($request->name) == '' && $request->password == null && trim($request->password) == '') {
            throw new ValidationException('Id, Name, Password can not blank');
        } else if ($request->id == null && trim($request->id) == '' && $request->name == null && trim($request->name) == '') {
            throw new ValidationException('Id, Name can not blank');
        } else if ($request->id == null && trim($request->id) == '' && $request->password == null && trim($request->password) == '') {
            throw new ValidationException('Id, Password can not blank');
        } else if ($request->name == null && trim($request->name) == '' && $request->password == null && trim($request->password) == '') {
            throw new ValidationException('Name, Password can not blank');
        } else if ($request->id == null || trim($request->id) == '') {
            throw new ValidationException('Id can not blank');
        } else if ($request->name == null || trim($request->name) == '') {
            throw new ValidationException('Name can not blank');
        } else if ($request->password == null || trim($request->password) == '') {
            throw new ValidationException('Password can not blank');
        }
    }


    public function login(UserLoginRequest $request): UserLoginResponse
    {
        $this->validateUserLoginRequest($request);

        $user = $this->userRepository->findById($request->id);

        if ($user == null) {
            throw new ValidationException("Id or password is wrong");
        }

        if (password_verify($request->password, $user->password)) {
            $response = new UserLoginResponse();
            $response->user = $user;
            return $response;
        } else {
            throw new ValidationException("Id or password is wrong");
        }
    }

    private function validateUserLoginRequest(UserLoginRequest $request)
    {
        if ($request->id == null && trim($request->id) == ''  && $request->password == null && trim($request->password) == '') {
            throw new ValidationException('Id, Password can not blank');
        } else if ($request->id == null || trim($request->id) == '') {
            throw new ValidationException('Id can not blank');
        } else if ($request->password == null || trim($request->password) == '') {
            throw new ValidationException('Password can not blank');
        }
    }

    public function updateProfile(UserUpdateProfileRequest $request): UserUpdateProfileResponse
    {
        $this->validateUserUpdateProfileRequest($request);

        try {
            Database::beginTransaction();

            $user = $this->userRepository->findById($request->id);

            if ($user == null) {
                throw new ValidationException("User is not found");
            }
            $user->name = $request->name;
            $this->userRepository->update($user);

            Database::commitTransaction();

            $response = new UserUpdateProfileResponse();
            $response->user = $user;

            return $response;
        } catch (Exception $exception) {
            Database::rollbackTransaction();
            throw $exception;
        }
    }

    private function validateUserUpdateProfileRequest(UserUpdateProfileRequest $request)
    {
        if ($request->name == null || trim($request->name) == "") {
            throw new ValidationException('Name can not blank');
        }
    }

    public function updatePassword(UserUpdatePasswordRequest $request): UserUpdatePasswordResponse
    {
        $this->validateUserUpdatePasswordRequest($request);

        try {
            Database::beginTransaction();

            $user = $this->userRepository->findById($request->id);
            if ($user == null) {
                throw new ValidationException("User is not found");
            }

            if (!password_verify($request->oldPassword, $user->password)) {
                throw new ValidationException("Old password is wrong");
            }

            $user->password = password_hash($request->newPassword, PASSWORD_BCRYPT);
            $this->userRepository->update($user);

            Database::commitTransaction();

            $respone = new UserUpdatePasswordResponse();
            $respone->user = $user;
            return $respone;
        } catch (Exception $exception) {
            Database::rollbackTransaction();
            throw $exception;
        }
    }

    private function validateUserUpdatePasswordRequest(UserUpdatePasswordRequest $request)
    {
        if ($request->oldPassword == null && trim($request->oldPassword) == '' && $request->newPassword == null && trim($request->newPassword) == '') {
            throw new ValidationException("Old password, New password can not blank");
        } elseif ($request->oldPassword == null || trim($request->oldPassword) == '') {
            throw new ValidationException("Old password can not blank");
        } elseif ($request->newPassword == null || trim($request->newPassword) == '') {
            throw new ValidationException("New password can not blank");
        }
    }
}

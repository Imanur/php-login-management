<?php

namespace Programmerphp\Loginmanagement\Controller;

use Programmerphp\Loginmanagement\Config\Database;
use Programmerphp\Loginmanagement\Config\View;
use Programmerphp\Loginmanagement\Exception\ValidationException;
use Programmerphp\Loginmanagement\Model\UserLoginRequest;
use Programmerphp\Loginmanagement\Model\UserRegisterRequest;
use Programmerphp\Loginmanagement\Model\UserUpdatePasswordRequest;
use Programmerphp\Loginmanagement\Model\UserUpdateProfileRequest;
use Programmerphp\Loginmanagement\Repository\SessionRepository;
use Programmerphp\Loginmanagement\Repository\UserRepository;
use Programmerphp\Loginmanagement\Service\SessionService;
use Programmerphp\Loginmanagement\Service\UserService;

class UserController
{

    protected UserService $userService;
    protected SessionService $sessionService;

    public function __construct()
    {
        $connection = Database::getConnection();
        $userRepository = new UserRepository($connection);
        $this->userService = new UserService($userRepository);
        $sesionRepository = new SessionRepository($connection);
        $this->sessionService = new SessionService($sesionRepository, $userRepository);
    }


    public function register()
    {
        View::render('User/register', [
            'title' => 'Register New User'
        ]);
    }

    public function postRegister()
    {
        $request = new UserRegisterRequest();
        $request->id = $_POST['id'];
        $request->name = $_POST['name'];
        $request->password = $_POST['password'];

        try {
            $this->userService->register($request);
            View::redirect('/users/login');
        } catch (ValidationException $Exception) {
            View::render('User/register', [
                'title' => 'Register New Account',
                'error' => $Exception->getMessage()
            ]);
        }
    }

    public function login()
    {
        View::render('User/login', [
            'title' => 'Login User'
        ]);
    }

    public function postLogin()
    {
        $request = new UserLoginRequest();
        $request->id = $_POST['id'];
        $request->password = $_POST['password'];

        try {
            $response = $this->userService->login($request);

            $this->sessionService->create($response->user->id);

            View::redirect('/');
        } catch (ValidationException $Exception) {
            View::render('User/login', [
                'title' => 'Login User',
                'error' => $Exception->getMessage()
            ]);
        }
    }

    public function logout()
    {
        $this->sessionService->destroy();
        View::redirect('/');
    }

    public function updateProfile()
    {
        $user = $this->sessionService->current();

        View::render('User/profile', [
            'title' => 'Update User Profile',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ]
        ]);
    }

    public function postUpdateProfile()
    {
        $user = $this->sessionService->current();

        $request = new UserUpdateProfileRequest();
        $request->id = $user->id;
        $request->name = $_POST['name'];

        try {
            $this->userService->updateProfile($request);
            View::redirect('/');
        } catch (ValidationException $Exception) {
            View::render('User/profile', [
                'title' => 'Update User Profile',
                'error' => $Exception->getMessage(),
                'user' => [
                    'id' => $user->id,
                    'name' => $_POST['name']
                ]
            ]);
        }
    }

    public function updatePassword()
    {
        $user = $this->sessionService->current();
        View::render('User/password', [
            'title' => 'Update User Password',
            'user' => [
                'id' => $user->id
            ]
        ]);
    }

    public function postUpdatePassword()
    {
        $user = $this->sessionService->current();

        $request = new UserUpdatePasswordRequest();
        $request->id = $user->id;
        $request->oldPassword = $_POST['oldPassword'];
        $request->newPassword = $_POST['newPassword'];

        try {
            $this->userService->updatePassword($request);
            View::redirect('/');
        } catch (ValidationException $Exception) {
            View::render('User/password', [
                'title' => 'Update User Password',
                'error' => $Exception->getMessage(),
                'user' => [
                    'id' => $user->id
                ]
            ]);
        }
    }
}

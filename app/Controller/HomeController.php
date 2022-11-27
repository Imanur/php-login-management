<?php

namespace Programmerphp\Loginmanagement\Controller;

use Programmerphp\Loginmanagement\Config\Database;
use Programmerphp\Loginmanagement\Config\View;
use Programmerphp\Loginmanagement\Repository\SessionRepository;
use Programmerphp\Loginmanagement\Repository\UserRepository;
use Programmerphp\Loginmanagement\Service\SessionService;

class HomeController
{

    protected SessionService $sessionService;

    public function __construct()
    {
        $connection = Database::getConnection();
        $sessionRepository = new SessionRepository($connection);
        $userRepository = new UserRepository($connection);
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }

    function index()
    {
        $user = $this->sessionService->current();
        if ($user == null) {
            View::render('Home/index', [
                'title' => 'PHP Login Management'
            ]);
        } else {
            View::render('Home/dashboard', [
                'title' => 'Dashboard',
                'user' => [
                    'name' => $user->name
                ]
            ]);
        }
    }
}

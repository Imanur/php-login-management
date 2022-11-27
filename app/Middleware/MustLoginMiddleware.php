<?php

namespace Programmerphp\Loginmanagement\Middleware;

use Programmerphp\Loginmanagement\Config\Database;
use Programmerphp\Loginmanagement\Config\View;
use Programmerphp\Loginmanagement\Repository\SessionRepository;
use Programmerphp\Loginmanagement\Repository\UserRepository;
use Programmerphp\Loginmanagement\Service\SessionService;

class MustLoginMiddleware implements Middleware
{

    protected SessionService $sessionService;

    public function __construct()
    {
        $connection = Database::getConnection();
        $sessionRepository = new SessionRepository($connection);
        $userRepository = new UserRepository($connection);
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }

    function before(): void
    {
        $user = $this->sessionService->current();
        if ($user == null) {
            View::redirect('/users/login');
        }
    }
}

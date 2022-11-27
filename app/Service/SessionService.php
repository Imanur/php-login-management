<?php

namespace Programmerphp\Loginmanagement\Service;

use Programmerphp\Loginmanagement\Config\Database;
use Programmerphp\Loginmanagement\Domain\Session;
use Programmerphp\Loginmanagement\Domain\User;
use Programmerphp\Loginmanagement\Repository\SessionRepository;
use Programmerphp\Loginmanagement\Repository\UserRepository;

class SessionService
{
    public static string $COOKIE_NAME = "X-PHP-SESSION";

    protected SessionRepository $sessionRepository;
    protected UserRepository $userRepository;

    public function __construct(SessionRepository $sessionRepository, UserRepository $userRepository)
    {
        $this->sessionRepository = $sessionRepository;
        $this->userRepository = $userRepository;
    }

    public function create(string $idUser): Session
    {
        try {
            Database::beginTransaction();
            $session = new Session();
            $session->id =  uniqid();
            $session->idUser = $idUser;

            $this->sessionRepository->save($session);

            setcookie(self::$COOKIE_NAME, $session->id, time() + (60 * 60 * 24 * 30), "/");

            Database::commitTransaction();
            return $session;
        } catch (\Exception $exception) {
            Database::rollbackTransaction();
            throw $exception;
        }
    }

    public function destroy()
    {
        $sessionId = $_COOKIE[self::$COOKIE_NAME] ?? '';
        $this->sessionRepository->deleteById($sessionId);

        setcookie(self::$COOKIE_NAME, '', 1, "/");
    }

    public function current(): ?User
    {
        $sessionId = $_COOKIE[self::$COOKIE_NAME] ?? '';
        $session = $this->sessionRepository->findById($sessionId);

        if ($session == null) {
            return null;
        }

        return $this->userRepository->findById($session->idUser);
    }
}

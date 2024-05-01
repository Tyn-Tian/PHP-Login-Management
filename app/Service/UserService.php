<?php

namespace LoginManagement\Service;

use Exception;
use LoginManagement\Config\Database;
use LoginManagement\Domain\User;
use LoginManagement\Exception\ValidationException;
use LoginManagement\Model\UserRegisterRequest;
use LoginManagement\Model\UserRegisterResponse;
use LoginManagement\Repository\UserRepository;

class UserService
{

    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function register(UserRegisterRequest $request): UserRegisterResponse
    {
        $this->validateUserRegistrationRequest($request);

        try {
            Database::beginTransaction();
            $user = $this->userRepository->findById($request->id);

            if ($user != null) {
                throw new ValidationException("User Id already exists");
            }

            $user = new User();
            $user->id = $request->id;
            $user->name = $request->name;
            $user->password = password_hash($request->password, PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $response = new UserRegisterResponse();
            $response->user = $user;

            Database::commitTransaction();
            return $response;
        } catch(\Exception $exception) {
            Database::rollBackTransaction();
            throw $exception;
        }
    }

    public function validateUserRegistrationRequest(UserRegisterRequest $request)
    {
        if (
            $request->id == null || $request->name == null || $request->password == null ||
            trim($request->id) == "" || trim($request->name) == "" || trim($request->password) == ""
        ) {
            throw new ValidationException("Id, Name, Password cannot blank");
        }
    }
}

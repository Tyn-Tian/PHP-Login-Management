<?php

namespace LoginManagement\Service;

use Exception;
use LoginManagement\Config\Database;
use LoginManagement\Domain\User;
use LoginManagement\Exception\ValidationException;
use LoginManagement\Model\UserLoginRequest;
use LoginManagement\Model\UserLoginResponse;
use LoginManagement\Model\UserProfileUpdateRequest;
use LoginManagement\Model\UserProfileUpdateResponse;
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
        } catch (\Exception $exception) {
            Database::rollBackTransaction();
            throw $exception;
        }
    }

    private function validateUserRegistrationRequest(UserRegisterRequest $request)
    {
        if (
            $request->id == null || $request->name == null || $request->password == null ||
            trim($request->id) == "" || trim($request->name) == "" || trim($request->password) == ""
        ) {
            throw new ValidationException("Id, Name, Password cannot blank");
        }
    }

    public function login(UserLoginRequest $request)
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
        if (
            $request->id == null || $request->password == null ||
            trim($request->id) == "" || trim($request->password) == ""
        ) {
            throw new ValidationException("Id and Password cannot blank");
        }
    }

    public function updateProfile(UserProfileUpdateRequest $request): UserProfileUpdateResponse
    {
        $this->validateUserProfileUpdateRequest($request);

        try {
            Database::beginTransaction();

            $user = $this->userRepository->findById($request->id);

            if ($user == null) {
                throw new ValidationException("User is not found");
            }

            $user->name = $request->name;
            $this->userRepository->update($user);

            Database::commitTransaction();

            $response = new UserProfileUpdateResponse();
            $response->user = $user;
            return $response;
        } catch (\Exception $exception) {
            Database::rollBackTransaction();
            throw $exception;
        }
    }

    private function validateUserProfileUpdateRequest(UserProfileUpdateRequest $request)
    {
        if ($request->name == null || trim($request->name) == "") {
            throw new ValidationException("Name cannot blank");
        }
    }
}

<?php

use LoginManagement\Domain\User;

class UserRepository
{
    public function __construct(
        private \PDO $connection
    ) {
    }

    public function save(User $user): User 
    {
        $statement = $this->connection->prepare("INSERT INTO users(id, name, password) VALUES(?, ?, ?)");
        $statement->execute([
            $user->id,
            $user->name,
            $user->password,
        ]);
        return $user;
    }
}

<?php

namespace App\Mapper\Entity;

use App\Entity\User;
use App\Model\Response\Entity\User\UserDetail;

class UserMapper
{
    public function mapToDetail(User $user, UserDetail $model): UserDetail
    {
        return $model
            ->setId($user->getId())
            ->setUsername($user->getUsername())
            ->setEmail($user->getEmail())
            ->setAbout($user->getAbout())
            ->setAge($user->getAge())
            ->setDisplayName($user->getDisplayName())
            ->setAvatar($user->getAvatar())
            ->setLastLogin($user->getLastLogin())
            ->setRoles($user->getRoles())
        ;
    }
}

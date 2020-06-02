<?php

declare(strict_types = 1);

namespace Tests\Unit;

class User
{
    protected $name;

    protected $password;

    /**
     * @type object
     * @class Tests\Unit\UserInfo
     */
    protected $user_info;

    public function getUserInfo()
    {
        return $this->user_info;
    }
}

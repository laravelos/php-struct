<?php

declare(strict_types = 1);

namespace Tests\Unit;

use Laravelos\Struct;

class StructClass extends Struct
{
    protected $name;

    protected $password;

    /**
     * @type object
     * @class Tests\Unit\User
     */
    protected $user;
}

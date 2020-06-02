<?php

declare(strict_types = 1);

namespace Tests\Unit;

use Laravelos\Struct;

class UserMainInfo extends Struct
{
    protected $name;

    protected $set_name;

    /**
     * @type arrayObject
     * @class Tests\Unit\StructClass
     */
    protected $array_object;

    /**
     * @type object
     * @class Tests\Unit\StructClass
     */
    protected $object;

    /**
     * @type object
     * @class Tests\Unit\User
     */
    protected $user;

    /**
     * @type object
     * @class Tests\Unit\User
     */
    protected $user_id;

    /**
     * @type array
     * @column name,1111,2222,333333
     */
    protected $array;

    /**
     * summary
     *
     * @author
     */
    public function setSetName($val)
    {
        $this->set_name = $val . '_set';
    }
}

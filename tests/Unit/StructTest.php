<?php

declare(strict_types = 1);

namespace Tests\Unit;

use Laravelos\Struct;
use PHPUnit\Framework\TestCase;

class StructTest extends TestCase
{
    public function testMutatorKey()
    {
        $userMainInfo = new UserMainInfo();
        $key          = $userMainInfo->getMutatorKey('name_Name_n2');
        $this->assertEquals('NameNameN2', $key);
    }

    public function testSet()
    {
        $data                        = $this->getTestData();
        $userMainInfo                = new UserMainInfo();
        $userMainInfo->name          = $data['name'];
        $userMainInfo->name_no_exist = $data['name_no_exist'];
        $userMainInfo->set_name      = $data['set_name'];
        $userMainInfo->array         = $data['array'];
        $userMainInfo->array_object  = $data['array_object'];
        $userMainInfo->object        = $data['object'];
        $userMainInfo->user          = $data['user'];
        $this->assert($userMainInfo);
    }

    public function testCreate()
    {
        $data          = $this->getTestData();
        $userMainInfo  = new UserMainInfo($data);
        $this->assert($userMainInfo);
    }

    public function testArrayType()
    {
        $data                  = $this->getTestData();
        $data['user_id']       = 'string';

        try {
            $userMainInfo  = new UserMainInfo($data);
            $this->assertFalse(true);
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse(false);
        }
    }

    public function testObjectToArray()
    {
        $data             = $this->getTestData();
        $userMainInfo     = new UserMainInfo($data);
        $datanew          = $userMainInfo->toArray();

        $this->assert($userMainInfo);
    }

    public function assert($userMainInfo)
    {
        $this->assertEquals(1, $userMainInfo->name);
        $this->assertEquals(null, $userMainInfo->name_no_exist);
        $this->assertEquals('set_name_set', $userMainInfo->set_name);
        $this->assertEquals('foo', $userMainInfo->array['name']);
        $this->assertEquals(1, count($userMainInfo->array_object));
        $this->assertEquals(StructClass::class, get_class($userMainInfo->array_object[0]));
        $this->assertEquals(StructClass::class, get_class($userMainInfo->object));
        $this->assertEquals(User::class, get_class($userMainInfo->object->user));
        $this->assertEquals(UserInfo::class, get_class($userMainInfo->object->user->getUserInfo()));
        $this->assertFalse(isset($userMainInfo->array['age']));
    }

    public function getTestData()
    {
        $user                  = ['name'=>'bar', 'password'=>'123456', 'user_info'=>['age'=>18]];
        $data['name']          = 1;
        $data['name_no_exist'] = 1;
        $data['set_name']      = 'set_name';
        $data['array']         = ['name'=>'foo', 'age'=>18];
        $data['array_object']  = ['name'=>'object', 'password'=>'123456', 'user'=>$user];
        $data['object']        = ['name'=>'object', 'password'=>'123456', 'user'=>$user];
        $data['user']          = $user;

        return $data;
    }
}

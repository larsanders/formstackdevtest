<?php
//require_once 'src/autoload.php';
include '/vagrant/src/UserModel.php';
//use PHPUnit\Framework\TestCase;

class UserModelTest extends PHPUnit_Framework_TestCase
{
    protected static function getMethod($name) 
    {
        $class = new ReflectionClass('UserModel');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testCreateUserReturnsFalseWithEmptyParamsArray()
    {
        $db = new stdClass;
        $um = new UserModel($db);
        $params = [];
        $this->assertEquals(false, $um->createUser($params));
    }

    public function testValidateParamsWithBadEmailAddress()
    {
        $m = self::getMethod('validateParams');
        $db = new stdClass;
        $um = new UserModel($db);
        $params = ['email' => 'a'];
        $this->assertEquals(false, $m->invokeArgs($um, [$params]));
    }

    public function testValidateParamsWithGoodEmailAddress()
    {
        $m = self::getMethod('validateParams');
        $db = new stdClass;
        $um = new UserModel($db);
        $params = ['email' => 'a@b.cd'];
        $this->assertEquals(true, $m->invokeArgs($um, [$params]));
    }

}

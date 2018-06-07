<?php


namespace HuanL\Verify\Tests;

use HuanL\Verify\ICheckDataObject;
use HuanL\Verify\MathHelp;
use HuanL\Verify\Rule;
use HuanL\Verify\Verify;
use PHPUnit\Framework\TestCase;

require_once './../src/Verify.php';
require_once './../src/Rule.php';
require_once './../src/MathHelp.php';

class VerifyTest extends TestCase {

    /**
     * @var Verify
     */
    protected $verify = null;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */ {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->verify = new Verify();
    }

    public function testMultiRule() {
        $this->verify->addCheckData([
            'empty' => '123', 'user' => 'aaa'
        ]);
        $this->verify->addCheckRule([
            'empty' => ['empty' => [false, '233']],
            'user' => ['empty' => false]
        ]);
        self::assertEquals($this->verify->check(), true);
        self::assertEquals(sizeof($this->verify->getErrorList()), 0);
        self::assertEquals($this->verify->getLastError(), null);
    }

    public function testMultiRule1() {
        $this->verify->addCheckData([
            'empty' => '', 'user' => 'aaa'
        ]);
        $this->verify->addCheckRule([
            'empty' => ['empty' => [false, '233']],
            'user' => ['empty' => false]
        ]);
        self::assertEquals($this->verify->check(), false);
        self::assertEquals(sizeof($this->verify->getErrorList()), 1);
        self::assertEquals($this->verify->getLastError(), '233');
    }

    public function testMultiRule2() {
        $this->verify->addCheckData([
            'empty' => '', 'user' => ''
        ]);
        $this->verify->addCheckRule([
            'empty' => ['empty' => [false, '233']],
            'user' => ['empty' => false]
        ]);
        self::assertEquals($this->verify->check(false), false);
        self::assertEquals(sizeof($this->verify->getErrorList()), 2);
        self::assertEquals($this->verify->getLastError(), '233');
        self::assertEquals($this->verify->getLastError(), 'user不能为空');
    }

    public function testEmpty() {
        $this->verify->addCheckData('emp', '');
        $this->verify->addCheckRule('emp')->empty();
        self::assertEquals($this->verify->check(), true);
    }

    public function testEmpty1() {
        $a = new Rule();
        $this->verify->addCheckRule('emp')->empty(false, function () use ($a) {
            return $a;
        });
        self::assertEquals($this->verify->check(), false);
        self::assertEquals($this->verify->getLastError(), $a);
    }

    public function testEmpty2() {
        $this->verify->addCheckData('emp', '');
        $this->verify->addCheckRule('emp')->empty(false, '错误信息');
        self::assertEquals($this->verify->check(), false);
        self::assertEquals($this->verify->getLastError(), '错误信息');
    }

    public function testEmpty3() {
        $this->verify->addCheckData('emp', '123');
        $this->verify->addCheckRule('emp')->empty(false);
        self::assertEquals($this->verify->check(), true);
    }

    public function testEmpty4() {
        $this->verify->addCheckRule('emp')->empty();
        self::assertEquals($this->verify->check(), true);
    }

    public function testUserReg() {
        $this->verify->addCheckData([
            'user' => '幻令',
            'pwd' => '密码123456',
            'again' => '密码123456',
            'email' => 'code.farmer@qq.com'
        ]);
        $this->verify->addCheckRule([
            'user:用户名' => ['length' => [[2, 8]], 'func' => function ($user) {
                if ($user == '幻令') return '已经被注册过了';
                return true;
            }],
            'pwd:密码' => ['length' => [[6, 16]], 'regex' => MathHelp::PASSWORD],
            'again:再输入一次' => ['equal' => [':pwd', '两次密码不相等']],
            'email:邮箱' => ['regex' => MathHelp::EMAIL]
        ]);
        self::assertEquals($this->verify->check(false), false);
        self::assertEquals($this->verify->getLastError(), '已经被注册过了');
        self::assertEquals($this->verify->getLastError(), '密码不符合格式');
        $this->verify->setCheckData('user', 'qwe123');
        self::assertEquals($this->verify->check(true), false);
        self::assertEquals($this->verify->getLastError(), '密码不符合格式');
        $this->verify->setCheckData('pwd', 'qwe123');
        self::assertEquals($this->verify->check(true), false);
        self::assertEquals($this->verify->getLastError(), '两次密码不相等');
        $this->verify->setCheckData('again', 'qwe123');
        self::assertEquals($this->verify->check(true), true);
    }

    public function testObject() {
        $obj = new testObj();
        $this->verify->bindObject($obj);
        $this->verify->addCheckRule([
            'user' => ['length' => [[3, 8], '长度不正确']]
        ]);
        $obj->user = "23113";
        self::assertEquals($this->verify->check(true), true);
        $obj->user = "231切2的13";
        self::assertEquals($this->verify->check(true), false);
    }
}

class testObj implements ICheckDataObject {

    public $user;

    public function setCheckData($key, string $val = ''): ICheckDataObject {
        // TODO: Implement setCheckData() method.
        $this->$key = $val;
        return $this;
    }

    public function getCheckData($key): string {
        // TODO: Implement getCheckData() method.
        return $this->$key;
    }
}


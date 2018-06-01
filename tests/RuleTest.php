<?php


namespace HuanL\Verify\Tests;

use HuanL\Verify\MathHelp;
use HuanL\Verify\Rule;
use HuanL\Verify\Verify;
use PHPUnit\Framework\TestCase;

require_once './../src/Verify.php';
require_once './../src/Rule.php';
require_once './../src/MathHelp.php';

class RuleTest extends TestCase {
    /** @var Rule */
    protected $rule;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */ {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->rule = new Rule();
    }

    public function testRegex() {
        $msg = '不是整数';
        $this->rule->regex('/^[0-9]+$/', $msg);
        self::assertEquals($this->rule->check('991a3'), false);
        self::assertEquals($this->rule->getErrorMsg(), '不是整数');
        self::assertEquals($this->rule->check('94.651'), false);
        self::assertEquals($this->rule->check('9913'), true);
        $msg = '不是数字';
        $this->rule->regex(MathHelp::DIGITAL, $msg);
        self::assertEquals($this->rule->check('991a3'), false);
        self::assertEquals($this->rule->getErrorMsg(), '不是数字');
        self::assertEquals($this->rule->check('94.651'), true);
        self::assertEquals($this->rule->check('991.3'), true);
        self::assertEquals($this->rule->check('0'), true);
        self::assertEquals($this->rule->check('9.913'), true);
        self::assertEquals($this->rule->check('9.91.3'), false);
    }

    public function testLength() {
        $msg = '测试不符合长度要求';
        $this->rule->alias('测试')->length(6);
        self::assertEquals($this->rule->check('12313'), true);
        self::assertEquals($this->rule->check('123sd123124'), false);
        self::assertEquals($this->rule->getErrorMsg(), $msg);
        $this->rule->length([6, 12], $msg);
        self::assertEquals($this->rule->check('12313'), false);
        self::assertEquals($this->rule->getErrorMsg(), $msg);
        self::assertEquals($this->rule->check('123sd123124'), true);
    }

    public function testRange() {
        $this->rule->range([100, 1000]);
        self::assertEquals($this->rule->check(10), false);
        self::assertEquals($this->rule->check(1001), false);
        self::assertEquals($this->rule->check(984), true);
        self::assertEquals($this->rule->getErrorMsg(), '不在范围内');
    }

    public function testIn() {
        $this->rule->in([12, '哈哈', '略略略', '鹅妈妈']);
        self::assertEquals($this->rule->check('鹅妈妈'), true);
        self::assertEquals($this->rule->check('二重'), false);
        self::assertEquals($this->rule->check('略略略'), true);
        self::assertEquals($this->rule->check('xxx'), false);
    }

    public function testEqual() {
        $v = new Verify();
        $v->addData(['pwd' => 'qwe123', 'ag' => 'qwe123']);
        $this->rule = new Rule($v);
        $this->rule->equal(':ag', '两次输入的密码不同');
        self::assertEquals($this->rule->check('qwe123'), true);
        $v->setCheckData('ag', 'qqqqqqq');
        self::assertEquals($this->rule->check('qwe123'), false);
        self::assertEquals($this->rule->getErrorMsg(), '两次输入的密码不同');
    }

    public function testFunc() {
        $v = new Verify();
        $v->addData(['pwd' => 'qwe123', 'ag' => 'qwe123']);
        $this->rule = new Rule($v);
        $this->rule->func(function ($data, Rule $rule) {
            if ($data === $rule->getParam(':ag')) {
                return true;
            }
            return '密码不同';
        }, '两次输入的密码不同');
        self::assertEquals($this->rule->check('qwe123'), true);
        $v->setCheckData('ag', 'qqqqqqq');
        self::assertEquals($this->rule->check('qwe123'), false);
        self::assertEquals($this->rule->getErrorMsg(), '密码不同');
    }
}
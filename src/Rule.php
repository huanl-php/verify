<?php


namespace HuanL\Verify;


class Rule {

    /**
     * 数据
     * @var string
     */
    protected $data = '';

    /**
     * 规则
     * @var array
     */
    protected $rule = [];

    /**
     * 错误信息
     * @var array
     */
    protected $errorMsg = [];

    /**
     * 校验数据的错误信息
     * @var array
     */
    protected $checkErrorMsg = '';

    public function __construct($rule) {
        $this->rule = $rule;
    }

    /**
     * 验证数据
     * @param $data
     * @return bool
     */
    public function check($data = ''): bool {
        $this->data = $data;
        //先验证数据是否为空和规则是否允许空
        if (!$this->checkEmpty()) {
            //如果为空就不继续往下执行了
            return false;
        }
        return true;
    }

    /**
     * 验证数据是否为空
     * @return bool
     */
    protected function checkEmpty(): bool {
        if (empty($data) && !($this->rule['empty'] ?? false)) {
            $this->checkErrorMsg = 'empty';
            return false;
        }
        return true;
    }

    /**
     * 是否允许为空
     * @param bool $allow
     * @param string $msg
     * @return Rule
     */
    public function empty($allow = true, $msg = ''): Rule {
        return $this->rule('empty', $allow, $msg);
    }

    /**
     * 添加一个规则
     * @param string $rule
     * @param $judge
     * @param $msg
     * @return Rule
     */
    public function rule(string $rule, $judge, $msg): Rule {
        $this->rule[$rule] = $judge;
        $this->errorMsg[$rule] = $msg;
        return $this;
    }

    /**
     * 获取错误信息
     * @return mixed
     */
    public function getErrorMsg() {
        $error = $this->errorMsg[$this->checkErrorMsg];
        if ($error instanceof \Closure) {
            //匿名函数
            return $error($data, $this);
        }
        return $error;
    }
}
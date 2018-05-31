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
     * 标签
     * @var string
     */
    protected $label = '';

    /**
     * 别名
     * @var string
     */
    protected $alias = '';

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

    public function __construct($rule = [], $label = '') {
        $this->label = $label;
        //对规则进行处理
        foreach ($rule as $key => $item) {
            if (!is_array($item)) $item = [$item];
            call_user_func_array([$this, $key], $item);
        }
    }

    /**
     * 验证数据
     * @param string $data
     * @return bool
     */
    public function check(string $data = ''): bool {
        $this->data = $data;
        //先验证数据是否为空和规则是否允许空
        if (!$this->checkEmpty()) {
            //如果为空就不继续往下执行了
            $this->checkErrorMsg = 'empty';
            return false;
        } else if (!$this->checkRegex()) {
            $this->checkErrorMsg = 'regex';
            return false;
        } else if (!$this->checkLength()) {
            $this->checkErrorMsg = 'length';
            return false;
        }
        return true;
    }

    /**
     * 添加正则验证
     * @param string $regex
     * @param $msg
     * @return Rule
     */
    public function regex(string $regex = '', $msg = ''): Rule {
        if (empty($msg)) {
            $msg = $this->alias() . '不符合格式';
        }
        return $this->rule('regex', $regex, $msg);
    }

    public function length($length, $msg = '') {
        if (empty($msg)) {
            $msg = $this->alias . '不符合长度要求';
        }
        return $this->rule('length', $length, $msg);
    }

    /**
     * 验证文本长度
     * @return bool
     */
    protected function checkLength(): bool {
        if ($this->issetRule('length')) {
            $len = strlen($this->data);
            if (is_array($this->rule['length'])) {
                //数组的为范围 [min,max]
                if ($len < $this->rule['length'][0]) {
                    //比min要小
                    return false;
                } else if ($len > $this->rule[$this->length()][1]) {
                    //比max要大
                    return false;
                }
                return true;
            } else if ($len > $this->rule['length']) {
                return false;
            }
        }
        return true;
    }

    /**
     * 规则是否设置
     * @param $rule
     * @return bool
     */
    public function issetRule($rule) {
        return isset($this->rule[$rule]);
    }

    /**
     * 验证正则
     * @return bool
     */
    protected function checkRegex(): bool {
        if ($this->issetRule('regex')) {
            if (preg_match($this->rule['regex'], $this->data)) {
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * 验证数据是否为空
     * @return bool
     */
    protected function checkEmpty(): bool {
        if ($this->data == '' && !($this->rule['empty'] ?? false)) {
            return false;
        }
        return true;
    }

    /**
     * 设置/返回 别名
     * @param string $alias
     * @return $this|string
     */
    public function alias($alias = '') {
        if (empty($alias)) return $this->label;
        $this->alias = $alias;
        return $this;
    }

    /**
     * 是否允许为空
     * @param bool $allow
     * @param $msg
     * @return Rule
     */
    public function empty($allow = true, $msg = ''): Rule {
        if (empty($msg)) {
            $msg = $this->alias() . '不能为空';
        }
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
            return $error($this->data, $this);
        }
        return $error;
    }

    /**
     * 添加
     * @param string $label
     * @return Rule
     */
    public function label(string $label): Rule {
        $this->label = $label;
        return $this;
    }
}
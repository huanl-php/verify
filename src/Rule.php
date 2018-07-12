<?php


namespace HuanL\Verify;


use HuanL\Container\Container;
use function Sodium\add;

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

    /**
     * 验证类
     * @var Verify
     */
    protected $verify = null;

    public function __construct($rule = [], $label = '', Verify $verify = null) {
        if (func_num_args() == 1) {
            $this->verify = $rule;
        } else {
            $this->label = $label;
            $this->verify = $verify;
            //对规则进行处理
            $this->addRule($rule);
        }
    }

    /**
     * 添加规则
     * @param $key
     * @param array $rule
     * @return Rule
     */
    public function addRule($key, array $rule = []): Rule {
        if (is_array($key)) {
            foreach ($key as $k => $item) {
                if (!is_array($item)) $item = [$item];
                call_user_func_array([$this, $k], $item);
            }
        } else {
            call_user_func_array([$this, $key], $rule);
        }
        return $this;
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
        }
        $emptyRule = null;
        if ($this->issetRule('empty')) {
            $emptyRule = $this->rule['empty'];
            unset($this->rule['empty']);
        }
        foreach ($this->rule as $key => $value) {
            if (!call_user_func_array([$this, 'check' . $key], [])) {
                $this->checkErrorMsg = $key;
                return false;
            }
        }
        if (!is_null($emptyRule)) {
            $this->rule['empty'] = $emptyRule;
        }
        return true;
    }

    /**
     * 相等
     * @param string $val
     * @param string $msg
     * @return Rule
     */
    public function equal(string $val, $msg = ''): Rule {
        $this->defaultMsg($msg, '{$alias}不相等');
        return $this->rule('equal', $val, $msg);
    }

    /**
     * 相等验证
     * @return bool
     */
    protected function checkEqual(): bool {
        if ($this->issetRule('equal')) {
            return $this->data === $this->getParam($this->rule['equal']);
        }
        return true;
    }

    /**
     * 正则验证
     * @param string $regex
     * @param $msg
     * @return Rule
     */
    public function regex(string $regex, $msg = ''): Rule {
        $this->defaultMsg($msg, '{$alias}不符合格式');
        return $this->rule('regex', $regex, $msg);
    }

    /**
     * 自定义方法
     * @param $func
     * @param $msg
     * @return Rule
     */
    public function func($func, $msg = ''): Rule {
        $this->defaultMsg($msg, '{$alias}验证没有通过');
        return $this->rule('func', $func, $msg);
    }

    /**
     * 验证自定义方法
     * @return bool
     */
    protected function checkFunc(): bool {
        if ($this->issetRule('func')) {
            $ret = call_user_func_array($this->rule['func'], [$this->data, $this]);
            if (is_string($ret)) {
                $this->errorMsg['func'] = $ret;
                return false;
            }
            return $ret;
        }
        return true;
    }

    /**
     * 数字范围
     * @param $range
     * @param string $msg
     * @return Rule
     */
    public function range($range, $msg = ''): Rule {
        if (is_string($range)) {
            if (strpos($range, ',')) {
                $range = explode(',', $range);
            }
        }
        $this->defaultMsg($msg, '{$alias}不在范围内');
        return $this->rule('range', $range, $msg);
    }

    /**
     * 验证数字范围
     * @return bool
     */
    protected function checkRange(): bool {
        if ($this->issetRule('range')) {
            return $this->inRange($this->data, $this->rule['range']);
        }
        return true;
    }

    /**
     * 在其中
     * @param array $in
     * @param string $msg
     * @return Rule
     */
    public function in(array $in, $msg = ''): Rule {
        $this->defaultMsg($msg, '{$alias}不在选项中');
        return $this->rule('in', $in, $msg);
    }

    /**
     * 验证在其中
     * @return bool
     */
    public function checkIn(): bool {
        if ($this->issetRule('in')) {
            return in_array($this->data, $this->rule['in']);
        }
        return true;
    }

    /**
     * 默认的信息
     * @param string $msg
     * @param string $default
     * @return mixed
     */
    protected function defaultMsg(&$msg, string $default = '') {
        if (empty($msg)) $msg = $default;
        return $msg;
    }

    /**
     * 文本长度
     * @param $length
     * @param string $msg
     * @return Rule
     */
    public function length($length, $msg = '') {
        if (is_string($length)) {
            if (strpos($length, ',')) {
                $length = explode(',', $length);
            }
        }
        $this->defaultMsg($msg, '{$alias}不符合长度要求');
        return $this->rule('length', $length, $msg);
    }

    /**
     * 验证文本长度
     * @return bool
     */
    protected function checkLength(): bool {
        if ($this->issetRule('length')) {
            $len = strlen($this->data);
            return $this->inRange($len, $this->rule['length']);
        }
        return true;
    }

    /**
     * 是否在范围内
     * @param $size
     * @param $range
     * @return bool
     */
    protected function inRange($size, $range): bool {
        if (is_array($range)) {
            //数组的为范围 [min,max]
            if ($size < $range[0]) {
                //比min要小
                return false;
            } else if ($size > $range[1]) {
                //比max要大
                return false;
            }
            return true;
        } else if ($size > $range) {
            return false;
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
        if (empty($alias)) {
            return $this->alias ?: $this->label;
        }
        $this->alias = $alias;
        return $this;
    }

    /**
     * 是否允许为空
     * @param $allow
     * @param $msg
     * @return Rule
     */
    public function empty($allow = true, $msg = ''): Rule {
        if (func_num_args() == 1) {
            $msg = $allow;
            $allow = false;
        }
        $this->defaultMsg($msg, '{$alias}不能为空');
        return $this->rule('empty', $allow, $msg);
    }

    /**
     * 从Verify中获取参数
     * @param $name
     * @return mixed
     */
    public function getParam($name) {
        if (substr($name, 0, 1) === ':') {
            return $this->verify->getCheckData(substr($name, 1));
        }
        return $name;
    }

    /**
     * 添加一个规则
     * @param string $rule
     * @param $judge
     * @param $msg
     * @return Rule
     */
    protected function rule(string $rule, $judge, $msg): Rule {
        $this->rule[$rule] = $judge;
        $this->errorMsg[$rule] = $msg;
        return $this;
    }

    /**
     * 获取规则
     * @return array
     */
    public function getRule(): array {
        //合并错误信息和规则数组
        $ret = [];
        foreach ($this->rule as $key => $value) {
            $ret[$key] = [$value, $this->errorMsg[$key] ?? ''];
        }
        return $ret;
    }

    /**
     * 设置规则
     * @param array $rule
     */
    public function setRule(array $rule): Rule {
        $this->rule = $rule;
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
            return str_replace($error($this->data, $this), $this->alias(), $error);;
        }
        $error = str_replace('{$alias}', $this->alias(), $error);
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

    /**
     * 获取标签
     * @return string
     */
    public function getLabel(): string {
        return $this->label;
    }
}
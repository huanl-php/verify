<?php


namespace HuanL\Verify;


class Verify {

    /**
     * 验证的数据
     * @var array
     */
    private $checkData = [];

    /**
     * 验证的规则
     * @var array
     */
    private $checkRules = [];

    /**
     * 错误列表数组栈
     * @var array
     */
    private $errorStack = [];

    /**
     * Verify constructor.
     * @param array $data
     */
    public function __construct(array $data = [], array $rules = []) {
        $this->checkData = $data;
        $this->checkRules = $rules;
    }

    /**
     * 添加数据
     * @param  $key
     * @param $data
     * @return Verify
     */
    public function addData($key, $data = ''): Verify {
        if (is_array($key)) {
            $this->checkData = array_merge($this->checkData, $key);
        } else {
            $this->checkData[$key] = $data;
        }
        return $this;
    }

    /**
     * 添加规则
     * @param  $key
     * @param array $rule
     * @return Rule|Verify
     */
    public function addRule($key, array $rule = []) {
        if (is_array($key)) {
            foreach ($key as $k => $item) {
                $this->addRule($k, $item);
            }
            return $this;
        } else {
            $this->checkRules[$key] = (new Rule($rule, $key));
            return $this->checkRules[$key];
        }
    }

    /**
     * 对数据进行验证,当$meet为true时,遇到错误就直接返回不对后面的在判断
     * @param bool $meet
     * @return bool
     */
    public function check($meet = true): bool {
        $retState = true;
        //遍历规则
        foreach ($this->checkRules as $key => $item) {
            if (!$item->check($this->checkData[$key] ?? '')) {
                array_push($this->errorStack, $item);
                $retState = false;
                if ($meet) return false;
            }
        }
        return $retState;
    }

    /**
     * 获取错误列表
     * @return array
     */
    public function getErrorList(): array {
        return $this->errorStack;
    }

    /**
     * 获取上一个错误
     * @return mixed
     */
    public function getLastError() {
        /** @var Rule $error */
        $error = array_pop($this->errorStack);
        return is_null($error) ? null : $error->getErrorMsg();
    }

    /**
     * 获取上一个错误规则对象
     * @return mixed
     */
    public function getLastErrorRule(): Rule {
        /** @var Rule $error */
        $error = array_pop($this->errorStack);
        return is_null($error) ? null : $error;
    }

}
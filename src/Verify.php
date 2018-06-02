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
     * 错误列表数组队列
     * @var array
     */
    private $errorQueue = [];

    /**
     * Verify constructor.
     * @param array $data
     */
    public function __construct(array $rules = [], array $data = []) {
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
     * 修改校验数据
     * @param $key
     * @param $val
     * @return Verify
     */
    public function setCheckData($key, $val): Verify {
        $this->checkData[$key] = $val;
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
            $alias = '';
            if ($pos = strpos($key, ':')) {
                $alias = substr($key, $pos + 1);
                $key = substr($key, 0, $pos);
            }
            $this->checkRules[$key] = (new Rule($rule, $key, $this));
            return $this->checkRules[$key]->alias($alias);
        }
    }

    /**
     * 对数据进行验证,当$meet为true时,遇到错误就直接返回不对后面的在判断
     * @param bool $meet
     * @return bool
     */
    public function check($meet = true): bool {
        $this->errorQueue = [];
        $retState = true;
        //遍历规则
        foreach ($this->checkRules as $key => $item) {
            if ($item->check($this->checkData[$key] ?? '') === false) {
                array_push($this->errorQueue, $item);
                $retState = false;
                if ($meet) return false;
            }
        }
        return $retState;
    }

    /**
     * 获取数据
     * @param string $key
     * @return bool|mixed
     */
    public function getCheckData(string $key = '') {
        if (empty($key))
            return $this->checkData;
        return $this->checkData[$key] ?? false;
    }

    /**
     * 获取错误列表
     * @return array
     */
    public function getErrorList(): array {
        return $this->errorQueue;
    }

    /**
     * 获取上一个错误
     * @return mixed
     */
    public function getLastError() {
        /** @var Rule $error */
        $error = array_shift($this->errorQueue);
        return is_null($error) ? null : $error->getErrorMsg();
    }

    /**
     * 获取上一个错误规则对象
     * @return mixed
     */
    public function getLastErrorRule(): Rule {
        /** @var Rule $error */
        $error = array_pop($this->errorQueue);
        return is_null($error) ? null : $error;
    }

}
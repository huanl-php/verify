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
     * 绑定的对象
     * @var ICheckDataObject
     */
    private $object = null;

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
    public function addCheckData($key, $data = ''): Verify {
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
    public function setCheckData($key, $val = ''): Verify {
        if (is_array($key)) {
            $this->checkData = $key;
        } else {
            $this->checkData[$key] = $val;
        }
        return $this;
    }

    /**
     * 添加规则
     * @param  $key
     * @param array $rule
     * @return Rule|Verify
     */
    public function addCheckRule($key, array $rule = []) {
        if (is_array($key)) {
            foreach ($key as $k => $item) {
                $this->addCheckRule($k, $item);
            }
            return $this;
        } else if ($key instanceof Rule) {
            return $this->checkRules[$key->getLabel()] = $key;
        } else {
            $alias = '';
            if ($pos = strpos($key, ':')) {
                $alias = substr($key, $pos + 1);
                $key = substr($key, 0, $pos);
            }
            $this->checkRules[$key] = (new Rule($rule, $key, $this));
            $this->checkRules[$key]->alias($alias);
            return $this->checkRules[$key];
        }
    }

    /**
     * 获取验证规则
     * @return array
     */
    public function getCheckRule(): array {
        return $this->checkRules;
    }

    /**
     * 获取规则数组
     * @return array
     */
    public function getArrayRule(): array {
        $retArray = [];
        foreach ($this->checkRules as $key => $item) {
            $retArray[$key] = $item->getRule();
        }
        return $retArray;
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
            if ($item->check($this->getCheckData($key)) === false) {
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
     * @return mixed
     */
    public function getCheckData(string $key = '') {
        if (empty($key))
            return $this->checkData;
        return $this->checkData[$key] ?? (empty($this->object) ? '' : $this->object->getCheckData($key));
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

    /**
     * 绑定数据对象
     * @param ICheckDataObject $verifyObject
     * @return ICheckDataObject
     */
    public function bindObject(ICheckDataObject $verifyObject): ICheckDataObject {
        return $this->object = $verifyObject;

    }
}
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
     * 错误的规则
     * @var Rule
     */
    private $errorRule = null;

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
     * @param string $key
     * @param $data
     * @return Verify
     */
    public function addData(string $key, $data): Verify {
        $this->checkData[$key] = $data;
        return $this;
    }

    /**
     * 添加规则
     * @param string $key
     * @param array $rule
     * @return Rule
     */
    public function addRule(string $key, array $rule = []): Rule {
        $this->checkRules[$key] = new Rule($rule);
        return $this->checkRules[$key];
    }

    /**
     * 对数据进行验证
     * @return bool
     */
    public function check(): bool {
        //遍历规则
        foreach ($this->checkRules as $key => $item) {
            if (!$item->check($this->checkData[$key] ?? '')) {
                $this->errorRule = $item->getErrorMsg();
                return false;
            }
        }
        return true;
    }

}
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
    public function __construct(array $data = [], array $rules) {
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
     * @param $rule
     * @return Verify
     */
    public function addRule(string $key, $rule): Verify {
        $this->checkRules[$key] = $rule;
        return $this;
    }

    /**
     * 对数据进行验证
     * @return bool
     */
    public function check(): bool {
        //遍历规则
        foreach ($this->checkRules as $key => $item) {
            $rule = new Rule($item, $this->checkData[$key] ?? '');
            if (!$rule->check()) {
                $this->errorRule = $rule;
                return false;
            }
            // TODO: 不知道是手动释放内存好还是等系统自动释放好,先标记
        }
    }

}
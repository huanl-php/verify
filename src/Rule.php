<?php


namespace HuanL\Verify;


class Rule {

    /**
     * 数据
     * @var mixed
     */
    private $data;

    /**
     * 规则
     * @var mixed
     */
    private $rule;

    public function __construct($rule, $data = '') {
        $this->rule = $rule;
        $this->data = $data;
    }

    /**
     * 验证数据
     * @return bool
     */
    public function check(): bool {
        //先验证数据是否为空和规则是否允许空
        if (empty($this->data) && !$this->rule['empty']) {
            return false;
        }
        return true;
    }

}
<?php


namespace HuanL\Verify;


class MathHelp {

    /**
     * 验证数字
     * @var string
     */
    const DIGITAL = '/^-?(\d+\.?\d+?|\d)$/';

    /**
     * 验证正整数
     * @var string
     */
    const UINT = '/^[1-9]\d*$/';

    /**
     * 验证整数
     * @var string
     */
    const INT = '/^-?[1-9]\d*$/';

    /**
     * 验证正小数
     * @var string
     */
    const UDECIMAL = '/^[1-9]\d*\.\d*|0\.\d*[1-9]\d*$/';

    /**
     * 验证小数
     * @var string
     */
    const DECIMAL = '/-?([1-9]\d*\.\d*|0\.\d*[1-9]\d*|0?\.0+|0)/';

    /**
     * 验证邮箱
     * @var string
     */
    const EMAIL = '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/';

    /**
     * 验证密码
     * @var string
     */
    const PASSWORD = '/^[\x20-\x7e]{6,16}$/';
}
<?php


namespace HuanL\Verify;


interface ICheckDataObject {

    /**
     * 设置校验数据
     * @param $key
     * @param $val
     * @return mixed
     */
    public function setCheckData($key, $val): ICheckDataObject;

    /**
     * 获取校验数据
     * @param $key
     * @return mixed
     */
    public function getCheckData($key);


}
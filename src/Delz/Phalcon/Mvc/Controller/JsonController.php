<?php

namespace Delz\Phalcon\Mvc\Controller;

/**
 * api控制器，输出json
 *
 * @package Delz\Phalcon\Mvc\Controller
 */
class JsonController extends Controller
{
    /**
     * beforeExecuteRoute事件
     *
     * 关闭视图，并设置Content-Type
     *
     * @param $dispatcher
     */
    public function beforeExecuteRoute($dispatcher)
    {
        $this->get('view')->disable();
        $this->get('response')->setHeader('Access-Control-Allow-Origin','*');
        $this->get('response')->setHeader('Access-Control-Allow-Methods','POST');
        $this->get('response')->setHeader('Access-Control-Allow-Headers','x-requested-with,content-type');
        //$this->get('response')->setHeader('Content-Type', 'application/json;charset=utf-8');
    }
}
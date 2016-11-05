<?php

namespace ctrl;

use framework\util\Singleton;

class EchoCtrl extends CtrlBase
{
    public function batchExecute()
    {
        $ret['code'] = 'foo';
        $this->output($ret);
    }

       /**
     * 输入结果
     * @param array $ret
     */
    public function output($ret = array())
    {
        echo json_encode($ret, true);
    }

    /**
     * 初始化请求连接
     */
    public function __construct()
    {

        
    }

}
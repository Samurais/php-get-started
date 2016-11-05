<?php
namespace ctrl;

use framework\helper;
use framework\view;

/**
 * 用户访问的首页
 *
 * @package       IndexCtrl
 * @subpackage    CtrlBase
 */
class IndexCtrl extends CtrlBase
{

        /**
     * 主方法，获取首页
     */
         public function main()
    {
        phpinfo();

    }

}
<?php

namespace app\agent\controller;

use cmf\controller\AdminBaseController;
use think\Db;

/**
 * Class AIndexController
 * @package app\user\controller
 *
 * @adminMenuRoot(
 *     'name'   =>'代理商管理',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 10,
 *     'icon'   =>'group',
 *     'remark' =>'用户管理'
 * )
 *
 * @adminMenuRoot(
 *     'name'   =>'用户组',
 *     'action' =>'default1',
 *     'parent' =>'user/AdminIndex/default',
 *     'display'=> true,
 *     'order'  => 10000,
 *     'icon'   =>'',
 *     'remark' =>'用户组'
 * )
 */
class AdminAfterController extends AdminBaseController
{
    public function import(){
	return $this->fetch();
    }
}

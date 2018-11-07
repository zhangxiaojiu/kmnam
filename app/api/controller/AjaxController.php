<?php

namespace app\api\controller;

use cmf\controller\BaseController;
use think\Db;
use think\Validate;

class AjaxController extends BaseController
{
    public function selChina($pid){
	$list = Db::table('china')->where(['pid'=>$pid,'id'=>['neq',$pid]])->select();
	if(count($list) <= 0){
	    self::jsonRet(0,'empty data');
	}
	self::jsonRet(1,'成功',$list);
    }
    public function jsonRet($code=0,$msg='empty',$data=[]){
	$ret = [
	    'code'=>$code,
	    'msg'=>'success',
	    'data'=>$data
	];
	die(json_encode($ret));
    }
}

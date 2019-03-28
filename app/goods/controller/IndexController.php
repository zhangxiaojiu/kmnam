<?php

namespace app\goods\controller;

use cmf\controller\UserBaseController;
use think\Db;

class IndexController extends UserBaseController
{
    function _initialize()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
	$nId = getNavId();
	if($nId !== 3){
	    die('无权访问');
	}
        parent::_initialize();
    }
    public function index(){
	$list = Db::name('goods')->where(['status'=>1])->select();
	$this->assign('list',$list);
	return $this->fetch();
    }
    public function info(){
	$id = input('param.id',0);
	if(!$id){
	    $this->error('参数错误');
	}
	$info = Db::name('goods')->find($id);
	$list = Db::name('goods_log')->where(['goods_id'=>$id])->select();
	$this->assign('info',$info);
	$this->assign('list',$list);
	return $this->fetch();
    }
    public function add(){
	$data = $this->request->post();
	if(Db::name('goods')->where(['brand'=>$data['brand']])->find()){
	    $this->error('品牌存在');
	}
	Db::name('goods')->insert($data);
	$this->success("成功");
    }
    public function del(){
	$id = input('param.id',0);
	$info = Db::name('goods')->where(['id'=>$id])->setField('status',0);
	$this->success('删除成功');
    }
    public function in(){
	if($data = $this->request->post()){
	    $data['type'] = 'in';
	    self::changeLog($data);
	    $this->success('成功');
	}
	$list = Db::name('goods')->select();
	$this->assign('list',$list);
	return $this->fetch();
    }
    public function out(){
	if($data = $this->request->post()){
	    $data['type'] = 'out';
	    $quantity = Db::name('goods')->where(['id'=>$data['goods_id']])->value('quantity');
	    if($data['quantity']>$quantity){
		$this->error('库存不够');
	    }
	    self::changeLog($data);
	    $this->success('成功');
	}
	$list = Db::name('goods')->select();
	$this->assign('list',$list);
	return $this->fetch();
    }
    public function changeLog($data){
	if(!$goodsId = $data['goods_id']){
	    $this->error('参数错误');
	}
	$type = $data['type'];
	if($type == 'in'){
	    $number = $data['quantity'];
	}elseif($type == 'out'){
	    $number = -1*$data['quantity'];
	}else{
	    $this->error('类型错误');
	}
	Db::name('goods_log')->insert($data);
	Db::name('goods')->where(['id'=>$goodsId])->setInc('quantity',$number);
    }
}

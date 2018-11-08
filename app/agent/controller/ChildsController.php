<?php

namespace app\agent\controller;

use cmf\controller\UserBaseController;
use think\Db;
use think\Validate;

class ChildsController extends UserBaseController
{

    function _initialize()
    {
        parent::_initialize();
    }
    public function index()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
	$userId = $user['id'];
	$whereOr = [];
	$where = $search = ['state'=>1];
	
	$name = input('param.name','');
	$tel = input('param.tel','');
	$wechat = input('param.wechat','');
	$company = input('param.company','');
	$brand = input('param.brand','');
	$address = input('param.address1','');
	$view = [
	    'name' => $name,
	    'tel' => $tel,
	    'wechat' => $wechat,
	    'company' => $company,
	    'brand' => $brand,
	    'address' => $address
	];
	$childs = Db::name('user')->where(['pid'=>$userId])->select();
	$inArr = $childsSel = [];
	foreach($childs as $v){
	    $childsSel[$v['id']] = $v['user_nickname'] == ''?$v['user_login']:$v['user_nickname'];
	    $inArr[] = $v['id'];
	}
	if(count($inArr) > 0){
	    $childChilds = Db::name('user')->where(['pid'=>['in',$inArr]])->select();
	    foreach($childChilds as $v){
		$childsSel[$v['id']] = $v['user_nickname'] == ''?$v['user_login']:$v['user_nickname'];
		$inArr[] = $v['id'];
	    }
	}
	$where['user_id'] = $search['user_id'] = ['in',$inArr];
	$this->assign('childsSel',$childsSel);
	if(!empty(input('param.user_id',''))){
	    $where['user_id'] = $search['user_id'] = input('param.user_id');
	}
	if(!empty($name)){
	    $where['name'] = $search['name'] = ['like','%'.$name.'%'];
	}
	if(!empty($tel)){
	    $where['tel'] = $search['tel'] = ['like','%'.$tel.'%'];
	}
	if(!empty($wechat)){
	    $where['wechat'] = $search['wechat'] = ['like','%'.$wechat.'%'];
	}
	if(!empty($company)){
	    $where['company'] = $search['company'] = ['like','%'.$company.'%'];
	}
	if(!empty($brand)){
	    $where['brand'] = $search['brand'] = ['like','%'.$brand.'%'];
	}
	if($address !== ''){
	    $a1 = $address;
	    $a2 = !empty(input('param.address2',''))?','.input('param.address2'):'';
	    $a3 = !empty(input('param.address3',''))?','.input('param.address3'):'';
	    $a4 = !empty(input('param.address4',''))?','.input('param.address4'):'';
	    $a5 = !empty(input('param.address5',''))?','.input('param.address5'):'';
	    $address = $a1.$a2.$a3.$a4.$a5;
	    $where['address'] = $search['address'] = ['like',$address.'%'];
	    $addressText = getAddress($address);
	    $whereOr['address'] = ['like','%'.$addressText.'%'];
	}
	$page = input('param.page',0);
	if($page == 0){
	    session('search',[$search,$whereOr]);
	}
	if($page > 0){
	    $where = session('search')[0];
	    $whereOr = session('search')[1];
	}
	$this->assign('where',$view);
	$list = Db::name('agent')->where($where)->whereOr($whereOr)->order('update_time desc')->paginate(10);
	foreach($list as $k => $v){
	    $v['address'] = getAddress($v['address']);
	    $list[$k] = $v;
	}
	$this->assign('list',$list);
        return $this->fetch();
    }
    public function agented()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
	$userId = $user['id'];
	$whereOr = [];
	$where = $search = ['state'=>1];
	
	$name = input('param.name','');
	$tel = input('param.tel','');
	$wechat = input('param.wechat','');
	$company = input('param.company','');
	$brand = input('param.brand','');
	$address = input('param.address1','');
	$view = [
	    'name' => $name,
	    'tel' => $tel,
	    'wechat' => $wechat,
	    'company' => $company,
	    'brand' => $brand,
	    'address' => $address
	];
	$childs = Db::name('user')->where(['pid'=>$userId])->select();
	$inArr = $childsSel = [];
	foreach($childs as $v){
	    $inArr[] = $v['id'];
	    $childsSel[$v['id']] = $v['user_nickname'] == ''?$v['user_login']:$v['user_nickname'];
	}
	$where['user_id'] = $search['user_id'] = ['in',$inArr];
	$this->assign('childsSel',$childsSel);
	if(!empty(input('param.user_id',''))){
	    $where['user_id'] = $search['user_id'] = input('param.user_id');
	}
	if(!empty(input('param.sign_num',''))){
	    $where['sign_num'] = $search['sign_num'] = input('param.sign_num');
	}
	if(!empty($name)){
	    $where['name'] = $search['name'] = ['like','%'.$name.'%'];
	}
	if(!empty($tel)){
	    $where['tel'] = $search['tel'] = ['like','%'.$tel.'%'];
	}
	if(!empty($wechat)){
	    $where['wechat'] = $search['wechat'] = ['like','%'.$wechat.'%'];
	}
	if(!empty($company)){
	    $where['company'] = $search['company'] = ['like','%'.$company.'%'];
	}
	if(!empty($brand)){
	    $where['brand'] = $search['brand'] = ['like','%'.$brand.'%'];
	}
	if($address !== ''){
	    $a1 = $address;
	    $a2 = !empty(input('param.address2',''))?','.input('param.address2'):'';
	    $a3 = !empty(input('param.address3',''))?','.input('param.address3'):'';
	    $a4 = !empty(input('param.address4',''))?','.input('param.address4'):'';
	    $a5 = !empty(input('param.address5',''))?','.input('param.address5'):'';
	    $address = $a1.$a2.$a3.$a4.$a5;
	    $where['address'] = $search['address'] = ['like',$address.'%'];
	    $addressText = getAddress($address);
	    $whereOr['address'] = ['like','%'.$addressText.'%'];
	}
	$page = input('param.page',0);
	if($page == 0){
	    session('search',[$search,$whereOr]);
	}
	if($page > 0){
	    $where = session('search')[0];
	    $whereOr = session('search')[1];
	}
	$this->assign('where',$view);
	$list = Db::name('agented')->where($where)->whereOr($whereOr)->order('update_time desc')->paginate(10);
	foreach($list as $k => $v){
	    $v['address'] = getAddress($v['address']);
	    $list[$k] = $v;
	}
	$this->assign('list',$list);
        return $this->fetch();
    }
    public function info(){
        $user = cmf_get_current_user();
        $this->assign($user);
	$id = input('param.id',0);
	$info = Db::name('agent')->find($id);
	$info['address'] = getAddress($info['address']);
	$list = Db::name('agentFollow')->where(['agent_id'=>$info['id']])->order('id desc')->paginate(5);
	$this->assign('list',$list);
	$this->assign('info',$info);

	return $this->fetch();
    }
    public function agentedInfo(){
        $user = cmf_get_current_user();
        $this->assign($user);
	$id = input('param.id',0);
	$info = Db::name('agented')->find($id);
	$info['address'] = getAddress($info['address']);
	$list = Db::name('agentedFollow')->where(['agent_id'=>$info['id']])->order('id desc')->paginate(5);
	$this->assign('list',$list);
	$this->assign('info',$info);

	return $this->fetch();
    }
}

<?php

namespace app\agent\controller;

use cmf\controller\UserBaseController;
use think\Db;
use think\Validate;

class AgentedController extends UserBaseController
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
	$where = $search = ['user_id'=>$userId,'state'=>1];
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
    public function operateAgent(){
	if ($this->request->isPost()) {
	    $validate = new Validate([
		'name' => 'max:100',
		'state'           => 'between:0,2',
		'birthday'      => 'dateFormat:Y-m-d|after:-88 year|before:-1 day',
		'tel'      => 'max:50',
		'wechat'      => 'max:50',
		'sign_num'      => 'max:50',
		'company'     => 'max:255',
	    ]);
	    $validate->message([
		'name.max'   => 'name超过最大长度',
		'state.between'         => 'state无效值',
		'birthday.dateFormat' => lang('BIRTHDAY_IS_INVALID'),
		'birthday.after'      => lang('BIRTHDAY_IS_TOO_EARLY'),
		'birthday.before'     => lang('BIRTHDAY_IS_TOO_LATE'),
		'tel.max'        => 'tel too long',
		'wechat.max'        => 'wechat too long',
		'company.max'       => 'company too long',
		'sign_num.max'       => 'sign_num too long'
	    ]);
	    $data = $this->request->post();
	    if (!$validate->check($data)) {
		$this->error($validate->getError());
	    }
	    $userId = cmf_get_current_user_id();
	    $data['user_id'] = $userId;
	    $operate = input('param.operate','');
	    if(empty($operate)){
		$this->error('operate wrong');
	    }
	    if(isset($data['address1'])){
		$a1 = $data['address1'];
		$a2 = isset($data['address2'])?','.$data['address2']:'';
		$a3 = isset($data['address3'])?','.$data['address3']:'';
		$a4 = isset($data['address4'])?','.$data['address4']:'';
		$a5 = isset($data['address5'])?','.$data['address5']:'';
		unset($data['address1']);
		unset($data['address2']);
		unset($data['address3']);
		unset($data['address4']);
		unset($data['address5']);

		$data['address'] = $a1.$a2.$a3.$a4.$a5;
	    }
	    if($operate == 'add'){
		self::checkSignNum($data['sign_num']);
		$ret = Db::name('agented')->insert($data);
	    }
	    if($operate == 'edit'){
		self::checkAgentOwn($data['id']);
		$ret = Db::name('agented')->update($data);
	    }
	    if ($ret) {
		$this->success('成功');
	    } else {
		$this->error('失败');
	    }
	} else {
	    $this->error('异常进入');
	}	
    }
    public function info(){
        $user = cmf_get_current_user();
        $this->assign($user);
	$id = input('param.id',0);
	$info = Db::name('agented')->find($id);
	$info['address'] = getAddress($info['address']);
	$list = Db::name('agentedFollow')->where(['user_id'=>$user['id'],'agent_id'=>$info['id']])->order('id desc')->paginate(5);
	$this->assign('list',$list);
	$this->assign('info',$info);

	return $this->fetch();
    }
    public function detail(){
        $user = cmf_get_current_user();
        $this->assign($user);
	$id = input('param.id',0);
	$info = Db::name('agented')->find($id);
	$info['address'] = getAddress($info['address']);
	$list = Db::name('agentedDetail')->where(['sign_num'=>$info['sign_num']])->order('id desc')->paginate(5);
	$this->assign('list',$list);
	$this->assign('info',$info);

	return $this->fetch();
    }
    public function del(){
	$id = input('param.id',0);
	$info = Db::name('agented')->where(['id'=>$id])->setField('state',0);
	$this->success('删除成功');
    }
    public function addFollow(){
	$data = $this->request->post();
	if(!isset($data['type'])){
	    $this->error('请选择类型');
	}
	$userId = cmf_get_current_user_id();
	$data['user_id'] = $userId;
	if(Db::name('agentedFollow')->insert($data)){
	    $time = date('Y-m-d H:i:s');
	    Db::name('agented')->where(['id'=>$data['agent_id']])->setField('update_time',$time);
	    $this->success('ok');
	}
    }

    public function checkAgentOwn($id){
	$agentInfo = Db::name('agented')->find($id);
	$userId = cmf_get_current_user_id();
	if($agentInfo['user_id'] !== $userId){
	    $this->error('agent is not your');
	}
    }
    public function checkSignNum($num){
	$Info = Db::name('agented')->where(['sign_num'=>$num])->find();
	if($Info){
	    $this->error('平台号重复');
	}
    }

}

<?php

namespace app\agent\controller;

use cmf\controller\UserBaseController;
use think\Db;
use think\Validate;

class IndexController extends UserBaseController
{

    function _initialize()
    {
        $nId = getNavId();
        if ($nId !== 2) {
            die('无权访问');
        }
        parent::_initialize();
    }

    public function index()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        $userId = $user['id'];
        $where = $search = ['user_id' => $userId, 'state' => 1];
        $whereOr = [];
        $name = input('param.name', '');
        $tel = input('param.tel', '');
        $wechat = input('param.wechat', '');
        $company = input('param.company', '');
        $brand = input('param.brand', '');
        $mark_color = input('param.mark_color', '');
        $remark = input('param.remark', '');
        $address = input('param.address1', '');
        $create_time = input('param.create_time', '');
        $update_time = input('param.update_time', '');
        $view = [
            'name' => $name,
            'tel' => $tel,
            'wechat' => $wechat,
            'company' => $company,
            'brand' => $brand,
            'remark' => $remark,
            'address' => $address,
            'create_time' => '',
            'update_time' => ''
        ];
        if (!empty($name)) {
            $where['name'] = $search['name'] = ['like', '%' . $name . '%'];
        }
        if (!empty($tel)) {
            $where['tel'] = $search['tel'] = ['like', '%' . $tel . '%'];
        }
        if (!empty($wechat)) {
            $where['wechat'] = $search['wechat'] = ['like', '%' . $wechat . '%'];
        }
        if (!empty($company)) {
            $where['company'] = $search['company'] = ['like', '%' . $company . '%'];
        }
        if (!empty($brand)) {
            $where['brand'] = $search['brand'] = ['like', '%' . $brand . '%'];
        }
        if (!empty($remark)) {
            $where['remark'] = $search['remark'] = ['like', '%' . $remark . '%'];
        }
        if (!empty($mark_color)) {
            $where['mark_color'] = $search['mark_color'] = $mark_color;
        }

        if (!empty($create_time)) {
            $cDays = input('param.c_days', '1');
            $beginCreateTime = $create_time . ' 00:00:00';
            $endCreateTime = date("Y-m-d H:i:s", strtotime($beginCreateTime . " + $cDays day"));
            $where['create_time'] = $search['create_time'] = ['between', [$beginCreateTime, $endCreateTime]];
        }
        if (!empty($update_time)) {
            $uDays = input('param.u_days', '1');
            $beginUpdateTime = $update_time . ' 00:00:00';
            $endUpdateTime = date("Y-m-d H:i:s", strtotime($beginUpdateTime . " + $uDays day"));
            $where['update_time'] = $search['update_time'] = ['between', [$beginUpdateTime, $endUpdateTime]];
        }
        if ($address !== '') {
            $a1 = $address;
            $a2 = !empty(input('param.address2', '')) ? ',' . input('param.address2') : '';
            $a3 = !empty(input('param.address3', '')) ? ',' . input('param.address3') : '';
            $a4 = !empty(input('param.address4', '')) ? ',' . input('param.address4') : '';
            $a5 = !empty(input('param.address5', '')) ? ',' . input('param.address5') : '';
            $address = $a1 . $a2 . $a3 . $a4 . $a5;
            $addressText = getAddress($address);
            $whereOr = "address like '%" . $addressText . "%' OR address like '" . $address . "%'";
        }
        $page = input('param.page', 0);
        if ($page == 0) {
            session('search', [$search, $whereOr]);
        }
        if ($page > 0) {
            $where = session('search')[0];
            $whereOr = session('search')[1];
        }
        $this->assign('where', $view);
        $list = Db::name('agent')->where($where)->where($whereOr)->order('star desc,update_time desc,id desc')->paginate(10);
        foreach ($list as $k => $v) {
            $v['address'] = getAddress($v['address']);
            $list[$k] = $v;
        }
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function operateAgent()
    {
        if ($this->request->isPost()) {
            $validate = new Validate([
                'name' => 'max:100',
                'state' => 'between:0,2',
                'birthday' => 'dateFormat:Y-m-d|after:-88 year|before:-1 day',
                'tel' => 'max:50',
                'wechat' => 'max:50',
                'company' => 'max:255',
            ]);
            $validate->message([
                'name.max' => 'name超过最大长度',
                'state.between' => 'state无效值',
                'birthday.dateFormat' => lang('BIRTHDAY_IS_INVALID'),
                'birthday.after' => lang('BIRTHDAY_IS_TOO_EARLY'),
                'birthday.before' => lang('BIRTHDAY_IS_TOO_LATE'),
                'tel.max' => 'tel too long',
                'wechat.max' => 'wechat too long',
                'company.max' => 'company too long'
            ]);
            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $userId = cmf_get_current_user_id();
            $data['user_id'] = $userId;
            $operate = input('param.operate', '');
            if (empty($operate)) {
                $this->error('operate wrong');
            }
            if (isset($data['address1'])) {
                $a1 = $data['address1'];
                $a2 = isset($data['address2']) ? ',' . $data['address2'] : '';
                $a3 = isset($data['address3']) ? ',' . $data['address3'] : '';
                $a4 = isset($data['address4']) ? ',' . $data['address4'] : '';
                $a5 = isset($data['address5']) ? ',' . $data['address5'] : '';
                unset($data['address1']);
                unset($data['address2']);
                unset($data['address3']);
                unset($data['address4']);
                unset($data['address5']);

                $data['address'] = $a1 . $a2 . $a3 . $a4 . $a5;
            }
            if ($operate == 'add') {
                $ret = Db::name('agent')->insert($data);
            }
            if ($operate == 'edit') {
                self::checkAgentOwn($data['id']);
                $ret = Db::name('agent')->update($data);
            }
            if ($ret) {
                $this->success('成功', "index");
            } else {
                $this->error('失败');
            }
        } else {
            $this->error('异常进入');
        }
    }

    public function info()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        $id = input('param.id', 0);
        $info = Db::name('agent')->find($id);
        $info['address'] = getAddress($info['address']);
        $list = Db::name('agentFollow')->where([
            'user_id' => $user['id'],
            'agent_id' => $info['id']
        ])->order('id desc')->paginate(5);
        $this->assign('list', $list);
        $this->assign('info', $info);

        return $this->fetch();
    }

    public function del()
    {
        $id = input('param.id', 0);
        $info = Db::name('agent')->where(['id' => $id])->setField('state', 0);
        $this->success('删除成功');
    }

    public function star()
    {
        $id = input('param.id', 0);
        $star = input('param.star', 0);
        $v = $star == 0 ? 1 : 0;
        $info = Db::name('agent')->where(['id' => $id])->setField('star', $v);
        $this->redirect('index/index');
    }

    public function addFollow()
    {
        $data = $this->request->post();
        if (!isset($data['type'])) {
            $this->error('请选择类型');
        }
        $userId = cmf_get_current_user_id();
        $data['user_id'] = $userId;
        if (Db::name('agentFollow')->insert($data)) {
            $time = date('Y-m-d H:i:s');
            Db::name('agent')->where(['id' => $data['agent_id']])->setField('update_time', $time);
            $this->success('ok');
        }
    }

    public function importAgent()
    {
        $file = request()->file('agentfile');
        $info = $file->validate([
            'size' => 1567890,
            'ext' => 'csv,xls,xlsx'
        ])->move(ROOT_PATH . 'public' . DS . 'upload');
        if ($info) {
            // 输出 jpg
            //echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            //echo $info->getSaveName();
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
            //echo $info->getFilename();
            $type = $info->getExtension();
            $uploadfile = ROOT_PATH . 'public' . DS . 'upload' . DS . $info->getSaveName();
            if ($type == 'xlsx' || $type == 'xls') {
                $reader = \PHPExcel_IOFactory::createReader('Excel2007'); // 读取 excel 文档
            } else {
                if ($type == 'csv') {
                    $reader = \PHPExcel_IOFactory::createReader('CSV'); // 读取 excel 文档
                } else {
                    die('Not supported file types!');
                }
            }

            $PHPExcel = $reader->load($uploadfile); // 文档名称
            $objWorksheet = $PHPExcel->getSheet(0);
            $highestRow = $objWorksheet->getHighestRow(); // 取得总行数
            $highestColumn = $objWorksheet->getHighestColumn(); // 取得总列数
            //echo $highestRow.$highestColumn;
            // 一次读取一列
            $res = array();
            $agent = [
                0 => 'name',
                1 => 'tel',
                2 => 'wechat',
                3 => 'address',
                4 => 'company',
                5 => 'brand',
                6 => 'remark'
            ];
            for ($row = 2; $row <= $highestRow; $row++) {
                $f = 0;
                for ($column = 0; $column <= 6; $column++) {
                    $val = $objWorksheet->getCellByColumnAndRow($column, $row)->getValue();
                    if ($val == '') {
                        $f++;
                    }
                    $res[$row - 2][$agent[$column]] = htmlspecialchars($val);
                }
                $res[$row - 2]['user_id'] = cmf_get_current_user_id();
                if ($f > 2) {
                    unset($res[$row - 2]);
                }
            }
            if (!empty($res)) {
                if (Db::name('agent')->insertAll($res)) {
                    $this->success('导入成功');
                }
            }
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }

    public function markColor()
    {
        $data = $this->request->post();
        if (!$id = $data['id']) {
            $this->error('wrong id');
        }
        $table = $data['table'];
        unset($data['table']);
        Db::name($table)->update($data);
        $this->redirect('index');
    }

    public function checkAgentOwn($id)
    {
        $agentInfo = Db::name('agent')->find($id);
        $userId = cmf_get_current_user_id();
        if ($agentInfo['user_id'] !== $userId) {
            $this->error('agent is not your');
        }
    }

    public function checkAgentName($name)
    {
        $agentInfo = Db::name('agent')->where(['name' => $name])->find();
        if ($agentInfo) {
            $this->error('we had this name,try another!');
        }
    }

}

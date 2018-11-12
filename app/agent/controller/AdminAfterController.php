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
    public function doImport(){
	$file = request()->file('agented_detail');
	$info = $file->validate(['size'=>1567890,'ext'=>'csv,xls,xlsx'])->move(ROOT_PATH . 'public' . DS . 'upload');
	if($info){
	    // 输出 jpg
	    //echo $info->getExtension();
	    // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
	    //echo $info->getSaveName();
	    // 输出 42a79759f284b767dfcb2a0197904287.jpg
	    //echo $info->getFilename(); 
	    $type = $info->getExtension();
	    $uploadfile = ROOT_PATH . 'public' . DS . 'upload'.DS.$info->getSaveName();
	    if($type=='xlsx'||$type=='xls' ){
		$reader = \PHPExcel_IOFactory::createReader('Excel2007'); // 读取 excel 文档
	    }else if( $type=='csv' ){
		$reader = \PHPExcel_IOFactory::createReader('CSV'); // 读取 excel 文档
	    }else{
		die('Not supported file types!');
	    }

	    $PHPExcel = $reader->load($uploadfile); // 文档名称
	    $objWorksheet = $PHPExcel->getSheet(0);
	    $highestRow = $objWorksheet->getHighestRow(); // 取得总行数
	    $highestColumn = $objWorksheet->getHighestColumn(); // 取得总列数
	    //echo $highestRow.$highestColumn;
	    // 一次读取一列
	    $res = array();
	    $agent = [0=>'name',1=>'sign_num',2=>'period',3=>'trade',4=>'active',5=>'remark'];
	    for ($row = 2; $row <= $highestRow; $row++) {
		$f = 0;//记录空字段，前二空则跳过
		for ($column = 0; $column <=5; $column++) {
		    $val = $objWorksheet->getCellByColumnAndRow($column, $row)->getValue();
		    if($val == ''){
			$f++;
		    }
		    $res[$row-2][$agent[$column]] = htmlspecialchars($val);
		}
		if($f >2){
		    unset($res[$row-2]);
		}
	    }
	    if(!empty($res)){
		if(Db::name('agentedDetail')->insertAll($res)){
		    $this->success('导入成功');
		}
	    }
	}else{
	    // 上传失败获取错误信息
	    $this->error( $file->getError());
	}
 
    }
}

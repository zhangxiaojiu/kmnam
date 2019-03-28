<?php
use think\Db;

function pr($arr,$e=false){
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
    if($e){
	exit();
    }
}

function getAddress($str){
    $ret = '';
    $addArr = explode(',',$str);
    if(count($addArr)>0){
	foreach($addArr as $v){
	    $name = Db::table('china')->where(['id'=>$v])->value('name');
	    $ret .= $name;
	}
    }
    if(preg_match('/[\x{4e00}-\x{9fa5}]/u', $str)>0){
	return $str;
    }else{
	return $ret;
    }
}
function getUsernameByid($id){
    $ret = Db::name('user')->where(['id'=>$id])->value('user_login');
    return $ret;
}
function getNavId($nId=1){
    $uid = session('user.id');
    if($uid){
	$roleName = Db::name('role')->alias('r')->join('kam_role_user ru','r.id = ru.role_id')->where("user_id = $uid")->value('name');
	if($roleName == '销售'){
	    $nId = 2;
	}
	if($roleName == '前台'){
	    $nId = 3;
	}
    }
    return intval($nId);
}
//phpexcel
function phpExcelXlsx($fileName,$sheetName,$title,$list){
    require_once '../simplewind/vendor/phpoffice/phpexcel/Classes/PHPExcel.php';

// Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

// Set document properties
    $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
        ->setLastModifiedBy("Maarten Balliauw")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Test result file");

    foreach ($title as $k=>$v){
        $cols[] = $k;
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($k.'1', $v);
    }
    foreach ($list as $i => $row){
        foreach ($row as $j => $item){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[$j].($i+2), $item);
        }
    }
// Rename worksheet
    $objPHPExcel->getActiveSheet()->setTitle($sheetName);


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$fileName.'.xlsx"');
    header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}

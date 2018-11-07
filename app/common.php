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

<?php
/**
 * 上传
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\home\controller;

class Uploadify extends Base {
	public function upload(){
		$func = input('func');
		$path = input('path','temp');
		$info = array(
				'num'=> input('num/d'),
				'title' => '',
				'upload' =>url('Admin/Ueditor/imageUp',array('savepath'=>$path,'pictitle'=>'banner','dir'=>'logo')),
				'size' => '4M',
				'type' =>'jpg,png,gif,jpeg',
				'input' => input('input'),
				'func' => empty($func) ? 'undefined' : $func,
		);
		$this->assign('info',$info);
		return $this->fetch();
	}
	
	/*
	 删除上传的图片
	*/
	public function delupload(){
		$action=isset($_GET['action']) ? $_GET['action'] : null;
		$filename= isset($_GET['filename']) ? $_GET['filename'] : null;
		$filename= str_replace('../','',$filename);
		$filename= trim($filename,'.');
		$filename= trim($filename,'/');
		if($action=='del' && !empty($filename)){
			$size = getimagesize($filename);
			$filetype = explode('/',$size['mime']);
			if($filetype[0]!='image'){
				return false;
				exit;
			}
			unlink($filename);
			exit;
		}
	}    
}
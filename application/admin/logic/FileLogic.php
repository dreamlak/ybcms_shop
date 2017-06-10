<?php
/**
 * 文件逻辑定义
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\logic;
use think\Model;
use think\File;
class FileLogic extends Model{
	/**
     * 获取目录
     * @param string $dir 目录
     */
	public function getFolder($dir){
		$dir='./'.str_replace(array('/','\\'),'',$dir).'/';
		$dirNameArr = array_map('basename',glob($dir.'*',GLOB_ONLYDIR));
		$dirArr=[];
		foreach($dirNameArr as $k=>$v){
			$dirArr[$k]['url']=$dir.$v;
			$dirArr[$k]['name']=$v;
		}
		return $dirArr;
	}
	/**
     * 获取文件
     * @param string $dir 目录
		["dir"] => string(10) "./uploads/"
	    ["basename"] => string(5) "brand"
	    ["ext"] => string(6) "folder"
	    ["url"] => string(16) "./uploads/brand/"
	    ["filename"] => string(5) "brand"
	    ["size"] => string(7) "1.49 MB"
     */
	public function getFile($dir){
		$dirNameArr = array_map('basename',glob($dir.'*'));
		$dirArr=[];
		foreach($dirNameArr as $k=>$v){
			if(!stristr($v,'~$')){
				$fileinfo=pathinfo($v);
				$dirArr[$k]['dir']=$dir;
				$dirArr[$k]['basename']=$fileinfo['basename'];
				if(!empty($fileinfo['extension'])){
					$dirArr[$k]['ext']=$fileinfo['extension'];
					$dirArr[$k]['url']=$dir.$v;
					$dirArr[$k]['size']=$this->getFilesize($dir.$v);
				}else{
					$dirArr[$k]['ext']='folder';//文件夹
					$dirArr[$k]['url']=$dir.$v.'/';
					$dirArr[$k]['size']=getRealSize(getDirSize($dir.$v));
				}
				$dirArr[$k]['url2']=str_replace('/','@',substr($dirArr[$k]['url'],1));
				$dirArr[$k]['filename']=$fileinfo['filename'];
			}
		}
		return $dirArr;
	}
	/**
     * 获取文件大小
     * @param string $file 文件
     */
	public function getFilesize($file){
		if(!file_exists($file))return false;
		$size=filesize($file);
		$unit=array('b','kb','mb','gb','tb','pb'); 
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	}
}
?>
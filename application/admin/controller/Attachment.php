<?php
/**
 * 附件管理
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\controller;
use app\admin\logic\FileLogic;
use \think\Db;
use think\Page;
class Attachment extends AdminBase{
	public function index(){
		$upload_path='./'.str_replace(array('/','\\'),'',config('config.upload_path')).'/';//上传目录
		$url=input('url')!=''?'.'.str_replace('@','/',input('url')):'';
		$url=$url!=''?$url:$upload_path;
		
		$fileLogic = new FileLogic();
		$file=$fileLogic->getFile($url);//获所有文件
		
		$totalCount=count($file);
		$pagecount=config('paginate.list_admin');
		$Page = new Page($totalCount,$pagecount);
		//从数组的第$page->firstRow个元素开始取出$page->listRows个，并返回数组中的其余元素
		$lists = array_slice($file, $Page->firstRow,$Page->listRows);
		//dump($lists);die;
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('lists',$lists);
		$this->assign('total',$totalCount);
		$this->assign('pagecount',$pagecount);
		$this->assign('folderSize',getRealSize(getDirSize($url)));
		
		$icon=[
			'folder'=>'fa-folder-open-o',
			'jpg'=>'fa-file-image-o','gif'=>'fa-file-image-o','png'=>'fa-file-image-o','jpeg'=>'fa-file-image-o','bmp'=>'fa-file-image-o',
			'zip'=>'fa-file-zip-o','rar'=>'fa-file-zip-o',
			'mp3'=>'fa-file-audio-o',
			'mp4'=>'fa-file-movie-o','flv'=>'fa-file-movie-o',
			'txt'=>'fa-file-text-o',
			'xlsx'=>'fa-file-excel-o','xls'=>'fa-file-excel-o',
			'pptx'=>'fa-file-powerpoint-o','ppt'=>'fa-file-powerpoint-o',
			'docx'=>'fa-file-word-o','doc'=>'fa-file-word-o',
			'pdf'=>'fa-file-pdf-o',
			'php'=>'fa-file-code-o','js'=>'fa-file-code-o','css'=>'fa-file-code-o','html'=>'fa-file-code-o',
		];
		$this->assign('icon',$icon);
		//dump($lists);die;
		return $this->fetch();
	}

	public function delfiles(){
		$urls=input('urls');
		if(getRealSize(getDirSize($urls))>0){
			delFile($urls);
		}else{
			rmdir($urls);
		}
		return ['status'=>1,'msg'=>'删除成功'];
	}
}
?>
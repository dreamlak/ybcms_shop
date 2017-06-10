<?php
/*
 * @name  文件上传
 * @time on 2016/12/12
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\common\controller;
use think\Controller;
use \think\Db;
class Fileupload extends Controller{
	//文件上传入口
    public function index(){
		$map=['status'=>1];
		$map['type']=input('filetype');
		if(input('userid')!=''){
			$map['userid']=input('userid');
		}
		//文件列表(会员只能看到自己的)
    	$lists=Db::name('attachment')->where($map)->order('id desc')->page('1,15')->select();
		$this->assign('lists',$lists);
		
    	$attr=[];
    	$attr['filetype']=input('filetype');//文件类型
		$attr['filenum']=input('filenum');//允许上传文件个数
		$attr['ext']=input('ext');//允许上传扩展名
		$attr['upfilesize']=input('upfilesize');//允许单个上传文件的大小
		$attr['inputid']=input('inputid');//写入input表单的ID
		$attr['ismark']=input('ismark');//是否水印
		$attr['adminid']=input('adminid');//上传管理员ID
		$attr['userid']=input('userid');//上传用户ID
		$attr['dir']=input('dir');//上传目录
		$this->assign('attr',$attr);
		
    	return view();
    }
	//文件Ajax分页
	public function ajaxMore(){
	 	$aPage = !empty(input('page'))?input('page'):1;
	 	$map=['status'=>1];
		$map['type']=input('filetype');
		if(input('userid')!=''){
			$map['userid']=input('userid');
		}
	 	$lists = Db::name('attachment')->where($map)->order('id desc')->page($aPage.',15')->select();;
		if(count($lists)>0) {
            $data['html'] = "";
            foreach ($lists as $val) {
                $this->assign("v", $val);
                $data['html'] .= $this->fetch('_his_list');
            }
			$data['status'] = 1;
        } else {
            $data['stutus'] = 0;
        }
        return $data;
    }
	
	/*文件上传操作
	 * @param string $uptypes	上传文件类型
	 * @param int $ismark		是否允许图片加水印(上传图片有效)
	 * @param int $adminid		管理员ID
	 * @param int $userid		会员ID
	 * @param int $dir			目录
	 * return Json
	*/
	public function fileupload($uptypes='images',$ismark=0,$adminid=0,$userid=0,$dir=''){
		$request = request();
		$config=config('config');//配置参数
		//获取表单上传文件 ，表旱ID为file
	    $file = $request->file('file');
	    //移动到框架应用根目录/uploads/ 目录下
	    $upload_path=str_replace(array('/','\\'),'',$config['upload_path']);//获取上传目录
	    if($dir=='')$dir=$uptypes;
	    $filePath=$upload_path.'/' . $dir . '/';//上传路径（上传目录/文件类型/）
		//文件上传
		$vdate=[];
		$vdate['size']=$config['upload_size'];
		$vdate['ext']=$config['upload_type'];
	    $info = $file->validate($vdate)->rule('upfilename')->move(ROOT_PATH . $filePath);//"upfilename"是自定义文件名函数
		
	    if($info){
	        // 成功上传后 获取上传文件信息
			$data=[
				//'getATime' => $info->getATime(), //最后访问时间 
				//'getBasename' => $info->getBasename(), //获取无路径的basename 
				//'getCTime' => $info->getCTime(), //获取inode修改时间 
				//'getSaveName' => $info->getSaveName(),//获取带路径的文件名
				//'getGroup' => $info->getGroup(), //获取文件组 
				//'getInode' => $info->getInode(), //获取文件inode 
				//'getLinkTarget' => $info->getLinkTarget(), //获取文件链接目标文件 
				//'getMTime' => $info->getMTime(), //获取最后修改时间 
				//'getOwner' => $info->getOwner(), //文件拥有者 
				//'getPath' => $info->getPath(), //不带文件名的文件路径 
				//'getPathInfo' => $info->getPathInfo(), //上级路径的SplFileInfo对象 
				//'getPathname' => $info->getPathname(), //全路径 
				//'getPerms' => $info->getPerms(), //文件权限 
				//'getRealPath' => $info->getRealPath(), //文件绝对路径 
				//'getType' => $info->getType(),//文件类型 file dir link 
				//'isDir' => $info->isDir(), //是否是目录 
				//'isFile' => $info->isFile(), //是否是文件 
				//'isLink' => $info->isLink(), //是否是快捷链接 
				//'isExecutable' => $info->isExecutable(), //是否可执行 
				//'isReadable' => $info->isReadable(), //是否可读 
				//'isWritable' => $info->isWritable(), //是否可写 
				'type' => $uptypes,
				'name' => $info->getFilename(), //获取文件名 
				'url' =>'/'.$filePath.str_replace('\\','/',$info->getSaveName()),
				'ext' => $info->getExtension(), //文件扩展名 
				'size' => getRealSize($info->getSize()),//文件大小，单位字节 
				'ismark' => $ismark,
				'adminid' => $adminid,
				'userid' => $userid,
				'dir' => $dir,
				'addtime' => time(),
				'ip' => $request->ip(),
				'result'=>1,
				'error'=> 0,
			];
			//如果图片允许水印，给图片加水印
			if($ismark && $uptypes=='images'){
				$this->imagewater($data['url']);
			}
			//在此保存数据到数据库
			session('uploadfile_info', $data);
			unset($data['result']);
			unset($data['error']);
			Db::name('attachment')->insert($data);
			return json_encode($data);
	    }else{
	        //上传失败获取错误信息
	        $data=['result'=>0,'error' =>0,'msg'=>$file->getError()];
			session('uploadfile_info', $data);
			return json_encode($data);
	    }
		
	}

	//上传文件预览
	public function filepreview(){
		$data=session('uploadfile_info');
		session('uploadfile_info', null);
		return json_encode($data);
	}
	/**
	 * 图片水印
	 * $img		原图
	 */
	public function imagewater($img=''){
		$img='.'.$img;
		$config=config('config');//配置参数
		$is_mark=$config['is_mark'];//是否开启水印功能
		$mark_type=$config['mark_type'];//水印类型 1是图片，0是文字
		$mark_img='.'.$config['mark_img'];//水印图片
		$mark_txt=$config['mark_txt'];//水印文字
		$mark_sel=$config['mark_sel'];//水印位置
		$mark_degree=$config['mark_degree'];//水印透明度
		$font='./static/images/public/STFANGSO.TTF';
		
		if($is_mark==1){
			if($mark_type==1){
				$image = \think\Image::open($img)
			  		->water($mark_img,$mark_sel,$mark_degree)
			  		->save($img);
			}else{
				$image = \think\Image::open($img)
			  		->text($mark_txt,$font,20,'#ffffff')
			  		->save($img);
			}
			
		}
	}

	/*
              删除上传的图片
     */
    public function delupload(){
        $action = input('action');                
        $filename= input('filename');
        $filename= str_replace('../','',$filename);
        $filename= trim($filename,'.');
        $filename= trim($filename,'/');
		if($action=='del' && !empty($filename) && file_exists($filename)){
            $size = getimagesize($filename);
            $filetype = explode('/',$size['mime']);
            if($filetype[0]!='image'){
                return false;
                exit;
            }
            unlink($filename);
			//数据库删除
			Db::name('attachment')->where('url',input('filename'))->delete();
            exit;
        }
    }
}

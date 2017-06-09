<?php
/**
 * 管理角色模型
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\model;
use think\Db;
class AdminRole extends Base{
	// status属性读取器
    public function getStatusAttr($value){
        $status = [-1 => '删除', 0 => '禁用', 1 => '正常'];
        return $status[$value];
    }
	
	//添加/编缉角色
	public function addData($data,$opttype='add'){
		if(empty($data))return $this->returnInfo('操作失败！提交数据为空！',0);
		
		$rd=[];
		if($opttype=='add'){
			$rs=$this->insert($data);
			if($rs!==false){
				addAdminLog('成功添加角色:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'角色添加成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'角色添加失败'];
			}
		}else{
			$map=['roleid'=>input('post.roleid')];
			$rs=$this->where($map)->update($data);
			if($rs!==false){
				addAdminLog('成功编辑角色:'.$data['name']);
				$rd= ['status'=>1,'msg'=>'角色编辑成功'];
			}else{
				$rd= ['status'=>0,'msg'=>'角色编辑失败'];
			}
		}
		return $rd;
	}
	
	public function getTree($roledata,$nodeid=0,$data=''){
		if($data==''){
			$nodes=Db::name('node')->where('status',1)->field('nodeid,pid,title')->select();
			$data=model('Node')->multi_array($nodes);
		}
		$html='';
		foreach ($data as $r){
			$id=$r['nodeid'];
			$title=$r['title'];
			$levels=$r['levels'];
			$checked='';
			if(!empty($roledata)){
				if(in_array($r['nodeid'], $roledata)){
					$checked='checked="checked"';
				}
			}
			
			if($levels==4){
				$style=' style="float:left"';
			}else{
				$style='';
			}
			
			$line='';
			if($r['pid']>0){
				$lev=$levels-1;
				$line=str_repeat("\t",$lev);
			}
			
			$html.=$line.'<li'.$style.'>';
			$html.='<span><label><input '.$checked.' name="node[]" value="'.$id.'" level="'.$levels.'" onclick="javascript:checknode(this);" type="checkbox">'.$title.'</label></span>';
			//如果有子节点
			if(count($r['child'])>0){
				if($levels==3){
					$html.="\n".$line.'<ul class="treesun clearfix">'."\n";
				}else{
					$html.="\n".$line.'<ul class="treesub">'."\n";
				}
				$html.=$this->getTree($roledata,$nodeid,$r['child']);
				$html.=$line.'</ul>'."\n";
			}
			$html.='</li>'."\n";
		}
		return $html;
	}
}
?>
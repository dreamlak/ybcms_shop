<?php
/**
 * 自定义导航管理
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\controller;
use think\Validate;
use \think\Db;
use app\admin\logic\GoodsLogic;
class Navigation extends AdminBase{
	//导航列表
	public function index(){
		$map=[];
		input('name')!=''?$map['name']=['like','%'.input('name').'%']:'';
		$totalCount=Db::name('navigation')->where($map)->count();
		$pagecount=config('paginate.list_admin');
		$data=Db::name('navigation')->where($map)->order('sort ASC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		$lists = $data->all();
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());
		
		return $this->fetch();
	}
	//添加编辑导航
	public function addeditnav(){
		$id=input('id',0);
    	if(request()->isPost() || request()->isAjax()){
    		$data=input('post.');
    		if($id>0){
    			//编辑
				$rs=Db::name('navigation')->where('id',$id)->update($data);
				if($rs!==false){
					addAdminLog('成功编辑前台导航:'.$data['name']);
					$rd= ['status'=>1,'msg'=>'前台导航编辑成功'];
				}else{
					$rd= ['status'=>0,'msg'=>'前台导航编辑失败'];
				}
    		}else{
    			//添加
    			$myid=Db::name('navigation')->insertGetId($data);
				if($myid>0){
					addAdminLog('成功添加前台导航:'.$data['name']);
					$rd= ['status'=>1,'msg'=>'前台导航添加成功'];
				}else{
					$rd= ['status'=>0,'msg'=>'前台导航添加失败'];
				}
    		}
			return $rd;
    	}else{
    		//分类信息
			$info = Db::name('navigation')->where('id',$id)->find();
			$this->assign('info',$info);
			
			return $this->fetch();
    	}
	}
	//删除导航
	public function del(){
		if(empty(input('id'))){//批量删除
			$ids=input('ids/a');
			$rd=['status'=>0,'msg'=>'删除失败!'];
			$names='';
	    	if(is_array($ids)){
				foreach($ids as $id){
					//删除前获取该删除的领导信箱名
					$names.=Db::name('navigation')->where('id',$id)->value('name').'，';
					//删除
					$rs=Db::name('navigation')->where('id',$id)->delete();
					if($rs==0){
						return $rd;
					}else{
						$rd=['status'=>1,'msg'=>'删除成功!'];
					}
		        }
				if($rd['status']==1){
					addAdminLog('成功删除前台导航:'.$names);
				}
			}
			return $rd;
		}else{//单条删除
			//删除前获取该删除的领导信箱名
			$names=Db::name('navigation')->where('id',input('id'))->value('name');
			//删除
			$rs=Db::name('navigation')->where('id',input('id'))->delete();
			if($rs==0){
				$this->error('删除失败');
			}else{
				addAdminLog('成功删除前台导航:'.$names);
				$this->success('删除成功');
			}
		}
	}
	//排序
    public function setSort(){
        $sort=$_POST['sort'];
		foreach($sort as $k=>$v){
			Db::name('navigation')->where('id',$k)->setField('sort',$v);
		}
		return ['status'=>1,'msg'=>'排序成功！'];
    }
	//选择url
	public function selecturl(){
		$modArr = include APP_PATH.'admin/navurl.php';
		$this->assign('modArr',$modArr);
		
		$cat=Db::name('article_cat')->where('status','<>',-1)->select();
		$getSelect=model('ArticleCat')->getSelectUrl(0,$cat);
		$this->assign('getSelect',$getSelect);
		
		// 系统菜单
        $GoodsLogic = new GoodsLogic();
        $cat_list = $GoodsLogic->goods_cat_list();
        $goodcatlist = array();
        foreach ($cat_list AS $key => $value) {
            $line='';
			if($value['parent_id']>0){
				$levels=$value['level']-1;
				$line=str_repeat('├─',$levels);
			}
			
            $select_val = url("/Home/Goods/goodsList", ['id' => $key]);
            $goodcatlist[$select_val] = $line . $value['name'];
        }
		//dump($goodcatlist);die;
		$this->assign('goodcatlist',$goodcatlist);
		
		return $this->fetch();
	}
}
?>
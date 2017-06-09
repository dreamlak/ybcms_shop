<?php
/*
 * @name  搜索
 * @time on 2016/09/20
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\index\controller;
use think\Controller;
use think\Validate;
use think\Db;
class Search extends CommonBase{
	//搜索列表
    public function index(){
    	$map=['status'=>1];
		$key=input('key');
		if($key=='') $this->error("真抱歉！您还没有输入内容呢！");
		$map['title|description|keywords']=['like','%'.$key.'%'];
		
		$pagecount=config('paginate.list_rows');
		$totalCount=Db::name('article')->where($map)->count();
		if($totalCount==0) $this->error("真抱歉！没有你要查找的内容！");
		$data=Db::name('article')->where($map)->order('artid DESC')->paginate($pagecount,$totalCount,['query'=>request()->param()]);
		
		$lists = $data->all();
		foreach($lists as $k=>$v){
			$lists[$k]['description']=str_replace($key,"<b>$key</b>",$v['description']);
			$lists[$k]['title']=str_replace($key,"<b>$key</b>",$v['title']);
		}
		
		$this->assign('lists',$lists);
		$this->assign('total',$data->total());
		$this->assign('listRows',$data->listRows());
		$this->assign('currentPage',$data->currentPage());
		$this->assign('lastPage',$data->lastPage());
		$this->assign('pages',$data->render());

		return $this->fetch();
    }
}

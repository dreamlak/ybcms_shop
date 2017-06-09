<?php
/*
 * @name  酒店
 * @time on 2016/09/24
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\mobile\model;
use think\Model;
use think\Db;
class Hotel extends Model{
	public function getHotelLists($aPage=1,$aCount=10){
		$aCount=config('paginate.list_rows');
		$map=[];
		$map['status']=1;

		$totalCount=Db::name('hotel')->where($map)->count();
		$lists=Db::name('hotel')->where($map)->order('sort ASC,hotel_id DESC')->page($aPage, $aCount)->select();
		$data=[];
		$data['lists'] = $lists;
		$data['totalCount'] = $totalCount;
		if ($totalCount <= $aPage * $aCount) {
            $data['pagecount'] = 0;
        }else{
            $data['pagecount'] = 1;
        }
		
		return $data;
	}
	//评论
	public function getCommentLists($hotel_id,$aPage=1,$aCount=10){
		$aCount=config('paginate.list_rows');
		$map=[];
		$map['hotel_id']=$hotel_id;
		$map['parent_id']=0;

		$totalCount=Db::name('hotel_comment')->where($map)->count();
		$lists=Db::name('hotel_comment')->where($map)->order('id DESC')->page($aPage, $aCount)->select();
		foreach($lists as $k=>$v){
			$lists[$k]['child']=Db::name('hotel_comment')->where('parent_id',$v['id'])->order('id DESC')->select();
		}
		$data=[];
		$data['lists'] = $lists;
		$data['totalCount'] = $totalCount;
		if ($totalCount <= $aPage * $aCount) {
            $data['pagecount'] = 0;
        }else{
            $data['pagecount'] = 1;
        }
		
		return $data;
	}
}
?>
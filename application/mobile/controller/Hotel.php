<?php
/*
 * @name  酒店
 * @time on 2016/09/20
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\mobile\controller;
use think\Controller;
use think\Validate;
use think\Db;
class Hotel extends MobileBase{
	public function index(){
		$this->redirect('Mobile/Hotel/lists');
	}
	public function lists(){
		//$hotellists=model('Hotel')->getHotelLists();
		//$this->assign("hotellists", $hotellists);
		
		$this->setModName('酒店');
		
		return $this->fetch();
	}
	//查看文章更多
    public function ajaxHotelMore(){
	 	$aPage = input('page');
	 	//提现
	 	$lists = model('Hotel')->getHotelLists($aPage);
		if(count($lists['lists'])>0) {
            $data['html'] = "";
            foreach ($lists['lists'] as $val) {
                $this->assign("v", $val);
				
                $data['html'] .= $this->fetch('hotel/_lists');
            }
			$data['status'] = 1;
			$data['totalCount'] = $lists['totalCount'];
			$data['pagecount'] = $lists['pagecount'];
        } else {
            $data['stutus'] = 0;
        }
        return $data;
    }
	
	public function show(){
		$hotel_id = input('hotel_id');
		
		//信息
		$info=Db::name('hotel')->where('hotel_id',$hotel_id)->find();
		$info['images']=unserialize($info['images']);
		$this->assign('info',$info);
		$this->assign('hotel_id',$hotel_id);
		
		//点击
		Db::name('hotel')->where('hotel_id',$hotel_id)->setInc('click_count',1);
		
		//属性
		$attrArr=Db::name('hotel_attribute')->where('pid',0)->order('sort')->select();
		foreach($attrArr as $k=>$v){
			$attrArr[$k]['child']=Db::name('hotel_attribute')->where('pid',$v['attr_id'])->order('sort')->select();
		}
		$this->assign('attrArr',$attrArr);
		
		//上一篇
		$prev=Db::name('hotel')->where('hotel_id','<',$hotel_id)->order('hotel_id desc')->limit('1')->find();
        if($prev){
            $url=url('Mobile/Hotel/show',['hotel_id'=>$prev['hotel_id']]);
			$prevurl='上一篇：<a href="'.$url.'">'.$prev['name'].'</a>';
        }else{
            $prevurl='上一篇：<a href="javascript:void(0);">无</a>';
        }
		//下一篇
		$next=Db::name('hotel')->where('hotel_id','>',$hotel_id)->order('hotel_id desc')->limit('1')->find();
        if($next ){
            $url=url('Mobile/Hotel/show',['hotel_id'=>$next['hotel_id']]);
			$nexturl='下一篇：<a href="'.$url.'">'.$next['name'].'</a>';
        }else{
            $nexturl='下一篇：<a href="javascript:void(0);">无</a>';
        }
		$this->assign('prevurl',$prevurl);
		$this->assign('nexturl',$nexturl);
		
		//评论列表
		$commentlists=model('Hotel')->getCommentLists($hotel_id);
		$this->assign("comment", $commentlists);
		
		//标题
		$this->setModName($info['name']);
		
		return $this->fetch();
	}

	//查看评论更多
    public function ajaxComMore(){
	 	$aPage = input('page');
		$hotel_id = input('hotel_id');
	 	//提现
	 	$lists = model('Hotel')->getCommentLists($hotel_id,$aPage);
		if(count($lists['lists'])>0) {
            $data['html'] = "";
            foreach ($lists['lists'] as $val) {
                $this->assign("v", $val);
                $data['html'] .= $this->fetch('hotel/_lists_com');
            }
			$data['status'] = 1;
			$data['totalCount'] = $lists['totalCount'];
			$data['pagecount'] = $lists['pagecount'];
        } else {
            $data['stutus'] = 0;
        }
        return $data;
    }
	//发布课件评论
	public function comment(){
		$data=input('post.');
		//数据验证
    	$validate = new Validate([
        	'content|内容' => 'require',
    	]);
    	if(!$validate->check($data)){
        	$msg=$validate->getError();
        	return ['status'=>0,'msg'=>$msg];
    	}
			
		$data['userid']=$data['userid'];
		$data['username']=deal_emoji(Db::name('member')->where('userid',$data['userid'])->value('nickname'));
		$data['ip_address']=request()->ip();
		$data['addtime']=time();
		
		$rs=Db::name('hotel_comment')->insert($data);
		if($rs!==false){
			Db::name('hotel')->where('hotel_id',input('hotel_id'))->setInc('comment_count',1);
			return ['status'=>1,'msg'=>'评论成功！'];
		}else{
			return ['status'=>0,'msg'=>'评论失败！'];
		}
	}  
}
?>
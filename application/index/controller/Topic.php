<?php
/**
 * 专题
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\index\controller;

class Topic extends Base {
	/*
	 * 专题列表
	 */
	public function topicList(){
		$topicList = Db::name('topic')->where("topic_state=2")->select();
		$this->assign('topicList',$topicList);
		return $this->fetch();
	}
	
	/*
	 * 专题详情
	 */
	public function detail(){
		$topic_id = input('topic_id/d',1);
		$topic = Db::name('topic')->where("topic_id", $topic_id)->find();
		$this->assign('topic',$topic);
		return $this->fetch();
	}
	
	public function info(){
		$topic_id = input('topic_id/d',1);
		$topic = Db::name('topic')->where("topic_id", $topic_id)->find();
        echo htmlspecialchars_decode($topic['topic_content']);                
        exit;
	}
}
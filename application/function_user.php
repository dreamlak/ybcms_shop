<?php
/**
 * 获取用户信息
 * @param $user_id_or_name  用户id 邮箱 手机 第三方id
 * @param int $type  类型 0 user_id查找 1 邮箱查找 2 手机查找 3 第三方唯一标识查找
 * @param string $oauth  第三方来源
 * @return mixed
 */
function get_user_info($user_id_or_name,$type = 0,$oauth=''){
    $map = array();
    if($type == 0)
        $map['userid'] = $user_id_or_name;
	
    if($type == 1)
        $map['email'] = $user_id_or_name;
	
    if($type == 2)
        $map['tel'] = $user_id_or_name;
	
    if($type == 3){
        $map['openid'] = $user_id_or_name;
        $map['oauth'] = $oauth;
    }
	
    if($type == 4){
    	$map['unionid'] = $user_id_or_name;
    	$map['oauth'] = $oauth;
    }
	
    $user = db('member')->where($map)->find();
	
    return $user;
}
//获取会员昵称
function getNickname($userid){
	$nickname=db('member')->where('userid',$userid)->value('nickname');
	return deal_emoji($nickname);
}
function addUserLog($content='无内容',$base='pc',$uid='',$uname=''){
	/*$user=session('user');
	if($uid=='')$uid=$user['userid'];
	if($uname=='')$uname=$user['username'];
	
	$data=[
		'userid'=>$uid,
		'username'=>$uname,
		'logtime'=>time(),
		'logip'=>request()->ip(),
		'logbase'=>$base,
		'content'=>$content,
		'url'=>request()->url(),
	];
	$logid=db('member_log')->insertGetId($data);*/
}
//获取会员等级
function getlevel($level_id){
	$name=db('member_level')->where('id',$level_id)->value('name');
	return $name;
}
function is_weixin() {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    }return false;
}

function is_qq() {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'QQ') !== false) {
        return true;
    }return false;
}
function is_alipay() {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'AlipayClient') !== false) {
        return true;
    } return false;
}
function authType(){
	$t='';
	if(is_weixin()){
		$t = 'weixin';
	}elseif(is_qq()){
		$t = 'qq';
	}elseif(is_alipay()){
		$t = 'alipay';
	}else{
		$t = 'pc';
	}
	return $t;
}
/**
 * 隐藏号码，用*号代替
 * $str 要替换的字符串
 * $type 替换的类型，(1=手机号，2=身份证号，3=邮箱=用户名，4=姓名，5=IP)
*/
function hiddenStr($str,$type=1){
    switch($type){
		case 1:
			if(strlen($str)!=11) return '手机号码有误';
			return substr_replace($str,'<font color="red">******</font>',3,6);
			break;
		case 2:
			if(strlen($str)==15){
				return substr_replace($str,'<font color="red">***********</font>',2,11);
			}elseif(strlen($str)==18){
				return substr_replace($str,'<font color="red">**************</font>',3,14);
			}else{
				return '身份证号码有误';
			}
			break;
		case 3:
			return substr_replace($str,'<font color="red">*****</font>',1,5);
			break;
		case 4:
			$strlen = mb_strlen($str, 'utf-8');
			$firstStr = mb_substr($str, 0, 1, 'utf-8');//姓
			$lastStr  = mb_substr($str, -1, 1, 'utf-8');//名的最后一个字
			if($strlen==2){
				return $firstStr.'*';
			}else{
				return $firstStr.'**';
			}
			break;
		case 5:
			return substr_replace($str,'<font color="red">***********</font>',2,11);
			break;
    }
}
?>
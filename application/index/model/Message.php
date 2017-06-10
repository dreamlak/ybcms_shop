<?php
/**
 * 消息
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\index\model;
use think\Model;
use think\Db;

/**
 * @package index\Model
 */
class Message extends Model{
    protected $tableName = 'message';
    protected $_validate = array();

    /**
     * 获取用户的系统消息(不用了)
     * @return array
     */
    public function getUserMessageNotice()
    {
        $this->checkPublicMessage();
        $user_info = session('user');
        $user_system_message_no_read_where = array('um.userid' => $user_info['userid'], 'um.status' => 0, 'um.class' => 0);
        $user_system_message_no_read = DB::name('member_message')
            ->alias('um')
            ->comment('为啥查不了')
            ->field('um.id, um.msgid, m.message, m.addtime')
            ->join('__MESSAGE__ m','um.msgid = m.id','LEFT')
            ->where($user_system_message_no_read_where)
            ->select();
        return $user_system_message_no_read;
    }

    /**
     * 查询系统全体消息，如有将其插入用户信息表
     * @author dyr
     * @time 2016/09/01
     */
    public function checkPublicMessage(){
        $user_info = session('user');

        $user_message = DB::name('member_message')->where(array('userid' => $user_info['userid'], 'class' => 0))->select();
        $time_date = date('Y-m-d H:i:s', $user_info['regtime']);
        $message_where = array(
            'class' => 0,
            'type' => 1,
            'addtime' => array('gt', $time_date),
        );
        if (!empty($user_message)) {
            $userid_array = get_arr_column($user_message, 'id');
            $message_where['id'] = array('NOT IN', $userid_array);
        }
        $user_system_public_no_read = DB::name('message')->field('id')->where($message_where)->select();
        foreach($user_system_public_no_read as $key){
            DB::name('member_message')->comment('插入了没')->insert(['userid'=>$user_info['userid'],'msgid'=>$key['id'],'class'=>0,'status'=>0]);
        }
    }
}
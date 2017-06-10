<?php
namespace app\common\taglib;
use think\template\TagLib;
class Ybcms extends TagLib{
    /**
     * 定义标签列表
     */
    protected $tags   =  [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'art'       => ['attr'=>'where,order,catid,posids,modelid,thumb,num,page,key,item','close'=>1,'level'=>3],//文章
        'lists'     => ['attr'=>'table,where,order,num,page,field,item,key','close'=>1,'alias'=>'page','level'=>3],//通用
        'sql'     	=> ['attr'=>'sql,key,item,result_name','close'=>1,'level'=>3], // sql 万能标签
        'adv'       => ['attr'=>'where,order,num,name,key,item','close'=>1],
    ];
	public function tagArt($tag, $content){
		$where	=!empty($tag['where'])?$tag['where']:'1=1';//查询条件
		$order	=!empty($tag['order'])?$tag['order']:'artid DESC';//排序
        $num	=!empty($tag['num'])?$tag['num']:config('paginate.list_rows');//条数
        $catid	=!empty($tag['catid'])?$tag['catid']:'0';//栏目ID
        $posids	=!empty($tag['posids'])?$tag['posids']:'';//推荐位
        $modelid=!empty($tag['modelid'])?$tag['modelid']:'';//模型ID
        $thumb	=!empty($tag['thumb'])?$tag['thumb']:false;//是否只显示有缩略图的
        $page	=!empty($tag['page'])?$tag['page']:false;//是否要分页
        $key	=!empty($tag['key'])?$tag['key']:'key';//返回的变量key
        $item	=!empty($tag['item'])?$tag['item']:'v';//返回的变量item
        
        $map=[];
		$map['status']=1;
		if($catid>0){
			$catinfo=db('article_cat')->where('catid',$catid)->field('catid,ischild,pidarr')->find();
			if($catinfo['ischild']==1){
				$pidarr = explode(',', $catinfo['pidarr']);
				$map['catid']=['in',$pidarr];
			}else{
				$map['catid']=$catid;
			}
		}
		if($posids!=''){
			$map['posids']=$posids;
		}
		if($modelid!=''){
			$map['modelid']=$modelid;
		}
		if($thumb){
			$map['thumb']=['<>',''];
		}
		
        $str = '<?php ';
		if($page){
			$str .= '$totalCount=Db::name("article")->where('.$map.')->where("'.$where.'")->count();
				$data = db("article")->cache(true,CACHE_TIME)->where('.$map.')->where("'.$where.'")->order("'.$order.'")->paginate('.$num.',$totalCount,[\'query\'=>request()->param()]);
				$lists = $data->all();
				$total=$data->total();
				$listRows=$data->listRows();
				$currentPage=$data->currentPage();
				$lastPage=$data->lastPage();
				$pages=$data->render();
			';
		}else{
			$str .= '$lists = db("article")->cache(true,CACHE_TIME)->where('.$map.')->where("'.$where.'")->order("'.$order.'")->limit("'.$num.'")->select();';
		}
		$str .= '?>';
		
		$str .='{volist name="lists" id="'.$item.'" key="'.$key.'"}';
		$str .= $content;
		$str .='{/volist}';
		
        if(!empty($str)) {
            return $str;
        }
		return;
	}
	public function tagLists($tag,$content){
      	$table	=!empty($tag['table'])?$tag['table']:'';
       	$order	=!empty($tag['order'])?$tag['order']:'';
       	$num	=!empty($tag['num'])?$tag['num']:config('paginate.list_rows');
       	$item	=!empty($tag['item'])?$tag['item']:'v';
       	$where	=!empty($tag['where'])?$tag['where']:' 1 ';
       	$key	=!empty($tag['key'])?$tag['key']:'i';
       	$page	=!empty($tag['page'])?$tag['page']:false;
       	$field	=!empty($tag['field'])?$tag['field']:'';
		
		$parsestr ='<?php ';
		if($page){
			$parsestr .='$totalCount=db("'.$table.'")->where('.$where.')->count();
				$data=db("'.$table.'")->where("'.$where.'")->field("'.$field.'")->order("'.$order.'")->paginate('.$num.',$totalCount,[\'query\'=>request()->param()]);
		  		$__LIST__ = $data->all();
		  		$total=$data->total();
				$listRows=$data->listRows();
				$currentPage=$data->currentPage();
				$lastPage=$data->lastPage();
				$pages=$data->render();
			';
		}else{
			$parsestr .='$__LIST__=db("'.$table.'")->where("'.$where.'")->field("'.$field.'")->order("'.$order.'")->limit('.$num.')->select();';
		}
		$parsestr .= ' ?>';
		
		$parsestr .= '{volist name="__LIST__" id="'.$item.'" key="'.$key.'"}';
        $parsestr .= $content;
        $parsestr .= '{/volist}';
		
  		if(!empty($parsestr)) {
            return $parsestr;
        }
        return ;
    }
	public function tagSql($tag, $content){
            $sql = $tag['sql']; // sql 语句     
            $sql = str_replace(' eq ', ' = ', $sql); //等于
            $sql = str_replace(' neq  ', ' != ', $sql); //不等于            
            $sql = str_replace(' gt ', ' > ', $sql);//大于
            $sql = str_replace(' egt ', ' >= ', $sql);//大于等于
            $sql = str_replace(' lt ', ' < ', $sql);//小于
            $sql = str_replace(' elt ', ' <= ', $sql);//小于等于
            $key	=  !empty($tag['key']) ? $tag['key'] : 'key';//返回的变量key
            $item	=  !empty($tag['item']) ? $tag['item'] : 'item';//返回的变量item	
            $result_name  =  !empty($tag['result_name']) ? $tag['result_name'] : 'result_name';//返回的变量key			
            $t  =  !empty($tag['t']) ? $tag['t'] : CACHE_TIME;//缓存时间
            $name = 'sql_result_'.$item;//.rand(10000000,99999999); //数据库结果集返回命名
            
            $parseStr = '<?php
                            $md5_key = md5("'.$sql.'");
                            $'.$result_name.' = $'.$name.' = S("sql_".$md5_key);
                            if(empty($'.$name.')){                            
                                $'.$result_name.' = $'.$name.' = query("'.$sql.'"); 
                                S("sql_".$md5_key,$'.$name.','.$t.');
                            }
                     	';  
            $parseStr  .=   ' foreach($'.$name.' as $'.$key.'=>$'.$item.'): ?>';
            $parseStr  .=   $content;
            $parseStr  .=   '<?php endforeach; ?>';                                    
           
            if(!empty($parseStr)) {
                return $parseStr;
            }
            return ;
    }     
	public function tagAdv($tag, $content){
     	$order = $tag['order']; //排序
        $num = !empty($tag['num']) ? $tag['num'] : '1'; 
        $where = $tag['where']; //查询条件
        $item  = !empty($tag['item']) ? $tag['item'] : 'item';// 返回的变量item	
        $key  =  !empty($tag['key']) ? $tag['key'] : 'key';// 返回的变量key
        $pid  =  !empty($tag['pid']) ? $tag['pid'] : '0';// 位置ID
        $dev = !empty($tag['dev']) ? $tag['dev'] : 'pc';// 设备
		$name = !empty($tag['name']) ? $tag['name'] : '页面自动增加';//广告位名称
		
        $str = '<?php ';
        $str .= '$pid ='.$pid.';$dev="'.$dev.'";$name="'.$name.'";';
        $str .= '$ad_position = db("poster_space")->cache(true,CACHE_TIME)->column("id,name,width,height","id");';
        $str .= '$result = get_ad($pid,$num);';
        $str .= 'if(!in_array($pid,array_keys($ad_position)) && $pid){
				  	db("poster_space")->insert(array(
				        "id"=>$pid,
				        "name"=>request()->controller()." $name 广告位 $pid ",
				        "status"=>1,
				        "type"=>"banner",
				        "device"=>$dev,
				        "content"=>request()->controller()."页面",
				  	));
				  	delFile(RUNTIME_PATH); //删除缓存  
				}
				foreach($result as $'.$key.'=>$'.$item.'):
    			?>';
		$str .=  $content;
		$str .= '<?php endforeach; ?>';
        if(!empty($str)) {
            return $str;
        }
		//dump($str);die;
        return ;
	}
}
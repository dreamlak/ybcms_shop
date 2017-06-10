<?php
/**
 * 数据库管理
 * -----------------------------------------
 * CopyRight @Ybcms开发团队，并保留所有权利
 * Url: http://www.ybcms.com
 * -----------------------------------------
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */

namespace app\admin\controller;
use think\Controller;
use think\Validate;
use \think\Db;
class Database extends AdminBase{
	//数据表
	public function index(){
		$dbtables = Db::query('SHOW TABLE STATUS');
		//dump($dbtables);die;
		$total = 0;
        foreach ($dbtables as $k => $v) {
            $dbtables[$k]['size'] = getRealSize($v['Data_length'] + $v['Index_length']);
            $total += $v['Data_length'] + $v['Index_length'];
        }
		$this->assign('lists',$dbtables);
	 	$this->assign('total', getRealSize($total));
        $this->assign('tableNum', count($dbtables));
		return $this->fetch();
	}
	//备份操作
	public function backup(){
		//防止备份数据过程超时
        function_exists('set_time_limit') && set_time_limit(0);
    	send_http_status('310');
		$tables = input('tables/a');
        if (empty($tables)) {
            $this->error('请选择要备份的数据表');
        }
		$time = time();//开始时间
        if(!file_exists(ROOT_PATH.'data/backup')){
        	mkdir(ROOT_PATH.'data/backup');
        }
        $path = ROOT_PATH."data/backup/ybcms_tables_" . date("YmdHis").GetRandStr(3);
		$pre = "# -----------------------------------------------------------\n";
        //取得表结构信息
        //1，表示表名和字段名会用``包着的,0 则不用``
        Db::execute("SET SQL_QUOTE_SHOW_CREATE = 1");
		$outstr = '';
        foreach($tables as $table) {
            $outstr.="# 表的结构 {$table} \n";
            $outstr .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $tmp = Db::query("SHOW CREATE TABLE {$table}");
            $outstr .= $tmp[0]['Create Table'] . " ;\n\n";
        }
		
		$sqlTable = $outstr;
        $outstr = "";
        $file_n = 1;
        $backedTable = array();
		//表中的数据
		foreach ($tables as $table) {
			$backedTable[] = $table;
            $outstr.="\n\n# 转存表中的数据：{$table} \n";
		 	$tableInfo = Db::query("SHOW TABLE STATUS LIKE '{$table}'");
            $page = ceil($tableInfo[0]['Rows'] / 10000) - 1;
			for ($i = 0; $i <= $page; $i++){
				$query = Db::query("SELECT * FROM {$table} LIMIT " . ($i * 10000) . ", 10000");
				foreach ($query as $val) {
                    $temSql = "";
                    $tn = 0;
                    $temSql = '';
                    foreach ($val as $v) {
                    	$qian=array("\t","\n","\r");
						$v=str_replace($qian, '', trim($v));
                    	$v=addslashes($v);
                        $temSql.=$tn == 0 ? "" : ",";
                        $temSql.=$v == '' ? "''" : "'{$v}'";
                        $tn++;
                    }
                    $temSql = "INSERT INTO `{$table}` VALUES ({$temSql});\n";
					
                    $sqlNo = "\n# Time: " . date("Y-m-d H:i:s") . "\n" .
                            "# -----------------------------------------------------------\n" .
                            "# SQLFile Label：#{$file_n}\n# -----------------------------------------------------------\n\n\n";
							
                   	if($file_n == 1){
                    	$sqlNo = "# Description:备份的数据表[结构]：" . implode(",", $tables) . "\n".
                            	 "# Description:备份的数据表[数据]：" . implode(",", $backedTable) . $sqlNo;
                	}else{
                   		$sqlNo = "# Description:备份的数据表[数据]：" . implode(",", $backedTable) . $sqlNo;
                	}
					$sqlfilesize=1024*1024*3;
                    if (strlen($pre) + strlen($sqlNo) + strlen($sqlTable) + strlen($outstr) + strlen($temSql)>$sqlfilesize) {
                        $file = $path . "_" . $file_n . ".sql";
                        $outstr = $file_n == 1 ? $pre . $sqlNo . $sqlTable . $outstr : $pre . $sqlNo . $outstr;
                       
                        if (!file_put_contents($file, $outstr, FILE_APPEND)) {
                            $this->error("备份文件写入失败！");
                        }
    
                        $sqlTable = $outstr = "";
                        $backedTable = array();
                        $backedTable[] = $table;
                        $file_n++;
                        //dump($file_n);exit;
                    }
                    $outstr.=$temSql;
                }
			}
		}
		if (strlen($sqlTable . $outstr) > 0) {
            $sqlNo = "\n# Time: " . date("Y-m-d H:i:s") . "\n" .
                    "# -----------------------------------------------------------\n" .
                    "# SQLFile Label：#{$file_n}\n# -----------------------------------------------------------\n\n\n";
					
            if ($file_n == 1) {
                $sqlNo = "# Description:备份的数据表[结构] " . implode(",", $tables) . "\n".
                         "# Description:备份的数据表[数据] " . implode(",", $backedTable) . $sqlNo;
            } else {
                $sqlNo = "# Description:备份的数据表[数据] " . implode(",", $backedTable) . $sqlNo;
            }
			
            $file = $path . "_" . $file_n . ".sql";
            $outstr = $file_n == 1 ? $pre . $sqlNo . $sqlTable . $outstr : $pre . $sqlNo . $outstr;
			//exit($file);
            if (!file_put_contents($file, $outstr, FILE_APPEND)) {
                $this->error("备份文件写入失败！" );
            }
            $file_n++;
        }
        
        $time = time() - $time;
        
		return ['status'=>1,'msg'=>"成功备份数据表，本次备份共生成了" . ($file_n-1) . "个SQL文件。耗时：{$time} 秒"];
	}
	//备份文件列表
	public function restore(){
    	$size = 0;
    	$pattern = "*.sql";
    	$filelist = glob(ROOT_PATH."data/backup/".$pattern);
    	$fileArray = array();
    	foreach ($filelist  as $i => $file) {
    		//只读取文件
    		if (is_file($file)) {
    			$_size = filesize($file);
    			$size += $_size;
    			$name = basename($file);
    			$pre = substr($name, 0, strrpos($name, '_'));
    			$number = str_replace(array($pre. '_', '.sql'), array('', ''), $name);
    			$fileArray[] = array(
    				'name' => $name,
    				'pre' => $pre,
    				'time' => filemtime($file),
    				'size' => $_size,
    				'number' => $number,
    			);
    		}
    	}
    	
    	if(empty($fileArray)) $fileArray = array();
    	krsort($fileArray); //按备份时间倒序排列    	
    	$this->assign('vlist', $fileArray);
    	$this->assign('total', getRealSize($size));
    	$this->assign('filenum', count($fileArray));
    	return $this->fetch();
    }
	/**
     * 读取要导入的sql文件列表并排序后插入SESSION中
     */
    private function getRestoreFiles() {
    	$sqlfilepre = input('sqlfilepre');
    	if (empty($sqlfilepre)) {
    		$this->error('请选择要还原的数据文件！');
    	}
		//获取sql文件前缀(不带扩展名)
		$sqlfilepre=explode('.', $sqlfilepre);
		$sqlfilepre=$sqlfilepre[0];
		//获取sql文件前缀(不带卷号)
		$sqlfilepre=explode('_', $sqlfilepre);
		if(count($sqlfilepre)>1){
			unset($sqlfilepre[count($sqlfilepre)-1]);
		}
		$sqlfilepre=implode('_', $sqlfilepre);

		$pattern = $sqlfilepre. "*.sql";
    	$sqlFiles = glob(ROOT_PATH."data/backup/".$pattern);
    	if (empty($sqlFiles)) {
    		$this->error('不存在对应的SQL文件！');
    	}
    	
    	//将要还原的sql文件按顺序组成数组，防止先导入不带表结构的sql文件
    	$files = array();
    	foreach ($sqlFiles as $sqlFile) {
    		$sqlFile = basename($sqlFile);
    		$k = str_replace(".sql", "", str_replace($sqlfilepre . "_", "", $sqlFile));
    		$files[$k] = $sqlFile;
    	}
    	unset($sqlFiles, $sqlfilepre);
    	ksort($files);
		
    	return $files;
    }
	//还原数据库
    public function restoreData() {
    	function_exists('set_time_limit') && set_time_limit(0); //防止备份数据过程超时
    	//取得需要导入的sql文件
    	if (!isset($_SESSION['cacheRestore']['files'])) {
    		$_SESSION['cacheRestore']['starttime'] = time();
    		$_SESSION['cacheRestore']['files'] = $this->getRestoreFiles();
    	}

    	$files = $_SESSION['cacheRestore']['files'];
		
    	if (empty($files)) {
    		unset($_SESSION['cacheRestore']);
    		$this->error('不存在对应的SQL文件');
    	}
    	
    	//取得上次文件导入到sql的句柄位置
    	$position = isset($_SESSION['cacheRestore']['position']) ? $_SESSION['cacheRestore']['position'] : 0;
    	$execute = 0;
    	foreach ($files as $fileKey => $sqlFile) {
    		$file = ROOT_PATH."data/backup/". $sqlFile;
    		if (!file_exists($file))
    			continue;
    		$file = fopen($file, "r");
    		$sql = "";
    		fseek($file, $position); //将文件指针指向上次位置
    		while (!feof($file)) {
    			$tem = trim(fgets($file));
    			//过滤,去掉空行、注释行(#,--)
    			if (empty($tem) || $tem[0] == '#' || ($tem[0] == '-' && $tem[1] == '-'))
    				continue;
    			//统计一行字符串的长度
    			$end = (int) (strlen($tem) - 1);
    			//检测一行字符串最后有个字符是否是分号，是分号则一条sql语句结束，否则sql还有一部分在下一行中  
	    	   	if ($tem[$end] == ";") {
	    	   		$sql .= $tem;
	    	   		Db::execute($sql);
	    	   		$sql = "";
	    	   		$execute++;
	    	   		if ($execute > 1000) {
			    		$_SESSION['cacheRestore']['position'] = ftell($file);
			    		$imported = isset($_SESSION['cacheRestore']['imported']) ? $_SESSION['cacheRestore']['imported'] : 0;
			    		$imported += $execute;
			    		$_SESSION['cacheRestore']['imported'] = $imported;
			    		//echo json_encode(array("status" => 1, "info" => '如果导入SQL文件卷较大(多)导入时间可能需要几分钟甚至更久，请耐心等待导入完成，导入期间请勿刷新本页，当前导入进度：<font color="red">已经导入' . $imported . '条Sql</font>', "url" => url('restoreData', array(get_randomstr(5) => get_randomstr(5)))));
			    		$this->success('如果SQL文件卷较大(多),则可能需要几分钟甚至更久,<br/>请耐心等待完成，<font color="red">请勿刷新本页</font>，<br/>当前导入进度：<font color="red">已经导入' . $imported . '条Sql</font>', url('restoreData', [GetRandStr(5) => GetRandStr(5)]));
			    		exit();
					}
	    		} else {
	    			$sql .= $tem;
	    		}
    		}
    		//错误位置结束
    		fclose($file);
    		unset($_SESSION['cacheRestore']['files'][$fileKey]);
    		$position = 0;
    	}
    	$time = time() - $_SESSION['cacheRestore']['starttime'];
    	unset($_SESSION['cacheRestore']);
    	$this->success("导入成功，耗时：{$time} 秒钟", url('restore'));
    }
	//优化数据库
    public function optimize() {
    	$batchFlag = input('get.batchFlag/d');
    	if ($batchFlag) {
    		$table = input('key/a');
    	}else {
    		$table[] = input('tablename');
    	}
    	if (empty($table)) {
    		$this->error('请选择要优化的表');
    	}
    	$strTable = implode(',', $table);
    	if (!Db::query("OPTIMIZE TABLE {$strTable} ")) {
    		$strTable = '';
    	}
    	$this->success("优化表成功" . $strTable, url('Database/index'));
    }
	//修复数据库
    public function repair() {
    	$batchFlag = input('get.batchFlag/d');
    	if ($batchFlag) {
    		$table = input('key/a');
    	}else {
    		$table[] = input('tablename');
    	}
    	if (empty($table)) {
    		$this->error('请选择修复的表');
    	}
    	$strTable = implode(',', $table);
    	if (!Db::query("REPAIR TABLE {$strTable} ")) {
    		$strTable = '';
    	}
    	$this->success("修复表成功" . $strTable, url('Database/index'));
    }
	//导入SQL文件
	public function restoreUpload(){
		$file = request()->file('sqlfile');
		$filePath=ROOT_PATH . 'data/backup/';
	    $info = $file->validate(['ext'=>'sql'])->rule(date('YmdHis'))->move($filePath,'',false);//"upfilename"是自定义文件名函数
    	if (!$info) {//上传错误提示错误信息
    		$this->error($file->getError());
    	} else {//上传成功 获取上传文件信息
    		$file_path_full = $info->getPathname();
    		if (file_exists($file_path_full)) {
    			$this->success("上传成功", url('restore'));
    		} else {
    			$this->error('文件不存在');   
    		}
    	}
    }
	
	//下载SQL文件
    public function downFile(){
    	if (empty(input('file')) || empty(input('type')) || !in_array(input('type'), array("zip", "sql"))) {
    		$this->error("下载地址不存在");
    	}
    	$path = array("zip" => ROOT_PATH."data/zipdata/", "sql" => ROOT_PATH."data/backup/");
    	$filePath = $path[input('type')] . input('file');
    	if (!file_exists($filePath)) {
    		$this->error("该文件不存在，可能是被删除");
    	}
    	$filename = basename($filePath);
    	header("Content-type: application/octet-stream");
    	header('Content-Disposition: attachment; filename="' . $filename . '"');
    	header("Content-Length: " . filesize($filePath));
    	readfile($filePath);
    }
	
	/**
     * 删除SQL文件
     */
    public function delSqlFiles() {
    	$batchFlag = input('get.batchFlag/d');
    	//批量删除
    	if ($batchFlag) {
    		$files = input('key/a');
    	}else {
    		$files[] = input('sqlfilename');
    	}
    	if (empty($files)) {
    		$this->error('请选择要删除的sql文件');
    	}
    
    	foreach ($files as $file) {
    		$a = unlink(ROOT_PATH."data/backup/". '/' . $file);
    	}
    	if($a){
    		$this->success("已删除：" . implode(",", $files), url('Database/restore'));
    	}else{
    		$this->error("删除失败：" . implode(",", $files), url('Database/restore'));
    	}	
    }
}
?>
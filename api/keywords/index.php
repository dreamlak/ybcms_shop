<?php
header("Content-Type:text/html; charset=utf-8");
define('APP_ROOT', str_replace('\\', '/', dirname(__FILE__)));
//$data=!empty(trim($_POST['data']))?trim($_POST['data']):trim($_GET['data']);
$data=trim($_POST['data']);
if($data=='') return false;
$keyarr=array_merge(get_tags_arr($data),get_keywords_str($data));//合并
$keyarr=array_unique($keyarr);//去重
$keyarr=trim(implode(',',$keyarr));
echo $keyarr;
function get_tags_arr($title){
	require(APP_ROOT.'/pscws4.class.php');
	$pscws = new PSCWS4();
	$pscws->set_dict(APP_ROOT.'/scws/dict.utf8.xdb');
	$pscws->set_rule(APP_ROOT.'/scws/rules.utf8.ini');
	$pscws->set_ignore(true);
	$pscws->send_text($title);
	$words = $pscws->get_tops(5);
	$tags = array();
	foreach ($words as $val) {
		$tags[] = $val['word'];
	}
	$pscws->close();
	return $tags;
}
function get_keywords_str($content){
	require(APP_ROOT.'/phpanalysis.class.php');
	PhpAnalysis::$loadInit = false;
	$pa = new PhpAnalysis('utf-8', 'utf-8', false);
	$pa->LoadDict();
	$pa->SetSource($content);
	$pa->StartAnalysis( false );
	$tags = $pa->GetFinallyResult();
	$tags = explode(',', $tags);
	return $tags;
}
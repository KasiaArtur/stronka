<?php


include("inc/include.php");

$request_txt_array = array('pid','radio_report');
foreach($request_txt_array as $request_value)
{
	if(isset($_REQUEST[$request_value])){$$request_value = cleanit($_REQUEST[$request_value]);}else{$$request_value = '';}
}
if(isset($_REQUEST['repost_link'])){$repost_link = cleanit($_REQUEST['repost_link']);}else{$repost_link = '';}
if($pid != "" && $pid >= 0 && is_numeric($pid) && $radio_report > 0)
{
	$query="INSERT INTO posts_reports SET PID='".mysql_real_escape_string($pid)."', reason='".mysql_real_escape_string($radio_report)."', repost_link='".mysql_real_escape_string($repost_link)."', time='".time()."', ip='".$_SERVER['REMOTE_ADDR']."'";
	$result=$dbconn->execute($query);
}
header("Location:".$config['baseurl'].$config['postfolder'].$pid);exit;
?>
<?php


include("inc/include.php");
	
$query="SELECT PID, story FROM posts WHERE active='1' order by rand() limit 1";
$executequery=$dbconn->execute($query);
$PID = intval($executequery->fields['PID']);
$story = makeseo($executequery->fields['story']);
if($PID > 0)
{
	header("Location:".$config['baseurl'].$config['postfolder'].$PID."/".$story.".html");exit;
}
else
{
	header("Location:".$config['baseurl']."/");exit;
}
?>
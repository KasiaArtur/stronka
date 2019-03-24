<?php


include("inc/include.php");
$SID = intval($_SESSION['USERID']);
if ($SID != "" && $SID >= 0 && is_numeric($SID))
{	
	$pid = intval($_REQUEST['pid']);
	if($pid > 0)
	{
		$query = "select PID from posts where USERID='".mysql_real_escape_string($SID)."' AND PID='".mysql_real_escape_string($pid)."' and active='1' limit 1"; 
		$executequery = $dbconn->execute($query);
		$DID = intval($executequery->fields['PID']);
		if($DID > 0)
		{
			delete_post($DID);
		}
		$query="SELECT PID, story FROM posts WHERE active='1' order by rand() limit 1";
		$executequery=$dbconn->execute($query);
		$PID = intval($executequery->fields['PID']);
		if($PID > 0)
		{
			if($config['SEO'] == "1")
			{
				$seo_story = makeseo($executequery->fields['story']).".html";
			}
			else
			{
				$seo_story = "";
			}
			header("Location:".$config['baseurl'].$config['postfolder'].$PID."/".$seo_story);exit;
		}
		else
		{
			header("Location:".$config['baseurl']."/");exit;
		}
	}
	else
	{
		header("Location:".$config['baseurl']."/");exit;
	}
}
else
{
	header("Location:".$config['baseurl']."/login");exit;
}
?>
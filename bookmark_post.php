<?php


include("inc/include.php");

$request_int_array = array('bookmark','pid');
foreach($request_int_array as $request_value)
{
	if(isset($_REQUEST[$request_value])){$$request_value = intval($_REQUEST[$request_value]);}else{$$request_value = 0;}
}
$SID = intval($_SESSION['USERID']);
$SVERIFIED = intval($_SESSION['VERIFIED']);
if(($SID > 0) && ($pid > 0) && ($SVERIFIED > 0))
{
	if($bookmark == "1")
	{
		$query="SELECT count(*) as total FROM posts_bookmarks WHERE PID='".mysql_real_escape_string($pid)."' AND USERID='".mysql_real_escape_string($SID)."'";
		$executequery=$dbconn->execute($query);
		$bookmarked = intval($executequery->fields['total']);
		if($bookmarked == 0)
		{
			$query="INSERT INTO posts_bookmarks SET PID='".mysql_real_escape_string($pid)."', USERID='".mysql_real_escape_string($SID)."', time_added='".time()."'";
			$result=$dbconn->execute($query);
			$query="UPDATE users SET bookmarks=bookmarks+1 WHERE USERID='".mysql_real_escape_string($SID)."'";
			$result=$dbconn->execute($query);
		}
		$arr = array('okay' => true);
	}
	elseif($bookmark == "-1")
	{
		$query="DELETE FROM posts_bookmarks WHERE PID='".mysql_real_escape_string($pid)."' AND USERID='".mysql_real_escape_string($SID)."'";
		$result=$dbconn->execute($query);
		$query="UPDATE users SET bookmarks=bookmarks-1 WHERE USERID='".mysql_real_escape_string($SID)."'";
		$result=$dbconn->execute($query);
		$arr = array('okay' => true);
	}
	else
	{
		$arr = array('okay' => false);
	}
}
else
{
	$arr = array(
	'okay' => false
	);
}
echo json_encode($arr);
?>
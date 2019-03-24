<?php


include("inc/include.php");

$request_int_array = array('love','unlove','pid');
foreach($request_int_array as $request_value)
{
	if(isset($_REQUEST[$request_value])){$$request_value = intval($_REQUEST[$request_value]);}else{$$request_value = 0;}
}
$SID = intval($_SESSION['USERID']);
$SVERIFIED = intval($_SESSION['VERIFIED']);
if(($SID > 0) && ($pid > 0) && ($SVERIFIED > 0))
{
	$query="SELECT favclicks, unfavclicks, USERID, phase FROM posts WHERE PID='".mysql_real_escape_string($pid)."'";
    $executequery=$dbconn->execute($query);
    $favclicks = intval($executequery->fields['favclicks']);
	$unfavclicks = intval($executequery->fields['unfavclicks']);
	$phase = $executequery->fields['phase'];
	$UID = $executequery->fields['USERID'];
	
	if($UID != $SID)
	{
		$points_like_sent = intval($config['points_like_sent']);
		$points_like_received = intval($config['points_like_received']);
	}
	else
	{
		$points_like_sent = 0;
		$points_like_received = 0;
	}
	
	if($love == "1")
	{
		$query="SELECT count(*) as total FROM posts_unfavorited WHERE PID='".mysql_real_escape_string($pid)."' AND USERID='".mysql_real_escape_string($SID)."'";
		$executequery=$dbconn->execute($query);
		$unloved = intval($executequery->fields['total']);
		if($unloved > 0)
		{
			$points_like_sent = $points_like_sent * 2;
			$points_like_received = $points_like_received * 2;
			
			$query="INSERT INTO posts_favorited SET PID='".mysql_real_escape_string($pid)."', USERID='".mysql_real_escape_string($SID)."', time_added='".time()."'";
			$result=$dbconn->execute($query);
			$FID = mysql_insert_id();
			$query="UPDATE posts SET favclicks=favclicks+1, unfavclicks=unfavclicks-1 WHERE PID='".mysql_real_escape_string($pid)."'";
			$result=$dbconn->execute($query);
			$query="DELETE FROM posts_unfavorited WHERE PID='".mysql_real_escape_string($pid)."' AND USERID='".mysql_real_escape_string($SID)."'";
			$result=$dbconn->execute($query);
			$query="UPDATE users SET likes_sent=likes_sent+1, dislikes_sent=dislikes_sent-1, points=points+$points_like_sent WHERE USERID='".mysql_real_escape_string($SID)."'";
			$result=$dbconn->execute($query);
			$query="UPDATE users SET likes_received=likes_received+1, dislikes_received=dislikes_received-1, points=points+$points_like_received WHERE USERID='".mysql_real_escape_string($UID)."'";
			$result=$dbconn->execute($query);
			$total_clicks= $favclicks + 2 - $unfavclicks;
		}
		else
		{
			$query="INSERT INTO posts_favorited SET PID='".mysql_real_escape_string($pid)."', USERID='".mysql_real_escape_string($SID)."', time_added='".time()."'";
			$result=$dbconn->execute($query);
			$FID = mysql_insert_id();
			$query="UPDATE posts SET favclicks=favclicks+1 WHERE PID='".mysql_real_escape_string($pid)."'";
			$result=$dbconn->execute($query);
			$query="UPDATE users SET likes_sent=likes_sent+1, points=points+$points_like_sent WHERE USERID='".mysql_real_escape_string($SID)."'";
			$result=$dbconn->execute($query);
			$query="UPDATE users SET likes_received=likes_received+1, points=points+$points_like_received WHERE USERID='".mysql_real_escape_string($UID)."'";
			$result=$dbconn->execute($query);
			$total_clicks= $favclicks + 1 - $unfavclicks;
		}
		if($UID != $SID){update_up_notifications($pid,$UID,$SID,1);}
		update_user_timeline($SID,2,$pid,array('FID' => $FID, 'loved' => 1),0,0);
	}
	elseif($love == "-1")
	{
		$query="DELETE FROM posts_favorited WHERE PID='".mysql_real_escape_string($pid)."' AND USERID='".mysql_real_escape_string($SID)."'";
		$result=$dbconn->execute($query);
		$query="UPDATE posts SET favclicks=favclicks-1 WHERE PID='".mysql_real_escape_string($pid)."'";
		$result=$dbconn->execute($query);
		$query="UPDATE users SET likes_sent=likes_sent-1, points=points-$points_like_sent WHERE USERID='".mysql_real_escape_string($SID)."'";
		$result=$dbconn->execute($query);
		$query="UPDATE users SET likes_received=likes_received-1, points=points-$points_like_received WHERE USERID='".mysql_real_escape_string($UID)."'";
		$result=$dbconn->execute($query);
		$total_clicks= $favclicks - 1 - $unfavclicks;
		if($UID != $SID){update_up_notifications($pid,$UID,$SID,-1);}
		update_user_timeline($SID,2,$pid,array('FID' => 0, 'loved' => -1),0,0);
	}
	if($unlove == "1")
	{
		$query="SELECT count(*) as total FROM posts_favorited WHERE PID='".mysql_real_escape_string($pid)."' AND USERID='".mysql_real_escape_string($SID)."'";
		$executequery=$dbconn->execute($query);
		$loved = intval($executequery->fields['total']);
		if($loved > 0)
		{
			$points_like_sent = $points_like_sent * 2;
			$points_like_received = $points_like_received * 2;
			
			$query="INSERT INTO posts_unfavorited SET PID='".mysql_real_escape_string($pid)."', USERID='".mysql_real_escape_string($SID)."', time_added='".time()."'";
			$result=$dbconn->execute($query);
			$UFID = mysql_insert_id();
			$query="UPDATE posts SET favclicks=favclicks-1, unfavclicks=unfavclicks+1 WHERE PID='".mysql_real_escape_string($pid)."'";
			$result=$dbconn->execute($query);
			$query="DELETE FROM posts_favorited WHERE PID='".mysql_real_escape_string($pid)."' AND USERID='".mysql_real_escape_string($SID)."'";
			$result=$dbconn->execute($query);
			$query="UPDATE users SET likes_sent=likes_sent-1, dislikes_sent=dislikes_sent+1, points=points-$points_like_sent WHERE USERID='".mysql_real_escape_string($SID)."'";
			$result=$dbconn->execute($query);
			$query="UPDATE users SET likes_received=likes_received-1, dislikes_received=dislikes_received+1, points=points-$points_like_received WHERE USERID='".mysql_real_escape_string($UID)."'";
			$result=$dbconn->execute($query);
			$total_clicks= $favclicks - 2 - $unfavclicks;
		}
		else
		{
			$query="INSERT INTO posts_unfavorited SET PID='".mysql_real_escape_string($pid)."', USERID='".mysql_real_escape_string($SID)."', time_added='".time()."'";
			$result=$dbconn->execute($query);
			$UFID = mysql_insert_id();
			$query="UPDATE posts SET unfavclicks=unfavclicks+1 WHERE PID='".mysql_real_escape_string($pid)."'";
			$result=$dbconn->execute($query);
			$query="UPDATE users SET dislikes_sent=dislikes_sent+1, points=points-$points_like_sent WHERE USERID='".mysql_real_escape_string($SID)."'";
			$result=$dbconn->execute($query);
			$query="UPDATE users SET dislikes_received=dislikes_received+1, points=points-$points_like_received WHERE USERID='".mysql_real_escape_string($UID)."'";
			$result=$dbconn->execute($query);
			$total_clicks= $favclicks - 1 - $unfavclicks;
		}
		if($UID != $SID){update_down_notifications($pid,$UID,$SID,1);}
		update_user_timeline($SID,3,$pid,0,array('UFID' => $UFID, 'unloved' => 1),0);
	}
	elseif($unlove == "-1")
	{
		$query="DELETE FROM posts_unfavorited WHERE PID='".mysql_real_escape_string($pid)."' AND USERID='".mysql_real_escape_string($SID)."'";
		$result=$dbconn->execute($query);
		$query="UPDATE posts SET unfavclicks=unfavclicks-1 WHERE PID='".mysql_real_escape_string($pid)."'";
		$result=$dbconn->execute($query);
		$query="UPDATE users SET dislikes_sent=dislikes_sent-1, points=points+$points_like_sent WHERE USERID='".mysql_real_escape_string($SID)."'";
		$result=$dbconn->execute($query);
		$query="UPDATE users SET dislikes_received=dislikes_received-1, points=points+$points_like_received WHERE USERID='".mysql_real_escape_string($UID)."'";
		$result=$dbconn->execute($query);
		$total_clicks= $favclicks + 1 - $unfavclicks;
		if($UID != $SID){update_down_notifications($pid,$UID,$SID,-1);}
		update_user_timeline($SID,3,$pid,0,array('UFID' => 0, 'unloved' => -1),0);
	}
	
	$myes = $config['myes'];
	$mno = $config['mno'];
	$mtrend = $config['mtrend'];
	if($phase == 0)
	{
		if($total_clicks >= $mtrend)
		{
			$query="UPDATE posts SET phase='1', phase_time='".time()."' WHERE PID='".mysql_real_escape_string($pid)."' AND phase='0'";
			$result=$dbconn->execute($query);
		}
		else
		{
			if($total_clicks < 0 && abs($total_clicks) > $mno)
			{
				delete_post($pid);
			}
		}
	}
	elseif($phase == 1)
	{
		if($total_clicks < $mtrend)
		{
			$query="UPDATE posts SET phase='0', phase_time='".time()."' WHERE PID='".mysql_real_escape_string($pid)."' AND phase='1'";
			$result=$dbconn->execute($query);
		}
		elseif($total_clicks >= $myes)
		{
			$query="UPDATE posts SET phase='2', phase_time='".time()."' WHERE PID='".mysql_real_escape_string($pid)."' AND phase='1'";
			$result=$dbconn->execute($query);
		}
	}
	elseif($total_clicks < $myes)
	{
		$query="UPDATE posts SET phase='1', phase_time='".time()."' WHERE PID='".mysql_real_escape_string($pid)."' AND phase='2'";
		$result=$dbconn->execute($query);
	}
	echo intval($total_clicks);
}
?>
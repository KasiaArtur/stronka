<?php


include("inc/include.php");

if(isset($_REQUEST['vote'])){$vote = intval(cleanit($_REQUEST['vote']));}else{$vote = 0;}
if(isset($_REQUEST['unvote'])){$unvote = intval(cleanit($_REQUEST['unvote']));}else{$unvote = 0;}
if(isset($_REQUEST['CMID'])){$CMID = intval(cleanit($_REQUEST['CMID']));}else{$CMID = 0;}
if(isset($_SESSION['USERID'])){$SID = intval($_SESSION['USERID']);}else{$SID = 0;}
if(($SID > 0) && ($CMID > 0))
{
	$query="SELECT voteup, votedown FROM comments WHERE CMID='".mysql_real_escape_string($CMID)."'";
    $executequery=$dbconn->execute($query);
    $voteup = intval($executequery->fields['voteup']);
	$votedown = intval($executequery->fields['votedown']);

	if($vote == "1")
	{
		$query="SELECT count(*) as total FROM comments_unfavorited WHERE CMID='".mysql_real_escape_string($CMID)."' AND USERID='".mysql_real_escape_string($SID)."'";
		$executequery=$dbconn->execute($query);
		$unvoted = $executequery->fields['total'];
		if($unvoted > 0)
		{
			$query="INSERT INTO comments_favorited SET CMID='".mysql_real_escape_string($CMID)."', USERID='".mysql_real_escape_string($SID)."', time_added='".time()."'";
			$result=$dbconn->execute($query);
			$query="UPDATE comments SET voteup=voteup+1, votedown=votedown-1 WHERE CMID='".mysql_real_escape_string($CMID)."'";
			$result=$dbconn->execute($query);
			$query="DELETE FROM comments_unfavorited WHERE CMID='".mysql_real_escape_string($CMID)."' AND USERID='".mysql_real_escape_string($SID)."'";
			$result=$dbconn->execute($query);
			$total_votes= $voteup + 2 - $votedown;
		}
		else
		{
			$query="INSERT INTO comments_favorited SET CMID='".mysql_real_escape_string($CMID)."', USERID='".mysql_real_escape_string($SID)."', time_added='".time()."'";
			$result=$dbconn->execute($query);
			$query="UPDATE comments SET voteup=voteup+1 WHERE CMID='".mysql_real_escape_string($CMID)."'";
			$result=$dbconn->execute($query);
			$total_votes= $voteup + 1 - $votedown;
		}
	}
	elseif($vote == "-1")
	{
		$query="DELETE FROM comments_favorited WHERE CMID='".mysql_real_escape_string($CMID)."' AND USERID='".mysql_real_escape_string($SID)."'";
		$result=$dbconn->execute($query);
		$query="UPDATE comments SET voteup=voteup-1 WHERE CMID='".mysql_real_escape_string($CMID)."'";
		$result=$dbconn->execute($query);
		$total_votes= $voteup - 1 - $votedown;
	}
	
	if($unvote == "1")
	{
		$query="SELECT count(*) as total FROM comments_favorited WHERE CMID='".mysql_real_escape_string($CMID)."' AND USERID='".mysql_real_escape_string($SID)."'";
		$executequery=$dbconn->execute($query);
		$loved = intval($executequery->fields['total']);
		if($loved > 0)
		{
			$query="INSERT INTO comments_unfavorited SET CMID='".mysql_real_escape_string($CMID)."', USERID='".mysql_real_escape_string($SID)."', time_added='".time()."'";
			$result=$dbconn->execute($query);
			$query="UPDATE comments SET voteup=voteup-1, votedown=votedown+1 WHERE CMID='".mysql_real_escape_string($CMID)."'";
			$result=$dbconn->execute($query);
			$query="DELETE FROM comments_favorited WHERE CMID='".mysql_real_escape_string($CMID)."' AND USERID='".mysql_real_escape_string($SID)."'";
			$result=$dbconn->execute($query);
			$total_votes= $voteup - 2 - $votedown;
		}
		else
		{
			$query="INSERT INTO comments_unfavorited SET CMID='".mysql_real_escape_string($CMID)."', USERID='".mysql_real_escape_string($SID)."', time_added='".time()."'";
			$result=$dbconn->execute($query);
			$query="UPDATE comments SET votedown=votedown+1 WHERE CMID='".mysql_real_escape_string($CMID)."'";
			$result=$dbconn->execute($query);
			$total_votes= $voteup - 1 - $votedown;
		}
	}
	elseif($unvote == "-1")
	{
		$query="DELETE FROM comments_unfavorited WHERE CMID='".mysql_real_escape_string($CMID)."' AND USERID='".mysql_real_escape_string($SID)."'";
		$result=$dbconn->execute($query);
		$query="UPDATE comments SET votedown=votedown-1 WHERE CMID='".mysql_real_escape_string($CMID)."'";
		$result=$dbconn->execute($query);
		$total_votes = $voteup + 1 - $votedown;
	}	
	echo intval($total_votes);
}
?>
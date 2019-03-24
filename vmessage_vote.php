<?php


include("inc/include.php");

$vote = intval(cleanit($_REQUEST['vote']));
$CMID = intval(cleanit($_REQUEST['cmid']));
$SID = intval($_SESSION['USERID']);
if(($SID > 0) && ($CMID > 0))
{
	$query="SELECT voteup FROM comments WHERE CMID='".mysql_real_escape_string($CMID)."'";
    $executequery=$dbconn->execute($query);
    $voteup = intval($executequery->fields['voteup']);

	if($vote == "1" || $vote == "-1")
	{
		$query="SELECT count(*) as total FROM comments_favorited WHERE CMID='".mysql_real_escape_string($CMID)."' AND USERID='".mysql_real_escape_string($SID)."'";
		$executequery=$dbconn->execute($query);
		$voted = $executequery->fields['total'];
		if($vote == "1" && $voted == 0)
		{
			$query="INSERT INTO comments_favorited SET CMID='".mysql_real_escape_string($CMID)."', USERID='".mysql_real_escape_string($SID)."', time_added='".time()."'";
			$result=$dbconn->execute($query);
			$query="UPDATE comments SET voteup=voteup+1, votedown=votedown-1 WHERE CMID='".mysql_real_escape_string($CMID)."'";
			$result=$dbconn->execute($query);
			$total_votes= $voteup + 1;
		}
		elseif($vote == "-1" && $voted > 0)
		{
			$query="DELETE FROM comments_favorited WHERE CMID='".mysql_real_escape_string($CMID)."' AND USERID='".mysql_real_escape_string($SID)."'";
			$result=$dbconn->execute($query);
			$query="UPDATE comments SET voteup=voteup-1 WHERE CMID='".mysql_real_escape_string($CMID)."'";
			$result=$dbconn->execute($query);
			$total_votes= $voteup - 1;
		}
	}
	echo intval($total_votes);
}
?>
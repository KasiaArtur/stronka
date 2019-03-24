<?php


include("inc/include.php");

$request_txt_array = array('comment','CMID');
foreach($request_txt_array as $request_value)
{
	if(isset($_REQUEST[$request_value])){$$request_value = cleanit($_REQUEST[$request_value]);}else{$$request_value = '';}
}
if(isset($_REQUEST['PID'])){$PID = intval(cleanit($_REQUEST['PID']));}else{$PID = 0;}
$SID = intval($_SESSION['USERID']);
if($SID > 0)
{
	if($comment == "")
	{
		$response = array( "okay" => false,
		"message" => "",
		"error" => $errors['51'],
		"html" => "",
		);
	}
	elseif(($PID > 0))
	{
		if($CMID == "original"){$parentid = 0;}
		else{$parentid = intval($CMID);}
		$points=intval($config['comments_points']);
		$query="INSERT INTO comments SET USERID='".mysql_real_escape_string($SID)."', PID='".mysql_real_escape_string($PID)."', CMPARENTID='".mysql_real_escape_string($parentid)."', text='".mysql_real_escape_string($comment)."', time_added=".time();
		$result=$dbconn->execute($query);
		$CMID_new = mysql_insert_id();
		if($CMID_new > 0)
		{
			$querymessage2 = "SELECT A.*, B.username FROM comments A, users B WHERE A.USERID=B.USERID AND A.CMID='".mysql_real_escape_string($CMID_new)."' AND A.USERID='".mysql_real_escape_string($SID)."' AND A.PID='".mysql_real_escape_string($PID)."' order by CMID ASC LIMIT 1";
			$executequerymessage2 = $dbconn->execute($querymessage2);
			if($executequerymessage2->rowcount() > 0)
			{
				$comments =  $executequerymessage2->getarray();
				if($parentid == 0)
				{
					$comments = sort_comments($comments);
					STemplate::assign('comments',$comments);
					$html = STemplate::fetch('comments_view_comments_bits.tpl');
				}
				else
				{
					$comment = $comments[0];
					STemplate::assign('reply',$comment);
					$html = STemplate::fetch('comments_view_comments_reply.tpl');
				}
				$query="UPDATE posts SET comments=comments+1 WHERE PID='".mysql_real_escape_string($PID)."'";
				$result=$dbconn->execute($query);
				$query="UPDATE users SET comments=comments+1, points=points+$points WHERE USERID='".mysql_real_escape_string($SID)."'";
				$result=$dbconn->execute($query);
				$response = array( "okay" => true,
				"message" => "success",
				"error" => "",
				"html" => $html,
				);
				update_user_timeline($SID,4,$PID,0,0,array('CMID' => $CMID_new, 'add_or_delete' => 1));
			}
			else
			{
				$response = array( "okay" => false,
				"message" => "",
				"error" => $errors['56'],
				"html" => "",
				);
			}
		}
		else
		{
			$response = array( "okay" => false,
			"message" => "",
			"error" => $errors['57'],
			"html" => "",
			);
		}
	}
	else
	{
		$response = array( "okay" => false,
		"message" => "",
		"error" => $errors['22'],
		"html" => "",
		);
	}
}
else
{
	$response = array( "okay" => false,
	"message" => "",
	"error" => $errors['58'],
	"html" => "",
	);
}
echo json_encode($response);
?>
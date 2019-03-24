<?php


include("inc/include.php");

$request_txt_array = array('vmessage','VMID');
foreach($request_txt_array as $request_value)
{
	if(isset($_REQUEST[$request_value])){$$request_value = cleanit($_REQUEST[$request_value]);}else{$$request_value = '';}
}
if(isset($_REQUEST['PROFILEID'])){$PROFILEID = intval(cleanit($_REQUEST['PROFILEID']));}else{$PROFILEID = 0;}
$SID = intval($_SESSION['USERID']);
if($SID > 0)
{
	if($vmessage == "")
	{
		$response = array( "okay" => false,
		"message" => "",
		"error" => $errors['63'],
		"html" => "",
		);
	}
	elseif($PROFILEID > 0)
	{
		if($VMID == "original"){$parentid = 0;}
		else{$parentid = intval($VMID);}
		$query="INSERT INTO visitor_messages SET USERID='".mysql_real_escape_string($SID)."', PROFILEID='".mysql_real_escape_string($PROFILEID)."', VMPARENTID='".mysql_real_escape_string($parentid)."', text='".mysql_real_escape_string($vmessage)."', time_added=".time();
		$result=$dbconn->execute($query);
		$VMID_new = mysql_insert_id();
		if($VMID_new > 0)
		{
			$querymessage2 = "SELECT A.*, B.username FROM visitor_messages A, users B WHERE A.USERID=B.USERID AND A.VMID='".mysql_real_escape_string($VMID_new)."' AND A.USERID='".mysql_real_escape_string($SID)."' AND A.PROFILEID='".mysql_real_escape_string($PROFILEID)."' order by VMID ASC LIMIT 1";
			$executequerymessage2 = $dbconn->execute($querymessage2);
			if($executequerymessage2->rowcount() > 0)
			{
				$visitor_messages =  $executequerymessage2->getarray();
				if($parentid == 0)
				{
					$visitor_messages = sort_visitor_messages($visitor_messages);
					STemplate::assign('visitor_messages',$visitor_messages);
					$html = STemplate::fetch('vmessage_bits.tpl');
					$query="UPDATE users SET visitor_messages=visitor_messages+1 WHERE USERID='".mysql_real_escape_string($PROFILEID)."'";
					$result=$dbconn->execute($query);
				}
				else
				{
					$visitor_message = $visitor_messages[0];
					STemplate::assign('reply',$visitor_message);
					$html = STemplate::fetch('vmessage_reply_bit.tpl');
				}
			}
			else
			{
				$html = "";
			}
		}
		else
		{
			$html = "";
		}
		$response = array( "okay" => true,
		"message" => "success",
		"error" => "",
		"html" => $html,
		);
	}
	else
	{
		$response = array( "okay" => false,
		"message" => "",
		"error" => $errors['62'],
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
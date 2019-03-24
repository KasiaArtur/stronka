<?php


include("inc/include.php");

if(isset($_REQUEST['VMID'])){$VMID = intval(cleanit($_REQUEST['VMID']));}else{$VMID = 0;}
$SID = intval($_SESSION['USERID']);

if(($SID > 0) && ($VMID > 0))
{
	$query = "SELECT PROFILEID, USERID FROM visitor_messages WHERE (USERID='".$SID."' OR PROFILEID='".$SID."') AND VMID='".$VMID."'";
	$executequery = $dbconn->Execute($query);
	$PROFILEID = intval($executequery->fields['PROFILEID']);
	$USERID = intval($executequery->fields['USERID']);
	if($PROFILEID > 0)
	{
		delete_visitor_message($VMID,$USERID,$PROFILEID);
		$response = array( "okay" => true,
		"message" => "success",
		"error" => "",
		);
	}
	else
	{
		$response = array( "okay" => false,
		"message" => "",
		"error" => $errors['64'],
		);
	}
}
else
{
	$response = array( "okay" => false,
	"message" => "",
	"error" => $errors['65'],
	);
}
echo json_encode($response);
?>
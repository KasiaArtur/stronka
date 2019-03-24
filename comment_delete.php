<?php


include("inc/include.php");

if(isset($_REQUEST['CMID'])){$CMID = intval(cleanit($_REQUEST['CMID']));}else{$CMID = 0;}
$SID = intval($_SESSION['USERID']);

if(($SID > 0) && ($CMID > 0))
{
	$query = "SELECT PID FROM comments WHERE USERID='".$SID."' AND CMID='".$CMID."'";
	$executequery = $dbconn->Execute($query);
	$PID = intval($executequery->fields['PID']);
	if($PID > 0)
	{
		delete_comment($CMID,$SID,$PID);
		update_user_timeline($SID,4,$PID,0,0,array('CMID' => $CMID, 'add_or_delete' => -1));
		$response = array( "okay" => true,
		"message" => "success",
		"error" => "",
		);
	}
	else
	{
		$response = array( "okay" => false,
		"message" => "",
		"error" => $errors['59'],
		);
	}
}
else
{
	$response = array( "okay" => false,
	"message" => "",
	"error" => $errors['60'],
	);
}
echo json_encode($response);
?>
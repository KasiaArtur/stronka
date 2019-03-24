<?php


include("inc/include.php");

if(isset($_REQUEST['page'])){$page = intval($_REQUEST['page']);}else{$page = "1";}
$currentpage = $page;
if ($page >=2){$pagingstart = ($page-1)*$config['items_per_page'];}
else{$pagingstart = "0";}
if(isset($_REQUEST['uname'])){$uname = cleanit($_REQUEST['uname']);}else{$uname = '';}

if($uname != "")
{
	STemplate::assign('uname',$uname);
	$queryp = "select * from users where username='".mysql_real_escape_string($uname)."' AND status='1'"; 
	$resultsp=$dbconn->execute($queryp);
	if($resultsp->rowcount() > 0)
	{
		$users = $resultsp->getrows();
		STemplate::assign('user',$users[0]);
		$USERID = intval($users[0]['USERID']);
				
		$query1 = "SELECT count(*) as total from visitor_messages A, users B WHERE A.VMPARENTID='0' AND A.USERID=B.USERID AND A.PROFILEID='".mysql_real_escape_string($USERID)."' order by A.VMID ASC limit ".$config['maximum_results'];
		$query2 = "SELECT A.*, B.username from visitor_messages A, users B WHERE A.VMPARENTID='0' AND A.USERID=B.USERID AND A.PROFILEID='".mysql_real_escape_string($USERID)."' order by A.VMID ASC limit  $pagingstart, $config[items_per_page]";
		$executequery1 = $dbconn->Execute($query1);
		$total_messages = $executequery1->fields['total'];
		if ($total_messages > 0)
		{
			$executequery2 = $dbconn->Execute($query2);
			$visitor_messages = $executequery2->getrows();
			foreach($visitor_messages as $visitor_message)
			{
				$query_reply = "SELECT A.*, B.username from visitor_messages A, users B WHERE A.VMPARENTID='".mysql_real_escape_string(intval($visitor_message['VMID']))."' AND A.USERID=B.USERID AND A.PROFILEID='".mysql_real_escape_string($USERID)."' order by A.VMID ASC";
				$executequery_reply = $dbconn->Execute($query_reply);
				if($executequery_reply->rowcount() > 0)
				{
					$replies = $executequery_reply->getrows();
					$visitor_messages = array_merge($visitor_messages, $replies);
				}
			}
			$visitor_messages = sort_visitor_messages($visitor_messages);
			STemplate::assign('visitor_messages',$visitor_messages);
			$theprevpage=$currentpage-1;
			$thenextpage=$currentpage+1;
			if($currentpage > 1){STemplate::assign('tpp',$theprevpage);}
			$currentposts = $currentpage * $config['items_per_page'];
			if($total_messages > $currentposts){STemplate::assign('tnp',$thenextpage);}
		}
		STemplate::assign('pagetitle',$uname." ".$lang['270']);			
		$template = 'user.tpl';
		if($config['AUTOSCROLL'] != "1"){STemplate::assign('footerlinks',1);}
	}
	else
	{
		$error = $errors['25'];
		$template = 'empty.tpl';
		STemplate::assign('norightside',1);
		STemplate::assign('footerlinks',1);
	}
}
else
{
	$error = $errors['26'];
	$template = 'empty.tpl';
	STemplate::assign('norightside',1);
	STemplate::assign('footerlinks',1);
}

if ($config['topgags'] > 0)
{
	$topgags = load_topgags();
	STemplate::assign('topgags',$topgags);
}	

if ($config['rhome'] == 1)
{
	$r = load_rhome();
	STemplate::assign('r',$r);
}

if ($config['channels'] == 1)
{
$cats = loadallchannels();
STemplate::assign('allchannels',$cats);

$c = loadtopchannels($cats);
STemplate::assign('c',$c);
}

$_SESSION['location'] = "/user/".$uname."?page=".$page;

//TEMPLATES BEGIN
STemplate::assign('menu',10);
STemplate::assign('nosectionnav',1);
if(isset($error)){STemplate::assign('error',$error);}
STemplate::display('header.tpl');
STemplate::display($template);
STemplate::display('footer.tpl');
//TEMPLATES END
?>
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
		$query1 = "SELECT count(*) as total from posts A, users B, users_timeline C where A.active='1' AND A.PID=C.PID AND A.USERID=B.USERID AND C.USERID='".mysql_real_escape_string($USERID)."' AND C.action_type NOT LIKE '%3%' order by C.time_added desc limit ".$config['maximum_results'];
		$query2 = "SELECT A.*, B.username, C.* from posts A, users B, users_timeline C where A.active='1' AND A.PID=C.PID AND A.USERID=B.USERID AND C.USERID='".mysql_real_escape_string($USERID)."' AND C.action_type NOT LIKE '%3%' order by C.time_added desc limit  $pagingstart, $config[items_per_page]";
		$executequery1 = $dbconn->Execute($query1);
		$totalposts = $executequery1->fields['total'];
		if ($totalposts > 0)
		{
			$executequery2 = $dbconn->Execute($query2);
			$posts = $executequery2->getrows();
			$posts = gif_detector($posts);
			$posts = prepare_user_timeline($posts);
			STemplate::assign('posts',$posts);
			$theprevpage=$currentpage-1;
			$thenextpage=$currentpage+1;
			if($currentpage > 1){STemplate::assign('tpp',$theprevpage);}
			$currentposts = $currentpage * $config['items_per_page'];
			if($totalposts > $currentposts){STemplate::assign('tnp',$thenextpage);}
		}
		STemplate::assign('pagetitle',$uname." ".$lang['271']);			
		$template = 'user_timeline.tpl';
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

$_SESSION['location'] = "/user/".$uname."/timeline?page=".$page;

//TEMPLATES BEGIN
STemplate::assign('menu', 'user_timeline');
STemplate::assign('nosectionnav',1);
if(isset($error)){STemplate::assign('error',$error);}
STemplate::display('header.tpl');
STemplate::display($template);
STemplate::display('footer.tpl');
//TEMPLATES END
?>
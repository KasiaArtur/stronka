<?php


include("inc/include.php");

if(!isset($_SERVER['HTTP_USER_AGENT'])){$_SERVER['HTTP_USER_AGENT'] = "";}
if (in_array($_SERVER['HTTP_USER_AGENT'], array('facebookexternalhit/1.1 (+https://www.facebook.com/externalhit_uatext.php)', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)')))
{
  $facebook_bot = '1';
}
else {
  $facebook_bot = '0';
}

if(isset($_REQUEST['pid'])){$pid = intval($_REQUEST['pid']);}else{$pid = "";}
if ($pid != "" && $pid >= 0 && is_numeric($pid))
{
	STemplate::assign('pid',$pid);
	if(does_post_exist($pid))
	{		
		$query = "SELECT A.*, B.username, B.profilepicture FROM posts A, users B WHERE A.PID='".mysql_real_escape_string($pid)."' AND A.USERID=B.USERID";
       	$executequery = $dbconn->execute($query);
       	$parray = $executequery->getarray();
		if(intval($parray[0]['CID']) > 0)
		{
			$CID = $parray[0]['CID'];
			$queryc = "SELECT cname FROM channels WHERE CID='".$CID."' limit 1";
			$executequeryc = $dbconn->execute($queryc);
			if($executequeryc->rowcount() > 0)
			{
				$c =  $executequeryc->getarray();
				$cname = $c[0]['cname'];
				STemplate::assign('cname',$cname);
			}
		}
		if($parray[0]['tags'] != ""){$parray[0]['tags'] = prepare_tags($parray[0]['tags']);}
		if (!filter_var($parray[0]['source'], FILTER_VALIDATE_URL) === true) {$parray[0]['source'] = "";}
		STemplate::assign('p',$parray[0]);	
		$active = intval($parray[0]['active']);
		$videourl = trim($parray[0]['url']);
		$USERID = $parray[0]['USERID'];
		STemplate::assign('USERID',$USERID);
		$SID = intval($_SESSION['USERID']);
		if($SID != "" && $USERID != "")
		{
			if($SID == $USERID)
			{
				$owner = "1";
				STemplate::assign('owner', 1);
			}
		}
		if($active == "1" || isset($owner) || $facebook_bot == '1')
		{
			STemplate::assign('pagetitle',stripslashes($parray[0]['story']));
			$PID = $parray[0]['PID'];
			STemplate::assign('PID',$PID);
			update_last_viewed($PID);
			update_your_viewed($USERID);
			if (session_verification()){update_you_viewed($SID);}	
			$url = getPageUrl();
			$pos = strrpos($url,"new");
			if($pos > 0){STemplate::assign('new',1);}
			$pos = strrpos($url,"comment=empty");
			if($pos > 0){$error= $errors['51'];}
				
			$r = load_rhome();
			STemplate::assign('r',$r);
			
			if($config['populargags'] > 0)
			{
				$querypopular = "SELECT A.*, B.username FROM posts A, users B WHERE A.USERID=B.USERID AND A.PID!='".mysql_real_escape_string($pid)."' AND A.active='1' ORDER BY rand() desc limit 4";
				$executequerypopular = $dbconn->execute($querypopular);
				if($executequerypopular->rowcount() > 0)
				{
					$popular =  $executequerypopular->getarray();
					STemplate::assign('popular',$popular);
				}
			}
			if(isset($_SERVER['HTTP_REFERER']) && intval($parray[0]['CID']) > 0)
			{
				if(strpos($_SERVER['HTTP_REFERER'], "/channels/"))
				{
					$next_add2sql = " AND CID='".$CID."' ";
				}
				else
				{
					$next_add2sql = " AND phase='".$parray[0]['phase']."' ";
				}
			}
			else
			{
				$next_add2sql = " AND phase='".$parray[0]['phase']."' ";
			}

			$query="SELECT * FROM posts WHERE PID!='".mysql_real_escape_string($pid)."' AND PID<'".mysql_real_escape_string($pid)."' AND active='1' ".$next_add2sql." order by PID desc limit 3";
        	$executequery=$dbconn->execute($query);
			$nextstories1 = array();
			if($executequery->recordcount() > 0)
			{
				$nextstoriestemp =  $executequery->getarray();
				$j = $executequery->recordcount();
				for($i=1; $i<$j+1; $i++)
				{
					$nextstories1[$i - 1] = $nextstoriestemp[$j - $i];
				}
				STemplate::assign('prev',$nextstories1[$j - 1]['PID']);
				STemplate::assign('prevstory',$nextstories1[$j - 1]['story']);
			}
			$query="SELECT * FROM posts WHERE PID!='".mysql_real_escape_string($pid)."' AND PID>'".mysql_real_escape_string($pid)."' AND active='1' ".$next_add2sql." order by PID asc limit 2";
        	$executequery=$dbconn->execute($query);
			$nextstories2 = array();
			if($executequery->recordcount() > 0)
			{
				$nextstories2 =  $executequery->getarray();
				STemplate::assign('next',$nextstories2[0]['PID']);
				STemplate::assign('nextstory',$nextstories2[0]['story']);
			}
			$nextstories = array_merge($nextstories1, $parray);
			$nextstories = array_merge($nextstories, $nextstories2);
			STemplate::assign('nextstories',$nextstories);
			$comments_tabs = 0;
			$comments_sections = array();
			$comments_sections[] = array( 'tpl_name' => 'comments_view_bits.tpl' , 'tab_tpl_name' => 'comments_view_bits_tab.tpl' , 'enabled' => $config['comments_enabled']);
			$comments_sections[] = array( 'tpl_name' => 'comments_view_fb.tpl' , 'tab_tpl_name' => 'comments_view_fb_tab.tpl' , 'enabled' => $config['comments_fb_enabled']);
			$comments_sections[] = array( 'tpl_name' => 'comments_view_vk.tpl' , 'tab_tpl_name' => 'comments_view_vk_tab.tpl' , 'enabled' => $config['comments_vk_enabled']);
			foreach($comments_sections as $section_key => $comments_section)
			{
				if($comments_section['enabled'] == "1")
				{
					$comments_tabs = $comments_tabs + 1;
					$comments_tpl = $comments_section['tpl_name'];
					$comments_tab_tpl = $comments_section['tab_tpl_name'];
				}
				else
				{
					unset($comments_sections[$section_key]);
				}
			}
			if($comments_tabs > 1)
			{
				$comments_sections_array = array();
				if(isset($comments_sections[$config['comments_1tab']]) && $comments_sections[$config['comments_1tab']]['enabled'] == "1")
				{
					$comments_sections_array[] = $comments_sections[$config['comments_1tab']];
					unset($comments_sections[$config['comments_1tab']]);
				}
				$comments_sections_array = array_merge($comments_sections_array, $comments_sections);
				STemplate::assign('comments_sections_array',$comments_sections_array);
			}
			STemplate::assign('comments_tabs',$comments_tabs);
			STemplate::assign('comments_tpl',$comments_tpl);
			STemplate::assign('comments_tab_tpl',$comments_tab_tpl);
			if($config['comments_enabled'] == "1")
			{
				$url = getPageUrl();
				if(strrpos($url,"CMID=") > 0)
				{
					$parts = parse_url($url);
					parse_str($parts['query'], $query);
					$CMID = intval($query['CMID']);
				}
				else
				{
					$CMID = 0;
				}
				$comments = get_comments($pid, $CMID);
				STemplate::assign('comments',$comments);
			}
			/*
			if($config['comments_enabled'] == "1" && $config['comments_1tab'] == "1")
			{
				$first_tab = "Internal Comments";
				if($config['comments_fb_enabled'] == "1")
				{
					$second_tab = "Facebook Comments";
				}
				if($config['comments_vk_enabled'] == "1")
				{
					$third_tab = "vK Comments";
				}
			}
			else
			{
				if($config['comments_fb_enabled'] == "1")
				{
					$first_tab = "Facebook Comments";
					if($config['comments_vk_enabled'] == "1")
					{
						$second_tab = "vK Comments";
					}
				}
				elseif($config['comments_vk_enabled'] == "1")
				{
					$first_tab = "vK Comments";
				}
			}*/
			$template = "view.tpl";
		}
		else
		{
			$error = $errors['23'];
			$template = "empty.tpl";
			STemplate::assign('norightside',1);
		}
	}	
	else
	{
		$error = $errors['24'];
		$template = "empty.tpl";
		STemplate::assign('norightside',1);
	}
}
else
{
	$error = $errors['22'];
	$template = "empty.tpl";
	STemplate::assign('norightside',1);
}

if ($config['channels'] == 1)
{
	$cats = loadallchannels();
	STemplate::assign('allchannels',$cats);

	$c = loadtopchannels($cats);
	STemplate::assign('c',$c);
}

$_SESSION['location'] = $config['postfolder'].$pid;

//TEMPLATES BEGIN
STemplate::assign('viewpage',1);
STemplate::assign('footerlinks',1);
if(isset($error)){STemplate::assign('error',$error);}
STemplate::display('header.tpl');
STemplate::display($template);
STemplate::display('footer.tpl');
//TEMPLATES END
?>
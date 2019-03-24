<?php


include("inc/include.php");

if(isset($_REQUEST['pid'])){$pid = intval($_REQUEST['pid']);}else{$pid = "";}
if ($pid != "" && $pid >= 0 && is_numeric($pid))
{
	STemplate::assign('pid',$pid);
	if(does_post_exist($pid))
	{		
		$query = "SELECT A.*, B.username, B.profilepicture FROM posts A, users B WHERE A.PID='".mysql_real_escape_string($pid)."' AND A.USERID=B.USERID";
       	$executequery = $dbconn->execute($query);
       	$parray = $executequery->getarray();
		$pos = strrpos($parray[0]['pic'],".");
		$ph = strtolower(substr($parray[0]['pic'],$pos+1,strlen($parray[0]['pic'])-$pos));
		if($ph == "gif")
		{
			$parray[0]['gif'] = 1;
			$mp4_file=$config['posts_dir']."/videos/".$parray[0]['pic'].'.mp4';
			$webm_file=$config['posts_dir']."/videos/".$parray[0]['pic'].'.webm';
			if(file_exists($mp4_file)){$parray[0]['mp4'] = 1;}
			if(file_exists($webm_file)){$parray[0]['webm'] = 1;}
		}
		else{$parray[0]['gif'] = 0;}
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
		if($active == "1")
		{
			$PID = $parray[0]['PID'];
			STemplate::assign('PID',$PID);
			update_last_viewed($PID);
			update_your_viewed($USERID);
			if (session_verification()){update_you_viewed($SID);}
			$query="SELECT PID, story FROM posts WHERE PID!='".mysql_real_escape_string($PID)."' AND PID>'".mysql_real_escape_string($PID)."' AND active='1' order by PID asc limit 1";
        	$executequery=$dbconn->execute($query);
			if($executequery->fields['PID'] != "")
			{
				$next = $executequery->fields['PID'];
				$nextstory = $executequery->fields['story'];
				if($config['SEO'] == 1){$nextstory = "/".makeseo($nextstory).".html";}
				else{$nextstory = "";}
				$nexturl = $config['baseurl'].$config['postfolder'].$next.$nextstory;
				STemplate::assign('next',$next);
				STemplate::assign('nextstory',$nextstory);
			}
			else
			{
				$nexturl = "";
			}
			$query="SELECT PID, story FROM posts WHERE PID!='".mysql_real_escape_string($PID)."' AND PID<'".mysql_real_escape_string($PID)."' AND active='1' order by PID desc limit 1";
        	$executequery=$dbconn->execute($query);
			if($executequery->fields['PID'] != "")
			{
				$prev = $executequery->fields['PID'];
				$prevstory = $executequery->fields['story'];
				if($config['SEO'] == 1){$prevstory = "/".makeseo($prevstory).".html";}
				else{$prevstory = "";}
				$prevurl = $config['baseurl'].$config['postfolder'].$prev.$prevstory;
				STemplate::assign('prev',$prev);
				STemplate::assign('prevstory',$prevstory);
			}
			else
			{
				$prevurl = "";
			}
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
				$comments = get_comments($pid, 0);
				STemplate::assign('comments',$comments);
			}
		}
		else
		{
			$error = $errors['23'];
			
		}
	}	
	else
	{
		$error = $errors['24'];
	}
}
else
{
	$error = $errors['22'];
}

$_SESSION['location'] = $config['postfolder'].$pid;
if(isset($error))
{
	$arr = array('PID' => '', 'story' => '', 'storyseo' => '', 'html' => $error, 'nexturl' => '', 'prevurl' => '');
}
else
{
	$arr = array('PID' => $PID, 'story' => $parray[0]['story'], 'storyseo' => makeseo($parray[0]['story']).".html", 'html' => STemplate::fetch('view_loadpost.tpl'), 'comments' => STemplate::fetch('loadcomments.tpl'), 'nexturl' => $nexturl, 'prevurl' => $prevurl);
}
echo json_encode($arr);
?>
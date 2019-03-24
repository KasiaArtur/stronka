<?php


include("inc/include.php");

$points_upload = intval($config['points_upload']);
if($config['approve_stories'] == "1"){$active = "0";}else{$active = "1";}
$SID = intval($_SESSION['USERID']);
$SVERIFIED = intval($_SESSION['VERIFIED']);
if ($SID != "" && $SID >= 0 && is_numeric($SID) && ($SVERIFIED > 0))
{	
	$ctime = 24 * 60 * 60;
	$utime = time() - $ctime;
	$query = "select count(*) as total from posts WHERE USERID='".mysql_real_escape_string($SID)."' AND time_added>='$utime'"; 
	$executequery = $dbconn->execute($query);
	$myuploads = $executequery->fields['total'];
	$quota = $config['quota'];
	if($myuploads >= $quota)
	{
		$error = $errors['39'];
		$template = "empty.tpl";
	}
	else
	{
		$template = "submit.tpl";
		$queryc = "SELECT * FROM channels";
		$executequeryc = $dbconn->execute($queryc);
		$c =  $executequeryc->getarray();
		STemplate::assign('c',$c);
		$request_txt_array = array('url','submit_sec','title','tags','source','nsfw');
		foreach($request_txt_array as $request_value)
		{
			if(isset($_REQUEST[$request_value])){$$request_value = cleanit($_REQUEST[$request_value]);}else{$$request_value = '';}
		}
		if(!filter_var($source, FILTER_VALIDATE_URL) === true) {$source = "";}
		if(isset($_REQUEST['CID']) && !empty($_REQUEST['CID']))
		{
			$CID_array = $_REQUEST['CID'];
			$CID = implode(",",$CID_array);
		}
		else
		{
			$CID_array = array();
			$CID = "";
		}
		if($url != "")
		{
			if($submit_sec == "1")
			{
				if((strstr($url, 'youtube.com/watch?v=')) || (strstr($url, 'youtu.be/')) || (strstr($url, 'funnyordie.com/videos/')) || ((strstr($url, 'facebook.com/')) && (strstr($url, 'photo.php') || strstr($url, 'video.php') || strstr($url, '/videos/'))) || (strstr($url, 'videofy.me/')) || (strstr($url, 'vimeo.com/')) || (strstr($url, 'vine.co/v/')))
				{
					if($config['vupload'] == 1)
					{						
						if($title == "")
						{
							$error = $errors['40'];
						}
						
						if(!isset($error))
						{
							if(strstr($url, 'youtube.com/watch?v='))
							{
								$youtube_url = $url;
								$position       = strpos($youtube_url, 'watch?v=')+8;
								$remove_length  = strlen($youtube_url)-$position;
								$video_id       = substr($youtube_url, -$remove_length, 11);
								$addme = ", youtube_key='".mysql_real_escape_string($video_id)."'";
								$media_url = "http://img.youtube.com/vi/".$video_id."/0.jpg";
							}
							elseif(strstr($url, 'youtu.be/'))
							{
								$youtube_url = $url;
								$position       = strpos($youtube_url, 'youtu.be/')+9;
								$remove_length  = strlen($youtube_url)-$position;
								$video_id       = substr($youtube_url, -$remove_length, 11);
								$addme = ", youtube_key='".mysql_real_escape_string($video_id)."'";
								$media_url = "http://img.youtube.com/vi/".$video_id."/0.jpg";
							}
							elseif(strstr($url, 'funnyordie.com/videos/'))
							{
								$fod_url = $url;
								$position       = strpos($fod_url, 'funnyordie.com/videos/')+22;
								$remove_length  = strlen($fod_url)-$position;
								$video_id       = substr($fod_url, -$remove_length, 10);
								$addme = ", fod_key='".mysql_real_escape_string($video_id)."'";
								$media_url = "http://www.funnyordie.com/media/".$video_id."/thumbnail/large.jpg";
							}
							elseif(strstr($url, 'videofy.me/'))
							{
								$vfy_url = $url;
								$position       = strpos($vfy_url, 'videofy.me/')+11;
								$remove_length  = strlen($vfy_url)-$position;
								$video_id       = substr($vfy_url, -$remove_length);
								$position2       = strpos($video_id, '/')+1;
								$remove_length2  = strlen($video_id)-$position2;
								$video_id2       = substr($video_id, -$remove_length2);
								$addme = ", vfy_key='".mysql_real_escape_string($video_id2)."'";
								$media_url = og_thumbnail($url);
							}
							elseif(strstr($url, 'vimeo.com/'))
							{
								$vmo_url = $url;
								$position       = strpos($vmo_url, 'vimeo.com/')+10;
								$remove_length  = strlen($vmo_url)-$position;
								$video_id       = substr($vmo_url, -$remove_length);
								$addme = ", vmo_key='".mysql_real_escape_string($video_id)."'";
								$media_url = og_thumbnail($url);
							}
							elseif(strstr($url, 'vine.co/v/'))
							{
								$vine_url = $url;
								$position       = strpos($vine_url, 'vine.co/v/')+10;
								$remove_length  = strlen($vine_url)-$position;
								$video_id       = substr($vine_url, -$remove_length, 11);
								$addme = ", vine_key='".mysql_real_escape_string($video_id)."'";
								$vine['vine_thumbnail'] = $video_id;
								$media_url = insert_get_vine_thumbnail($vine);
							}
							elseif(strstr($url, 'facebook.com/'))
							{
								if(strstr($url, 'photo.php') || strstr($url, 'video.php'))
								{
									$fbv_url = $url;
									$parts = parse_url($fbv_url);
									parse_str($parts['query'], $query);
									$video_id = $query['v'];
								}
								elseif(strstr($url, '/videos/'))
								{
									$url = rtrim(preg_replace('/\\?.*/', '', $url),"/");
									preg_match("/[^\/]+$/", $url, $matches);
									$video_id = $matches[0];
								}
								$addme = ", fbv_key='".mysql_real_escape_string($video_id)."'";
								$media_url = "https://graph.facebook.com/".$video_id."/picture";
							}
							
							$query="INSERT INTO posts SET USERID='".mysql_real_escape_string($SID)."', story='".mysql_real_escape_string($title)."', tags='".mysql_real_escape_string($tags)."', source='".mysql_real_escape_string($source)."', CID='".mysql_real_escape_string($CID)."', nsfw='".mysql_real_escape_string($nsfw)."', url='".mysql_real_escape_string($url)."', time_added='".time()."', date_added='".date("Y-m-d")."', phase_time='".time()."', active='0', pip='".$_SERVER['REMOTE_ADDR']."' $addme";
							$result=$dbconn->execute($query);
							$pid = mysql_insert_id();
							if(isset($media_url))
							{
								$randomname = generateCode(5).time();
								$uploadedimage = $config['basedir'].'/temp/'.$randomname.'-temp.jpg';
								if(download_photo($media_url, $uploadedimage))
								{
									$theimageinfo = @getimagesize($uploadedimage);
									if($theimageinfo[2] != 1 && $theimageinfo[2] != 2 && $theimageinfo[2] != 3)
									{
										$error = $errors['43'];
										unlink($uploadedimage);
									}
									else
									{
										$thepp = $pid;
										if($theimageinfo[2] == 1){$thepp .= ".gif";}
										elseif($theimageinfo[2] == 2){$thepp .= ".jpg";}
										elseif($theimageinfo[2] == 3){$thepp .= ".png";}
										$myvideoimgnew=$config['posts_dir']."/o/".$thepp;
										if(file_exists($myvideoimgnew))
										{
											unlink($myvideoimgnew);
										}
										copy($uploadedimage , $myvideoimgnew);
										do_resize_image($myvideoimgnew, "700", "0", true, $config['posts_dir']."/t/l-".$thepp);
										do_resize_image($myvideoimgnew, "300", "0", true, $config['posts_dir']."/t/s-".$thepp);
										unlink($uploadedimage);
									}
								}
								$query = "UPDATE posts SET mediafile='$thepp', favclicks='1', active='$active' WHERE PID='".mysql_real_escape_string($pid)."'";
								$dbconn->execute($query);	
							}
							$query="INSERT INTO posts_favorited SET PID='".mysql_real_escape_string($pid)."', USERID='".mysql_real_escape_string($SID)."'";
							$dbconn->execute($query);
							$FID = mysql_insert_id();
							
							$query = "UPDATE users SET points=points+$points_upload, posts=posts+1, likes_sent=likes_sent+1, likes_received=likes_received+1 WHERE USERID='".mysql_real_escape_string($SID)."'";
							$executequery=$dbconn->execute($query);
							if(!empty($CID_array))
							{
								foreach($CID_array as $CID_single)
								{
									$query = "UPDATE channels SET channel_posts=channel_posts+1 WHERE CID='".mysql_real_escape_string($CID_single)."'";
									$executequery=$dbconn->execute($query);
									$query = "INSERT INTO channels_posts SET PID='".mysql_real_escape_string($pid)."', CID='".mysql_real_escape_string($CID_single)."'";
									$executequery=$dbconn->execute($query);
								}
							}
							if($tags != "" && $config['tag_cloud_enabled'] == "1"){insert_tag_cloud($tags);}
							if($config['fb_autopost'] == "1" && $config['fb_token'] != "" && $active == "1"){fb_auto($pid);}
							update_user_timeline($SID,1,$pid,array('FID' => $FID, 'loved' => 1),0,0);
							if($config['SEO'] == 1){header("Location:".$config['baseurl'].$config['postfolder'].$pid."/".makeseo($title).".html?new=1");exit;}
							else{header("Location:".$config['baseurl'].$config['postfolder'].$pid."/?new=1");exit;}
						}
					}
					else
					{
						$error = $errors['41'];
					}
				}
				else
				{
					if($title == "")
					{
						$error = $errors['40'];
					}
						
					if(!isset($error))
					{
						$pos = strrpos($url,".");
						$ph = strtolower(substr($url,$pos+1,strlen($url)-$pos));
							
						if($ph == "jpg" || $ph == "jpeg" || $ph == "png" || $ph == "gif")
						{

							$randomname = generateCode(5).time();
							$uploadedimage = $config['basedir'].'/temp/'.$randomname.'-temp.'.$ph;
							if(!download_photo($url, $uploadedimage))
							{
								$error = $errors['42'];
							}
							else
							{							
								$theimageinfo = @getimagesize($uploadedimage);
								if($theimageinfo[2] != 1 && $theimageinfo[2] != 2 && $theimageinfo[2] != 3)
								{
									$error = $errors['43'];
									unlink($uploadedimage);
								}
								else
								{
									$query="INSERT INTO posts SET USERID='".mysql_real_escape_string($SID)."', story='".mysql_real_escape_string($title)."', tags='".mysql_real_escape_string($tags)."', source='".mysql_real_escape_string($source)."', CID='".mysql_real_escape_string($CID)."', nsfw='".mysql_real_escape_string($nsfw)."', url='".mysql_real_escape_string($url)."', time_added='".time()."', date_added='".date("Y-m-d")."', phase_time='".time()."', active='0', pip='".$_SERVER['REMOTE_ADDR']."'";
									$result=$dbconn->execute($query);
									$pid = mysql_insert_id();
										
									if($uploadedimage != "")
									{
										$thepp = $pid;
										if($theimageinfo[2] == 1)
										{
											$thepp .= ".gif";
											$thepp2 = ".gif";
											if(isAnimatedGif($uploadedimage))
											{
												$animated_post = "1";
												$gif_width = $theimageinfo[0];
											}
											else
											{
												$animated_post = "0";
											}
										}
										elseif($theimageinfo[2] == 2)
										{
											$thepp .= ".jpg";
											$thepp2 = ".jpg";
											$animated_post = "0";
										}
										elseif($theimageinfo[2] == 3)
										{
											$thepp .= ".png";
											$thepp2 = ".png";
											$animated_post = "0";
										}
										if(!isset($error))
										{
											$myvideoimgnew=$config['posts_dir']."/o/".$thepp;
											if(file_exists($myvideoimgnew))
											{
												unlink($myvideoimgnew);
											}
											copy($uploadedimage , $myvideoimgnew);
											if($animated_post != "1")
											{
												$mediafile = $thepp;
												do_resize_image($myvideoimgnew, "700", "0", true, $config['posts_dir']."/t/l-".$thepp);
												do_resize_image($myvideoimgnew, "500", "0", true, $config['posts_dir']."/t/".$thepp);
												$pic_info = @getimagesize($config['posts_dir']."/t/".$thepp);
												$pic_height = $pic_info[1];
												do_resize_image($myvideoimgnew, "300", "0", true, $config['posts_dir']."/t/s-".$thepp);
											}
											else
											{
												$mediafile = $thepp.".jpg";
												if($gif_width > 750)
												{
													imagick_gif_resize($myvideoimgnew, "700", "0", true, $config['posts_dir']."/t/l-".$thepp, $config['posts_dir']."/t/z-".$thepp);
												}
												else
												{
													copy($myvideoimgnew , $config['posts_dir']."/t/l-".$thepp);
												}
												do_resize_image($config['posts_dir']."/t/l-".$thepp, "700", "0", true, $config['posts_dir']."/t/l-".$thepp.".jpg");
												if($gif_width > 550)
												{
													imagick_gif_resize($myvideoimgnew, "500", "0", true, $config['posts_dir']."/t/".$thepp, $config['posts_dir']."/t/z-".$thepp);
												}
												else
												{
													copy($myvideoimgnew , $config['posts_dir']."/t/".$thepp);
												}
												do_resize_image($config['posts_dir']."/t/".$thepp, "500", "0", true, $config['posts_dir']."/t/".$thepp.".jpg");
												$pic_info = @getimagesize($config['posts_dir']."/t/".$thepp.".jpg");
												$pic_height = $pic_info[1];
												if($gif_width > 400)
												{
													imagick_gif_resize($myvideoimgnew, "300", "0", true, $config['posts_dir']."/t/s-".$thepp, $config['posts_dir']."/t/z-".$thepp);
												}
												else
												{
													copy($myvideoimgnew , $config['posts_dir']."/t/s-".$thepp);
												}
												do_resize_image($config['posts_dir']."/t/".$thepp, "300", "0", true, $config['posts_dir']."/t/s-".$thepp.".jpg");
											}
											if(file_exists($config['posts_dir']."/o/".$thepp))
											{
												if($thepp2 == ".png")
												{
													$img=imagecreatefrompng($config['posts_dir']."/t/l-".$thepp);
													$img2=imagecreatefrompng($config['posts_dir']."/t/".$thepp);
												}
												elseif($thepp2 == ".jpg")
												{
													$img=imagecreatefromjpeg($config['posts_dir']."/t/l-".$thepp);
													$img2=imagecreatefromjpeg($config['posts_dir']."/t/".$thepp);
												}
												elseif($thepp2 == ".gif" && $animated_post != "1")
												{
													$img=imagecreatefromgif($config['posts_dir']."/t/l-".$thepp);
													$img2=imagecreatefromgif($config['posts_dir']."/t/".$thepp);
												}
												else
												{
													$wm_skip = "1";
												}
												if(!isset($wm_skip))												
												{
													if($config['twm'] == "1")
													{
														create_text_watermark($img,$pid,$thepp,$thepp2,1);
														create_text_watermark($img2,$pid,$thepp,$thepp2,0);
													}
													elseif($config['lwm'] == "1")
													{	
														create_logo_watermark($img,$thepp,1);
														create_logo_watermark($img2,$thepp,0);
													}
												}
													
												$query = "UPDATE posts SET pic='$thepp', pic_height='$pic_height', mediafile='$mediafile', gif='$animated_post', favclicks='1', active='$active' WHERE PID='".mysql_real_escape_string($pid)."'";
												$dbconn->execute($query);		
												$query="INSERT INTO posts_favorited SET PID='".mysql_real_escape_string($pid)."', USERID='".mysql_real_escape_string($SID)."'";
												$dbconn->execute($query);
												$FID = mysql_insert_id();
												
												$query = "UPDATE users SET points=points+$points_upload, posts=posts+1, likes_sent=likes_sent+1, likes_received=likes_received+1 WHERE USERID='".mysql_real_escape_string($SID)."'";
												$executequery=$dbconn->execute($query);
												if(!empty($CID_array))
												{
													foreach($CID_array as $CID_single)
													{
														$query = "UPDATE channels SET channel_posts=channel_posts+1 WHERE CID='".mysql_real_escape_string($CID_single)."'";
														$executequery=$dbconn->execute($query);
														$query = "INSERT INTO channels_posts SET PID='".mysql_real_escape_string($pid)."', CID='".mysql_real_escape_string($CID_single)."'";
														$executequery=$dbconn->execute($query);
													}
												}
												unlink($uploadedimage);
												if(isset($wm_skip) && $config['convert_gif2mp4_enabled'] == "1"){convert_gif_mp4($thepp);}
												if($tags != "" && $config['tag_cloud_enabled'] == "1"){insert_tag_cloud($tags);}
												if($config['fb_autopost'] == "1" && $config['fb_token'] != "" && $active == "1"){fb_auto($pid);}
												update_user_timeline($SID,1,$pid,array('FID' => $FID, 'loved' => 1),0,0);
												if($config['SEO'] == 1){header("Location:".$config['baseurl'].$config['postfolder'].$pid."/".makeseo($title).".html?new=1");exit;}
												else{header("Location:".$config['baseurl'].$config['postfolder'].$pid."/?new=1");exit;}
											}
										}
									}	
								}
							}
						}
						else
						{
							$error = $errors['44'];
						}
					}
				}
			}
			STemplate::assign('url_selected',1);
		}
		else
		{
			if($submit_sec == "1")
			{
				if(isset($_FILES['image']['tmp_name'])){$uploadedimage = $_FILES['image']['tmp_name'];}else{$uploadedimage = "";}
				if($uploadedimage == "")
				{
					$error = $errors['45'];
				}
				else
				{
					$theimageinfo = @getimagesize($uploadedimage);
					if($theimageinfo[2] != 1 && $theimageinfo[2] != 2 && $theimageinfo[2] != 3)
					{
						$error = $errors['43'];
					}
					else
					{
						if($title == "")
						{
							$error = $errors['40'];
						}
						else
						{
							$query="INSERT INTO posts SET USERID='".mysql_real_escape_string($SID)."', story='".mysql_real_escape_string($title)."', tags='".mysql_real_escape_string($tags)."', source='".mysql_real_escape_string($source)."', CID='".mysql_real_escape_string($CID)."', nsfw='".mysql_real_escape_string($nsfw)."', time_added='".time()."', date_added='".date("Y-m-d")."', phase_time='".time()."', active='0', pip='".$_SERVER['REMOTE_ADDR']."'";
							$result=$dbconn->execute($query);
							$pid = mysql_insert_id();
							
							if($uploadedimage != "")
							{
								$thepp = $pid;
								if($theimageinfo[2] == 1)
								{
									$thepp .= ".gif";
									$thepp2 = ".gif";
									if(isAnimatedGif($uploadedimage))
									{
										$animated_post = "1";
										$gif_width = $theimageinfo[0];
									}
									else
									{
										$animated_post = "0";
									}
								}
								elseif($theimageinfo[2] == 2)
								{
									$thepp .= ".jpg";
									$thepp2 = ".jpg";
									$animated_post = "0";
								}
								elseif($theimageinfo[2] == 3)
								{
									$thepp .= ".png";
									$thepp2 = ".png";
									$animated_post = "0";
								}
								if(!isset($error))
								{
									$myvideoimgnew=$config['posts_dir']."/o/".$thepp;
									if(file_exists($myvideoimgnew))
									{
										unlink($myvideoimgnew);
									}
									$myconvertimg = $_FILES['image']['tmp_name'];
									move_uploaded_file($myconvertimg, $myvideoimgnew);
									if($animated_post != "1")
									{
										$mediafile = $thepp;
										do_resize_image($myvideoimgnew, "700", "0", true, $config['posts_dir']."/t/l-".$thepp);
										do_resize_image($myvideoimgnew, "500", "0", true, $config['posts_dir']."/t/".$thepp);
										$pic_info = @getimagesize($config['posts_dir']."/t/".$thepp);
										$pic_height = $pic_info[1];
										do_resize_image($myvideoimgnew, "300", "0", true, $config['posts_dir']."/t/s-".$thepp);
									}
									else
									{
										$mediafile = $thepp.".jpg";
										if($gif_width > 750)
										{
											imagick_gif_resize($myvideoimgnew, "700", "0", true, $config['posts_dir']."/t/l-".$thepp, $config['posts_dir']."/t/z-".$thepp);
										}
										else
										{
											copy($myvideoimgnew , $config['posts_dir']."/t/l-".$thepp);
										}
										do_resize_image($config['posts_dir']."/t/l-".$thepp, "700", "0", true, $config['posts_dir']."/t/l-".$thepp.".jpg");
										if($gif_width > 850)
										{
											imagick_gif_resize($myvideoimgnew, "500", "0", true, $config['posts_dir']."/t/".$thepp, $config['posts_dir']."/t/z-".$thepp);
										}
										else
										{
											copy($myvideoimgnew , $config['posts_dir']."/t/".$thepp);
										}
										do_resize_image($config['posts_dir']."/t/".$thepp, "500", "0", true, $config['posts_dir']."/t/".$thepp.".jpg");
										$pic_info = @getimagesize($config['posts_dir']."/t/".$thepp.".jpg");
										$pic_height = $pic_info[1];
										if($gif_width > 400)
										{
											imagick_gif_resize($myvideoimgnew, "300", "0", true, $config['posts_dir']."/t/s-".$thepp, $config['posts_dir']."/t/z-".$thepp);
										}
										else
										{
											copy($myvideoimgnew , $config['posts_dir']."/t/s-".$thepp);
										}
										do_resize_image($config['posts_dir']."/t/".$thepp, "300", "0", true, $config['posts_dir']."/t/s-".$thepp.".jpg");
									}
									if(file_exists($config['posts_dir']."/o/".$thepp))
									{
										if($thepp2 == ".png")
										{
											$img=imagecreatefrompng($config['posts_dir']."/t/l-".$thepp);
											$img2=imagecreatefrompng($config['posts_dir']."/t/".$thepp);
										}
										elseif($thepp2 == ".jpg")
										{
											$img=imagecreatefromjpeg($config['posts_dir']."/t/l-".$thepp);
											$img2=imagecreatefromjpeg($config['posts_dir']."/t/".$thepp);
										}
										elseif($thepp2 == ".gif" && $animated_post != "1")
										{
											$img=imagecreatefromgif($config['posts_dir']."/t/l-".$thepp);
											$img2=imagecreatefromgif($config['posts_dir']."/t/".$thepp);
										}
										else
										{
											$wm_skip = "1";
										}
										if(!isset($wm_skip))											
										{
											if($config['twm'] == "1")
											{
												create_text_watermark($img,$pid,$thepp,$thepp2,1);
												create_text_watermark($img2,$pid,$thepp,$thepp2,0);
											}
											elseif($config['lwm'] == "1")
											{	
												create_logo_watermark($img,$thepp,1);
												create_logo_watermark($img2,$thepp,0);
											}
										}

										$query = "UPDATE posts SET pic='$thepp', pic_height='$pic_height', mediafile='$mediafile', gif='$animated_post', favclicks='1', active='$active' WHERE PID='".mysql_real_escape_string($pid)."'";
										$dbconn->execute($query);			
										$query="INSERT INTO posts_favorited SET PID='".mysql_real_escape_string($pid)."', USERID='".mysql_real_escape_string($SID)."'";
										$dbconn->execute($query);
										$FID = mysql_insert_id();
										
										$query = "UPDATE users SET points=points+$points_upload, posts=posts+1, likes_sent=likes_sent+1, likes_received=likes_received+1 WHERE USERID='".mysql_real_escape_string($SID)."'";
										$executequery=$dbconn->execute($query);
										if(!empty($CID_array))
										{
											foreach($CID_array as $CID_single)
											{
												$query = "UPDATE channels SET channel_posts=channel_posts+1 WHERE CID='".mysql_real_escape_string($CID_single)."'";
												$executequery=$dbconn->execute($query);
												$query = "INSERT INTO channels_posts SET PID='".mysql_real_escape_string($pid)."', CID='".mysql_real_escape_string($CID_single)."'";
												$executequery=$dbconn->execute($query);
											}
										}
										if(isset($wm_skip) && $config['convert_gif2mp4_enabled'] == "1"){convert_gif_mp4($thepp);}
										if($tags != "" && $config['tag_cloud_enabled'] == "1"){insert_tag_cloud($tags);}
										if($config['fb_autopost'] == "1" && $config['fb_token'] != "" && $active == "1"){fb_auto($pid);}
										update_user_timeline($SID,1,$pid,array('FID' => $FID, 'loved' => 1),0,0);
										if($config['SEO'] == 1){header("Location:".$config['baseurl'].$config['postfolder'].$pid."/".makeseo($title).".html?new=1");exit;}
										else{header("Location:".$config['baseurl'].$config['postfolder'].$pid."/?new=1");exit;}
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
elseif ($SID != "" && $SID >= 0 && is_numeric($SID))
{
	$error = $errors['49'];
	$template = 'empty.tpl';
}
else
{
	header("Location:".$config['baseurl']."/login");exit;
}

if(isset($error))
{
	if(isset($url)){STemplate::assign('url',$url);}
	if(isset($title)){STemplate::assign('title',$title);}
	if(isset($CID_array)){STemplate::assign('CID_array',$CID_array);}
	if(isset($tags)){STemplate::assign('tags',$tags);}
	if(isset($nsfw)){STemplate::assign('nsfw',$nsfw);}
	if(isset($source)){STemplate::assign('source',$source);}
}

if ($config['channels'] == 1)
{
$cats = loadallchannels();
STemplate::assign('allchannels',$cats);

}

$_SESSION['location'] = "/submit";

//TEMPLATES BEGIN
STemplate::assign('menu',6);
STemplate::assign('nosectionnav',1);
STemplate::assign('norightside',1);
STemplate::assign('footerlinks',1);
STemplate::assign('no_submit_popup',1);
if(isset($error)){STemplate::assign('error',$error);}
STemplate::display('header.tpl');
STemplate::display($template);
STemplate::display('footer.tpl');
//TEMPLATES END
?>
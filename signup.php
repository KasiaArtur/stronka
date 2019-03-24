<?php


include("inc/include.php");

//TEMPLATES BEGIN
STemplate::assign('pagetitle',$titles['10']);
STemplate::assign('nosectionnav',1);
STemplate::assign('norightside',1);
STemplate::assign('footerlinks',1);
STemplate::assign('no_signup_popup',1);
STemplate::display('header.tpl');
STemplate::display('signup.tpl');
STemplate::display('footer.tpl');
//TEMPLATES END
?>
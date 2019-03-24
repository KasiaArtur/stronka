<?php


$banned = "1";
include("inc/include.php");
//TEMPLATES BEGIN
STemplate::assign('nosectionnav',1);
STemplate::assign('norightside',1);
STemplate::assign('footerlinks',1);
STemplate::display("header.tpl");
STemplate::display("banned.tpl");
STemplate::display("footer.tpl");
//TEMPLATES END
?>
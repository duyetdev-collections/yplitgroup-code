<?php

require_once('f.php');

!empty($_GET['l'])?$l=$_GET['l']:die("<b>Error link!!!</b><br><i>Do not exist url!</i><br><i>l</i> param to input URL!");

$bot = new BOT($l);
//$bot->getAllPage();
$badlink = array('http://www.vbulletin.com','http://google.com','http://google.com.vn');
$bot->__badlink($badlink);
$bot->__badhost($badlink);
$bot->get('Link');

// BUGS: Show result

echo "<pre>";
print_r('HOst: '.$bot->__siteInfo['host']); echo '<br><br>';
echo "World link: <br>";
print_r($bot->_world_link); echo '<br><br>';
echo "Page link: <br>";
print_r($bot->_current_link); echo '<br><br>';
//echo "</pre>";


// Advanted: Secondary get link from list current link



/*



foreach( $bot->_current_link as $_next_link )
{
	echo( $bot->__siteInfo['url'] . '/' . $_next_link . "<br>");

}

*/
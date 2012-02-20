<?php
/**
 * @Project WAP_TRUYEN 1.0.0
 * @Author Yplitgroup (duyet2000@gmail.com)
 * @Copyright (C) 2012 Yplitgroup.  All rights reserved
 * @Phone: 0166.26.26.009 - 0122.66.26.009
 * @Website: http://lemon9x.com
 */
if( !defined('YPLITGROUP') ) die( 'Stop!!! <br /><i>Power by Yplitgroup</i><hr />' );

$header = "<html>";
$header	.="<head>";
$header	.= "<title>";
if( isset($page_title) )
{
$header .= $page_title . " - ";
}

$header .= $lang['page_title'];
$header .= "</title>";
$header .= "\n<!-- Power by Yplitgroup -->\n";
$header	.= "<link rel=\"stylesheet\" type=\"text/css\" href=\"skin/". $config['skin'] ."/css.css\" media=\"screen\"/>";
$header	.= "</head>";
$header	.= "<body>";
$header	.= "<center><div class=\"main\">";
$header	.= 	"<div class=\"logo\">";
$header	.=		"<a href=\"./index.php\" alt=\"". $lang['header_title'] ."\" title=\"". $lang['header_title'] ."\">" . $lang['header_title'] . "</a>";
$header	.=	"</div>";


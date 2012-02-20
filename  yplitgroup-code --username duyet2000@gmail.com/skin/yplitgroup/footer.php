<?php
/**
 * @Project WAP_TRUYEN 1.0.0
 * @Author Yplitgroup (duyet2000@gmail.com)
 * @Copyright (C) 2012 Yplitgroup.  All rights reserved
 * @Phone: 0166.26.26.009 - 0122.66.26.009
 * @Website: http://lemon9x.com
 */

$back_page = false;
if( isset( $_SERVER['HTTP_REFERER'] ) AND !empty( $_SERVER['HTTP_REFERER'] ) AND ( $_SERVER['HTTP_REFERER'] != $_SERVER['REQUEST_URI'] ) )
{
	$back_page = "<a href=\"". $_SERVER['HTTP_REFERER'] ."\">" . $lang['back'] . " - </a>";
}
else
{
	$back_page = "";
}

if( !empty( $_SESSION['admin_yplit'] ) )
{
	$admin_link =  "<a href=\"admin.php\">Admin Home</a> - ";
}
else
{
	$admin_link =  "<a href=\"admin.php\">Admin Login</a> - ";
}


$footer = "<div class=\"back-to-top\">". $admin_link . $back_page ."<a href=\"#\">". $lang['back_to_top'] ."</a></div>";
$footer .= "<div class=\"footer\">(c) Yplitgroup</div>\n";
$footer .= "</div></center>\n";
$footer .= "</body>\n";
$footer .= "</html>";
<?php
/**
 * @Project WAP_TRUYEN 1.0.0
 * @Author Yplitgroup (duyet2000@gmail.com)
 * @Copyright (C) 2012 Yplitgroup.  All rights reserved
 * @Phone: 0166.26.26.009 - 0122.66.26.009
 * @Website: http://lemon9x.com
 */

		
if( $showthread == true)
{
	$content = "<div class=\"thread_title\">" . $thread_title . "</div>\n";
}
else
{
	$content = "";
}

if( $showthread == true)
{
	$content .= "<div class=\"counter\">" . $lang['counter'] . ": " . $thread_counter . "</div>\n";
}
else
{
	$content .= "<div class=\"thread_counter\">" . $lang['counter'] . ": " . counter() . "</div>\n";
}

if( $showthread == false )
{
	$content .= "<span class=\"header_truyen\"></span>";
}

if( $showthread == true)
{
	$content .= "<div class=\"thread_content\">" . $thread_content . "</div>\n";
}
else
{
	$content .= "<div class=\"content\">" . $home_content . "</div>\n";
}
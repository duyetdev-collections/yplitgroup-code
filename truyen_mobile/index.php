<?php
/**
 * @Project WAP_TRUYEN 1.0.0
 * @Author Yplitgroup (duyet2000@gmail.com)
 * @Copyright (C) 2012 Yplitgroup.  All rights reserved
 * @Phone: 0166.26.26.009 - 0122.66.26.009
 * @Website: http://lemon9x.com
 */
session_start();
define('YPLITGROUP', true);

// Ket noi cac file
require_once('config.php');
require_once('function.php');
if( file_exists('language/') )
require_once('language/' . $config['lang'] . '.php' );


// Contruct
$db = new sql($config);

// Xac dinh id
if( isset( $_GET['id'] ) AND !empty( $_GET['id'] ) )
{
	$thread_id = intval( $_GET['id'] );
}

// If: id --> showthread Else: --> Homepage

if( isset( $thread_id ) )
{ // Show thread
	$showthread = true;
	$q = "SELECT * FROM `thread` WHERE `id` = " . $thread_id . " LIMIT 1";
	$db->sql_query( $q );
	$result = $db->sql_fetch_assoc(  );
	$page_title = $result['title'];
	$thread_title = $result['title'];
	$thread_counter = $result['counter'];
	$thread_content = nl2br($result['content']);
	if($_SESSION['admin_yplit'])
	{
		$thread_content .= "<br>(<a href=\"admin.php?cmd=edit&id=". $thread_id ."\">" . $lang['edit']. ")</a>";
	}
	
	show(  );
	
	// Update counter
	$q = "UPDATE `thread` SET `counter` = `counter`+1  WHERE `id` = " . $thread_id;
	$db->sql_query( $q );
	echo $db->sql_error;
}
else
{ // Show homepage
	$showthread = false;
	if(isset( $_GET['p'] ))
	{
		$limit = $p = (int) $_GET['p'];
	}
	else
	{
		$limit = 1;
		$p = 1;
	}
	if($limit==1)
	{
		$limit = 0;
	}
	$limit *= 20;
	
	// Order
	$order = "";
	if( !isset( $_GET['order'] ) )
	{
		$_GET['order'] = 'most';
	}
	if( isset( $_GET['order'] ) )
	{
		switch( $_GET['order'] )
		{
			case "Az":
			case "az":
			case "AZ":
				$order = " ORDER BY `title` "; 
			break;
			case "id":
				$order = " ORDER BY `id` ";
			break;
			case "id_desc":
				$order = " ORDER BY `id` DESC ";
			break;
			case "most":
				$order = " ORDER BY `counter` DESC ";
			break;
			
		}
	}

	
	$q = "SELECT `id`, `title`, `counter` FROM `thread` ";
	if( !empty( $order ) )
	{
		$q .= $order;
	}
	$q .= "LIMIT ". $limit . ", 20";
	

	$db->sql_query( $q );
		// Empty page
	if( $db->sql_numrows(  )  == 0 )
	{
		// Empty
		$showthread = false;
		$home_content = $lang['none'];
		show(  );
		exit;
	}
	$a = $p;
	if($p>1)
	{
		$a = ($p-1)*20+1;
	}
	$c = "";
	while( $row = $db->sql_fetch_assoc(  ) )
	{
		$title = short_title( $row['title'], 30 );
		$c .= "<div class=\"home_row". ($a%2==0?" a2\"":" a1\"") .">";
		$c .= "$a. <a href=\"index.php?id=". $row['id'] ."\">";
		$c .= $title;
		$c .= "</a>";
		if( $_GET['order'] == 'most')
		{
			$c .= " (". $row['counter'] .") ";
		}
		$c .= "</div>";
		$a++;
	}
	
	// Phan trang

	$q = "SELECT `id` FROM `thread`";
	$db->sql_query( $q );
	$n = $db->sql_numrows( );
	$_n = (int)($n/20);
	$home_content = "";
	if($_n>1)
	{
		$home_content .= "<div class=\"break_page\">";
		if( $p>1 )
		{
			$home_content .= " <a href=\"index.php?p=". ($p-1) . (!empty($order)?"&order=".$_GET['order']:"") . "\">" . $lang['pre'] . "</a> ";
		}
		for($i=1; $i<=$_n; $i++)
		{
			if($p == $i)
			{
				$home_content .= " <b>" . $i . "</b> ";
			}
			else
			{
				$home_content .= " <a href=\"index.php?p=". $i . (!empty($order)?"&order=".$_GET['order']:"") ."\">" . $i . "</a> ";
			}
		}
		if( $p<$_n )
		{
			$home_content .= " <a href=\"index.php?p=". ($p+1) . (!empty($order)?"&order=".$_GET['order']:"") ."\">" . $lang['next'] . "</a> ";
		}
		$home_content .= "</div>";
	}
	// Order choose
	$order_choose = "<div class=\"order_choose\">";
	$order_choose .= "<a href=\"index.php?order=az". ($p?"&p=".$p:'') ."\">Az</a> &nbsp; ";
	$order_choose .= "<a href=\"index.php?order=id". ($p?"&p=".$p:'') ."\">Id</a> &nbsp; ";
	$order_choose .= "<a href=\"index.php?order=id_desc". ($p?"&p=".$p:'') ."\">Id-Desc</a> &nbsp; ";
	$order_choose .= "<a href=\"index.php?order=most". ($p?"&p=".$p:'') ."\">Most</a>";
	$order_choose .= "</div>";
	$home_content = $order_choose . $c . $home_content;
	show(  );
}


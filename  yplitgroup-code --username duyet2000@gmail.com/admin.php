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

if(isset($_GET['pass']))
{
	die(s_hash($_GET['pass']));
}

// Contruct
$db = new sql($config);

// Log out
if( isset($_GET['logout']) )
{
	session_destroy();
			$showthread = false;
			$page_title = $lang['logout'];
			$home_content = $lang['logout_ok'];
			show(  );
			exit;
	
}

if( !empty( $_SESSION['admin_yplit'] ) )
{	// Da dang nhap
	if( !isset($_GET['cmd']) )
	{ //menu
			$showthread = false;
			$page_title = $lang['manager'];
			$home_content = "<div class=\"manager\">";
			$home_content .= "<a href=\"admin.php?cmd=manager\">".  $lang['manager'] ."</a><br>";
			$home_content .= "<a href=\"admin.php?cmd=add\">". $lang['add'] ."</a><br>";
			$home_content .= "</div>";
			$home_content .= "<div class=\"change_pass\"><a href=\"admin.php?cmd=change_pass\">" . $lang['change_pass'] . "</a></div>";
			$home_content .= "<div class=\"logout\"><a href=\"admin.php?logout\">". $lang['admin_logout'] ."</a></div>";
			show(  );
			exit;
	}
	else
	{ // cmd
	if( !empty( $_GET['cmd'] ) )
	{
		switch( $_GET['cmd'] )
		{
			case "manager":
				// Manager
				if( $_GET['p'] )
				{
					$p = (int)$_GET['p'];
				}
				else
				{
					$p = 1;
				}
				$q = "SELECT `id`, `title`, `counter` FROM `thread`";
				if( $p > 1 )
				{
					$q .= "LIMIT ". $p*20 . ", 20";
				}
				else
				{
					$q .= "LIMIT 0, 20";
				}
				
				$db->sql_query( $q );
				$home_content = "<div class=\"manager_content\">";
				$a = 1;
				while( $r = $db->sql_fetch_assoc(  ) )
				{
					$home_content .= "<a href=\"index.php?id=". $r['id'] ."\" class=\"". ($a%2==0?" m_a2\"":" m_a1\"") .">" . short_title($r['title']) . "</a>  &nbsp; <a href=\"admin.php?cmd=edit&id=". $r['id'] ."\">". $lang['edit'] ."</a> &nbsp; <a href=\"admin.php?cmd=del&id=". $r['id'] ."\">" . $lang['del'] . "</a><br>";
				}
				$home_content .= "</div>";
				
				// Phan trang
				$q = "SELECT `id` FROM `thread`";
				$db->sql_query( $q );
				$n = $db->sql_numrows( );
				$_n = (int)($n/20);
				if($_n>1)
				{
					$home_content .= "<div class=\"break_page\">";
					if( $p>1 )
					{
						$home_content .= " <a href=\"admin.php?cmd=manager&p=". ($p-1) ."\">" . $lang['pre'] . "</a> ";
					}
					for($i=1; $i<=$_n; $i++)
					{
						if($p == $i)
						{
							$home_content .= " <b>" . $i . "</b> ";
						}
						else
						{
							$home_content .= " <a href=\"admin.php?cmd=manager&p=". $i ."\">" . $i . "</a> ";
						}
					}
					if( $p<$_n )
					{
						$home_content .= " <a href=\"admin.php?cmd=manager&p=". ($p+1) ."\">" . $lang['next'] . "</a> ";
					}
					$home_content .= "</div>";
				}
				$page_title = $lang['manager'];
				show(  );
				
			break;
			
			case "add":
				// Add
				if( !$_POST['submit'] )
				{ // Form
					$showthread = false;
					$page_title = $lang['add'];
					$home_content = "<div class=\"add\">";
					$home_content .= "<form action=\"admin.php?cmd=add\" method=\"POST\">";
					$home_content .= "Title: <input type=\"text\" name=\"title\" value=\"\" size=\"25\"/><br>";
					$home_content .= "Content: <br>";
					$home_content .= "<textarea name=\"content\" rows=\"6\" cols=\"15\"></textarea><br>";
					$home_content .= "<input type=\"submit\" name=\"submit\" value=\"Sunmit\" />";
					$home_content .= "</form></div>";
					show(  );
					exit;
				}
				else
				{ // Xu li
					if( empty($_POST['title']) )
					{
						$showthread = false;
						$page_title = $lang['add'];
						$home_content = $lang['title_empty'];
						show(  );
						exit;
					}
					else
					{
						$thread_title = addslashes( $_POST['title'] );
						$thread_content = addslashes( $_POST['content'] );
						$q = "INSERT INTO `thread`(`title`, `content`, `counter`) VALUE (". $db->dbescape($thread_title) . ", ". $db->dbescape($thread_content) . ", 1);";
						$db->sql_query( $q );
						$showthread = false;
						$page_title = $lang['add'];
						$home_content = $lang['add_ok'];
						show(  );
						exit;
						}
				}
			break;
			
			case "del":
				// Add
				if( isset($_GET['id']) )
				{
					$id = (int)$_GET['id'];
				}
				$q = "DELETE FROM `thread` WHERE `id` = " . $id;
				$db->sql_query( $q );
				if( !$db->error )
				{
					$showthread = false;
					$page_title = $lang['del'];
					$home_content = sprintf($lang['del_ok'], $id);
					show(  ); 
				
				}
				else
				{
					$showthread = false;
					$page_title = $lang['err'];
					$home_content = $lang['del_fail'];
					show(  );
					exit;
					
				}
				
			break;
			
			case "edit":
				// Edit
				if(!isset( $_GET['id'] ))
				{
					header('Location: admin.php?cmd=manager');
				}
				else
				{
					if(!isset($_POST['submit']))
					{ // Edit page
						$id = (int) $_GET['id'];
						$q = "SELECT * FROM `thread` WHERE `id` = " . $id;
						$db->sql_query( $q );
						if( $db->sql_numrows(  ) == 0)
						{ // Not exist
							$showthread = false;
							$page_title = $lang['err'];
							$home_content = $lang['not_exits'];
							show(  );
							exit;
						}
						else
						{ // Form with last data
							$r = $db->sql_fetch_assoc(  );
							$showthread = false;
							$page_title = $lang['edit'];
							$home_content = "<div class=\"edit\">";
							$home_content .= "<form action=\"admin.php?cmd=edit&id=". $id ."\" method=\"POST\">";
							$home_content .= "<input type=\"hidden\" name=\"id\" value=\"". $id ."\" />";
							$home_content .= "Title: <input type=\"text\" name=\"title\" value=\"". $r['title'] ."\" size=\"25\"/><br>";
							$home_content .= "Content: <br>";
							$home_content .= "<textarea name=\"content\" rows=\"6\" cols=\"15\">" . $r['content'] . "</textarea><br>";
							$home_content .= "<input type=\"submit\" name=\"submit\" value=\"Submit\" />";
							$home_content .= "</form></div>";
							show(  );
							exit;
						}
					}
					else
					{  // Xu li 
						if( empty($_POST['title']) )
						{
							$showthread = false;
							$page_title = $lang['edit'];
							$home_content = $lang['title_empty'];
							show(  );
							exit;
						}
						else
						{
							$thread_id = $_POST['id']?(int)$_POST['id']:(int)$_GET['id'];
							$thread_title = $db->dbescape( $_POST['title'] );
							$thread_content = $db->dbescape( $_POST['content'] );
							$q = "UPDATE `thread` SET `title` = ". $thread_title . ", `content` = ". $thread_content . " WHERE `id` = " . $thread_id;
							$db->sql_query( $q );
							$showthread = false;
							$page_title = $lang['edit'];
							$home_content = $lang['edit_ok'];
							show(  );
							exit;
						}
					}
				}
			break;
			case "change_pass":
				if( !isset($_POST['submit']) )
				{ // Form
					$showthread = false;
					$page_title = $lang['change_pass'];
					$home_content = "<form action=\"admin.php?cmd=change_pass\" method=\"POST\">";
					$home_content .= $lang['new_pass'] . ": <input name=\"pass\" type=\"password\" value=\"\" /><br>";
					$home_content .= "<input type=\"submit\" value=\"Change\" name=\"submit\" />";
					$home_content .= "</form>";
					show(  );
					exit;
				}
				else
				{
					if( empty( $_POST['pass'] ) )
					{  // empty
						$showthread = false;
						$page_title = $lang['err'];
						$home_content = $lang['new_pass_empty'];
						show(  );
						exit;
					}
					else
					{ // Xu li request
						$new_pass = addslashes( $_POST['pass'] );
						$new_pass = s_hash($new_pass);
						$q = "UPDATE `user` SET `pass` = '". $new_pass . "' WHERE `user` = 'admin'";
						$db->sql_query( $q );
						$showthread = false;
						$page_title = $lang['change_pass'];
						$home_content = $lang['change_pass_ok'];
						show(  );
						exit;
					}
				}
			break;
		}
	}
	
	}
}
else
{	// Chua dang nhap
	if( $_POST['submit'] )
	{	// Logining .......
		if( empty( $_POST['user'] ) OR empty( $_POST['password'] ) )
		{
			$showthread = false;
			$page_title = $lang['login'];
			$home_content = $lang['user_or_pass_empty'];
			show(  );
			exit;
		}
		else
		{
			$p_user = $db->dbescape( $_POST['user'] ); 
			$p_pass =  $_POST['password'] ;
			$db->sql_query( "SELECT * FROM `user` WHERE `user` = ". $p_user . "" );
			if( $db->sql_numrows() == 0 )
			{ // Sai user
				$showthread = false;
				$page_title = $lang['login'];
				$home_content = $lang['user_incorect'];
				show(  );
				exit;
			}
			else
			{
				$r = $db->sql_fetch_assoc(  );
				//print_r($r);
				if($r['pass'] != s_hash($p_pass))
				{	// Sai pass
					$showthread = false;
					$page_title = $lang['login'];
					$home_content = $lang['pass_incorect'];
					show(  );
					exit;
				}
				else
				{	// Dang nhap thanh cong
					$_SESSION['admin_yplit'] = true;
					$showthread = false;
					$page_title = $lang['admin'];
					$home_content = $lang['login_ok'];
					show(  );
					exit;
				}
			}
		}
	}
	else
	{	// View form
		
		
		$showthread = false;
		$page_title = $lang['login'];
		$home_content = "<b>Login</b><br>";
		$home_content .= "<form method=\"POST\">";
		$home_content .= "<input name=\"user\" maxleght=\"30\" type=\"text\">";
		$home_content .= "<input name=\"password\" type=\"password\">";
		$home_content .= "<input name=\"submit\" type=\"submit\" value=\"Login\">";
		$home_content .= "</form>";
		
		show(  );
		exit;
	}
}
<?php
/**
 * @Project WAP_TRUYEN 1.0.0
 * @Author Yplitgroup (duyet2000@gmail.com)
 * @Copyright (C) 2012 Yplitgroup.  All rights reserved
 * @Phone: 0166.26.26.009 - 0122.66.26.009
 * @Website: http://lemon9x.com
 */
if( !defined('YPLITGROUP') ) die( 'Stop!!! <br /><i>Power by Yplitgroup</i><hr />' );

/*
* Class SQL
*/
class sql
{

    const NOT_CONNECT_TO_MYSQL_SERVER = 'Sorry! Could not connect to mysql server';

    const DATABASE_NAME_IS_EMPTY = 'Error! The database name is not the connection name';

    const UNKNOWN_DATABASE = 'Error! Unknown database';

    public $server = 'localhost';

    public $user = 'root';

    public $dbname = '';

    public $sql_version;

    public $db_charset;

    public $db_collation;

    public $db_time_zone;

    public $error = array();

    public $time = 0;

    public $query_strs = array();

    private $persistency = false;

    private $new_link = false;

    private $db_connect_id = false;

    private $create_db = false;

    private $query_result = false;

    private $row = array();

    private $rowset = array();

    /**
     * sql::__construct()
     * 
     * @param mixed $db_config
     * @return
     */
    public function __construct ( $config = array() )
    {
        $stime = array_sum( explode( " ", microtime() ) );
        
        if ( isset( $config['host'] ) and ! empty( $config['dbhost'] ) ) $this->server = $config['host'];
        if ( isset( $config['dbport'] ) and ! empty( $config['dbport'] ) ) $this->server .= ':' . $config['dbport'];
        if ( isset( $config['dbname'] ) ) $this->dbname = $config['dbname'];
        if ( isset( $config['user'] ) ) $this->user = $config['user'];
        if ( isset( $config['new_link'] ) ) $this->new_link = ( bool )$config['new_link'];
        if ( isset( $config['create_db'] ) ) $this->create_db = ( bool )$config['create_db'];
        if ( isset( $config['persistency'] ) ) $this->persistency = ( bool )$config['persistency'];
        
        $this->sql_connect( $config['pass'] );
        
        if ( $this->db_connect_id ) $this->sql_setdb();
        
        $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
    }

    /**
     * sql::sql_connect()
     * 
     * @return
     */
    private function sql_connect ( $dbpass )
    {
        $function = ( $this->persistency ) ? 'mysql_pconnect' : 'mysql_connect';
        
        $this->db_connect_id = @call_user_func( $function, $this->server, $this->user, $dbpass, $this->new_link );
        unset( $dbpass );
        if ( ! $this->db_connect_id )
        {
            $this->error = $this->sql_error( sql::NOT_CONNECT_TO_MYSQL_SERVER );
        }
        else
        {
            if ( empty( $this->dbname ) )
            {
                $this->error = $this->sql_error( sql::DATABASE_NAME_IS_EMPTY );
                $this->db_connect_id = false;
            }
            else
            {
                $dbselect = @mysql_select_db( $this->dbname, $this->db_connect_id );
                if ( ! $dbselect )
                {
                    if ( $this->create_db )
                    {
                        @mysql_query( "CREATE DATABASE " . $this->dbname . "", $this->db_connect_id );
                        $dbselect = @mysql_select_db( $this->dbname, $this->db_connect_id );
                    }
                    if ( ! $dbselect )
                    {
                        $this->error = $this->sql_error( sql::UNKNOWN_DATABASE );
                        @mysql_close( $this->db_connect_id );
                        $this->db_connect_id = false;
                    }
                }
            }
        }
    }

    /**
     * sql::sql_setdb()
     * 
     * @return
     */
    private function sql_setdb ( )
    {
        if ( $this->db_connect_id )
        {
            preg_match( "/^(\d+)\.(\d+)\.(\d+)/", mysql_get_server_info(), $m );
            $this->sql_version = ( $m[1] . '.' . $m[2] . '.' . $m[3] );
            if ( version_compare( $this->sql_version, '4.1.0', '>=' ) )
            {
                @mysql_query( "SET SESSION `time_zone`='" . NV_SITE_TIMEZONE_NAME . "'", $this->db_connect_id );
                
                $result = @mysql_query( 'SELECT @@session.time_zone AS `time_zone`, @@session.character_set_database AS `character_set_database`, 
                @@session.collation_database AS `collation_database`, @@session.sql_mode AS `sql_mode`', $this->db_connect_id );
                $row = @mysql_fetch_assoc( $result );
                @mysql_free_result( $result );
                
                $this->db_time_zone = $row['time_zone'];
                $this->db_charset = $row['character_set_database'];
                $this->db_collation = $row['collation_database'];
                
                if ( strcasecmp( $this->db_charset, "utf8" ) != 0 or strcasecmp( $this->db_collation, "utf8_general_ci" ) != 0 )
                {
                    @mysql_query( "ALTER DATABASE `" . $this->dbname . "` DEFAULT CHARACTER SET `utf8` COLLATE `utf8_general_ci`", $this->db_connect_id );
                    $result = @mysql_query( 'SELECT @@session.character_set_database AS `character_set_database`, 
                    @@session.collation_database AS `collation_database`', $this->db_connect_id );
                    $row = @mysql_fetch_assoc( $result );
                    @mysql_free_result( $result );
                    
                    $this->db_charset = $row['character_set_database'];
                    $this->db_collation = $row['collation_database'];
                }
                
                @mysql_query( "SET NAMES 'utf8'", $this->db_connect_id );
                
                if ( version_compare( $this->sql_version, '5.0.2', '>=' ) )
                {
                    $modes = ( ! empty( $row['sql_mode'] ) ) ? array_map( 'trim', explode( ',', $row['sql_mode'] ) ) : array();
                    if ( ! in_array( 'TRADITIONAL', $modes ) )
                    {
                        if ( ! in_array( 'STRICT_ALL_TABLES', $modes ) ) $modes[] = 'STRICT_ALL_TABLES';
                        if ( ! in_array( 'STRICT_TRANS_TABLES', $modes ) ) $modes[] = 'STRICT_TRANS_TABLES';
                    }
                    $mode = implode( ',', $modes );
                    @mysql_query( "SET SESSION `sql_mode`='" . $mode . "'", $this->db_connect_id );
                }
            }
        }
    }

    /**
     * sql::sql_close()
     * 
     * @return
     */
    public function sql_close ( )
    {
        if ( $this->db_connect_id )
        {
            if ( is_resource( $this->query_result ) ) @mysql_free_result( $this->query_result );
            if ( ! $this->persistency )
            {
                $result = @mysql_close( $this->db_connect_id );
                if ( ! $result ) $this->error = $this->sql_error();
                $this->db_connect_id = null;
                $this->row = array();
                $this->rowset = array();
                return $result;
            }
        }
        return false;
    }

    /**
     * sql::sql_query()
     * 
     * @param string $query
     * @return
     */
    public function sql_query ( $query = "" )
    {
        $stime = array_sum( explode( " ", microtime() ) );
        $this->query_result = false;
        if ( ! empty( $query ) )
        {
            $query = preg_replace( '/union/', 'UNI0N', $query );
            $this->query_result = @mysql_query( $query, $this->db_connect_id );
            $this->query_strs[] = array( 
                htmlspecialchars( $query ), ( $this->query_result ? true : false ) 
            );
        }
        if ( $this->query_result )
        {
            unset( $this->row[$this->query_result] );
            unset( $this->rowset[$this->query_result] );
            $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
            return $this->query_result;
        }
        else
        {
            $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
            return false;
        }
    }

    /**
     * sql::sql_query_insert_id()
     * 
     * @param string $query
     * @return
     */
    public function sql_query_insert_id ( $query = "" )
    {
        if ( empty( $query ) or ! preg_match( "/^INSERT\s/is", $query ) )
        {
            return false;
        }
        if ( ! $this->sql_query( $query ) )
        {
            return false;
        }
        $result = @mysql_insert_id( $this->db_connect_id );
        return $result;
    }

    /**
     * sql::sql_numrows()
     * 
     * @param integer $query_id
     * @return
     */
    public function sql_numrows ( $query_id = 0 )
    {
        $stime = array_sum( explode( " ", microtime() ) );
        if ( empty( $query_id ) ) $query_id = $this->query_result;
        
        if ( ! empty( $query_id ) )
        {
            $result = @mysql_num_rows( $query_id );
            $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
            return $result;
        }
        $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
        return false;
    }

    /**
     * sql::sql_affectedrows()
     * 
     * @return
     */
    public function sql_affectedrows ( )
    {
        $stime = array_sum( explode( " ", microtime() ) );
        if ( $this->db_connect_id )
        {
            $result = @mysql_affected_rows( $this->db_connect_id );
            $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
            return $result;
        }
        $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
        return false;
    }

    /**
     * sql::sql_numfields()
     * 
     * @param integer $query_id
     * @return
     */
    public function sql_numfields ( $query_id = 0 )
    {
        $stime = array_sum( explode( " ", microtime() ) );
        if ( empty( $query_id ) ) $query_id = $this->query_result;
        
        if ( ! empty( $query_id ) )
        {
            $result = @mysql_num_fields( $query_id );
            $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
            return $result;
        }
        $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
        return false;
    }

    /**
     * sql::sql_fieldname()
     * 
     * @param mixed $offset
     * @param integer $query_id
     * @return
     */
    public function sql_fieldname ( $offset, $query_id = 0 )
    {
        $stime = array_sum( explode( " ", microtime() ) );
        if ( empty( $query_id ) ) $query_id = $this->query_result;
        
        if ( ! empty( $query_id ) )
        {
            $result = @mysql_field_name( $query_id, $offset );
            $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
            return $result;
        }
        $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
        return false;
    }

    /**
     * sql::sql_fieldtype()
     * 
     * @param mixed $offset
     * @param integer $query_id
     * @return
     */
    public function sql_fieldtype ( $offset, $query_id = 0 )
    {
        $stime = array_sum( explode( " ", microtime() ) );
        if ( empty( $query_id ) ) $query_id = $this->query_result;
        
        if ( ! empty( $query_id ) )
        {
            $result = @mysql_field_type( $query_id, $offset );
            $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
            return $result;
        }
        $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
        return false;
    }

    /**
     * sql::sql_fetchrow()
     * 
     * @param integer $query_id
     * @param integer $type
     * @return
     */
    public function sql_fetchrow ( $query_id = 0, $type = 0 )
    {
        $stime = array_sum( explode( " ", microtime() ) );
        if ( empty( $query_id ) ) $query_id = $this->query_result;
        
        if ( ! empty( $query_id ) )
        {
            if ( $type != 1 and $type != 2 ) $type = 0;
            switch ( $type )
            {
                case 1:
                    $this->row['' . $query_id . ''] = @mysql_fetch_array( $query_id, MYSQL_NUM );
                    break;
                
                case 2:
                    $this->row['' . $query_id . ''] = @mysql_fetch_array( $query_id, MYSQL_ASSOC );
                    break;
                
                default:
                    $this->row['' . $query_id . ''] = @mysql_fetch_array( $query_id, MYSQL_BOTH );
            }
            $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
            return $this->row['' . $query_id . ''];
        }
        $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
        return false;
    }

    /**
     * sql::sql_fetchrowset()
     * 
     * @param integer $query_id
     * @return
     */
    public function sql_fetchrowset ( $query_id = 0 )
    {
        $stime = array_sum( explode( " ", microtime() ) );
        if ( empty( $query_id ) ) $query_id = $this->query_result;
        
        if ( ! empty( $query_id ) )
        {
            unset( $this->rowset['' . $query_id . ''] );
            unset( $this->row['' . $query_id . ''] );
            while ( $this->rowset['' . $query_id . ''] = @mysql_fetch_array( $query_id ) )
            {
                $result[] = $this->rowset['' . $query_id . ''];
            }
            $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
            return $result;
        }
        $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
        return false;
    }

    /**
     * sql::sql_fetchfield()
     * 
     * @param mixed $field
     * @param integer $rownum
     * @param integer $query_id
     * @return
     */
    public function sql_fetchfield ( $field, $rownum = -1, $query_id = 0 )
    {
        if ( empty( $query_id ) ) $query_id = $this->query_result;
        
        $result = false;
        if ( ! empty( $query_id ) )
        {
            if ( $rownum > - 1 )
            {
                $result = @mysql_result( $query_id, $rownum, $field );
            }
            else
            {
                if ( empty( $this->row['' . $query_id . ''] ) && empty( $this->rowset['' . $query_id . ''] ) )
                {
                    if ( $this->sql_fetchrow() ) $result = $this->row['' . $query_id . ''][$field];
                }
                else
                {
                    if ( $this->rowset['' . $query_id . ''] )
                    {
                        $result = $this->rowset['' . $query_id . ''][$field];
                    }
                    elseif ( $this->row['' . $query_id . ''] )
                    {
                        $result = $this->row['' . $query_id . ''][$field];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * sql::sql_rowseek()
     * 
     * @param mixed $rownum
     * @param integer $query_id
     * @return
     */
    public function sql_rowseek ( $rownum, $query_id = 0 )
    {
        if ( empty( $query_id ) ) $query_id = $this->query_result;
        
        if ( ! empty( $query_id ) )
        {
            $result = @mysql_data_seek( $query_id, $rownum );
            return $result;
        }
        return false;
    }

    /**
     * sql::sql_fetch_assoc()
     * 
     * @param integer $query_id
     * @return
     */
    public function sql_fetch_assoc ( $query_id = 0 )
    {
        $stime = array_sum( explode( " ", microtime() ) );
        if ( empty( $query_id ) ) $query_id = $this->query_result;
        
        if ( ! empty( $query_id ) )
        {
            $this->row['' . $query_id . ''] = @mysql_fetch_assoc( $query_id );
            $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
            return $this->row['' . $query_id . ''];
        }
        $this->time += ( array_sum( explode( " ", microtime() ) ) - $stime );
        return false;
    }

    /**
     * sql::sql_freeresult()
     * 
     * @param integer $query_id
     * @return
     */
    public function sql_freeresult ( $query_id = 0 )
    {
        if ( empty( $query_id ) ) $query_id = $this->query_result;
        
        if ( is_resource( $query_id ) )
        {
            unset( $this->row['' . $query_id . ''] );
            unset( $this->rowset['' . $query_id . ''] );
            @mysql_free_result( $query_id );
            return true;
        }
        return false;
    }

    /**
     * sql::sql_error()
     * 
     * @param string $message
     * @return
     */
    public function sql_error ( $message = '' )
    {
        if ( ! $this->db_connect_id )
        {
            return array( 
                'message' => @mysql_error(), 'user_message' => $message, 'code' => @mysql_errno() 
            );
        }
        return array( 
            'message' => @mysql_error( $this->db_connect_id ), 'user_message' => $message, 'code' => @mysql_errno( $this->db_connect_id ) 
        );
    }

    /**
     * sql::fixdb()
     * 
     * @param mixed $value
     * @return
     */
    public function fixdb ( $value )
    {
        $value = str_replace( '\'', '&#039;', $value );
        $value = preg_replace( array( 
            "/(se)(lect)/i", "/(uni)(on)/i", "/(con)(cat)/i", "/(c)(har)/i", "/(out)(file)/i", "/(al)(ter)/i", "/(in)(sert)/i", "/(d)(rop)/i", "/(f)(rom)/i", "/(whe)(re)/i", "/(up)(date)/i", "/(de)(lete)/i", "/(cre)(ate)/i" 
        ), "$1-$2", $value );
        return $value;
    }

    /**
     * sql::unfixdb()
     * 
     * @param mixed $value
     * @return
     */
    function unfixdb ( $value )
    {
        $value = preg_replace( array( 
            "/(se)\-(lect)/i", "/(uni)\-(on)/i", "/(con)\-(cat)/i", "/(c)\-(har)/i", "/(out)\-(file)/i", "/(al)\-(ter)/i", "/(in)\-(sert)/i", "/(d)\-(rop)/i", "/(f)\-(rom)/i", "/(whe)\-(re)/i", "/(up)\-(date)/i", "/(de)\-(lete)/i", "/(cre)\-(ate)/i" 
        ), "$1$2", $value );
        return $value;
    }

    /**
     * sql::dbescape()
     * 
     * @param mixed $value
     * @return
     */
    public function dbescape ( $value )
    {
        if ( is_array( $value ) )
        {
            $value = array_map( array( 
                $this, __function__ 
            ), $value );
        }
        else
        {
            if ( ! is_numeric( $value ) || $value{0} == '0' )
            {
                $value = "'" . mysql_real_escape_string( $this->fixdb( $value ) ) . "'";
            }
        }
        
        return $value;
    }

    /**
     * sql::dbescape_string()
     * 
     * @param mixed $value
     * @return
     */
    public function dbescape_string ( $value )
    {
        if ( is_array( $value ) )
        {
            $value = array_map( array( 
                $this, __function__ 
            ), $value );
        }
        else
        {
            $value = "'" . mysql_real_escape_string( $this->fixdb( $value ) ) . "'";
        }
        
        return $value;
    }

    /**
     * sql::nv_dblikeescape()
     * 
     * @param mixed $value
     * @return
     */
    public function dblikeescape ( $value )
    {
        if ( is_array( $value ) )
        {
            $value = array_map( array( 
                $this, __function__ 
            ), $value );
        }
        else
        {
            $value = mysql_real_escape_string( $this->fixdb( $value ) );
            $value = addcslashes( $value, '_%' );
        }
        
        return $value;
    }

    /**
     * sql::constructQuery()
     * 
     * @return
     */
    public function constructQuery ( )
    {
        $numargs = func_num_args();
        if ( empty( $numargs ) ) return false;
        
        $pattern = func_get_arg( 0 );
        if ( empty( $pattern ) ) return false;
        unset( $matches );
        $pattern = preg_replace( "/[\n\r\t]/", " ", $pattern );
        $pattern = preg_replace( "/[ ]+/", " ", $pattern );
        $pattern = preg_replace( array( 
            "/([\S]+)\[/", "/\]([\S]+)/", "/\[[\s]+/", "/[\s]+\]/", "/[\s]*\,[\s]*/" 
        ), array( 
            "\\1 [", "] \\1", "[", "]", ", " 
        ), $pattern );
        
        preg_match_all( "/[\s]*[\"|\']*[\s]*\[([s|d])([a]*)\][\s]*[\"|\']*[\s]*/", $pattern, $matches );
        
        $replacement = func_get_args();
        unset( $replacement[0] );
        
        $count1 = count( $matches[0] );
        $count2 = count( $replacement );
        
        if ( ! empty( $count1 ) )
        {
            if ( $count2 < $count1 ) return false;
            $replacement = array_values( $replacement );
            $pattern = str_replace( "%", "[:25:]", $pattern );
            $pattern = preg_replace( "/[\s]*[\"|\']*[\s]*\[([s|d])([a]*)\][\s]*[\"|\']*[\s]*/", " %s ", $pattern );
            
            $repls = array();
            foreach ( $matches[1] as $key => $datatype )
            {
                $repls[$key] = $replacement[$key];
                if ( $datatype == 's' )
                {
                    if ( isset( $matches[2][$key] ) and $matches[2][$key] == 'a' )
                    {
                        $repls[$key] = ( array )$repls[$key];
                        if ( ! empty( $repls[$key] ) )
                        {
                            $repls[$key] = array_map( array( 
                                $this, 'fixdb' 
                            ), $repls[$key] );
                            $repls[$key] = array_map( 'mysql_real_escape_string', $repls[$key] );
                            $repls[$key] = "'" . implode( "','", $repls[$key] ) . "'";
                        }
                        else
                        {
                            $repls[$key] = "''";
                        }
                    }
                    else
                    {
                        $repls[$key] = "'" . ( ! empty( $repls[$key] ) ? mysql_real_escape_string( $this->fixdb( $repls[$key] ) ) : "" ) . "'";
                    }
                }
                else
                {
                    if ( isset( $matches[2][$key] ) and $matches[2][$key] == 'a' )
                    {
                        $repls[$key] = ( array )$repls[$key];
                        $repls[$key] = ( ! empty( $repls[$key] ) ) ? "'" . implode( "','", array_map( 'intval', $repls[$key] ) ) . "'" : "'0'";
                    }
                    else
                    {
                        $repls[$key] = "'" . intval( $repls[$key] ) . "'";
                    }
                }
            }
            eval( "\$query = sprintf(\$pattern,\"" . implode( "\",\"", $repls ) . "\");" );
            $query = str_replace( "[:25:]", "%", $query );
        }
        else
        {
            $query = $pattern;
        }
        
        return $query;
    }
}

/*
*  Counter page
*/
function counter()
{
	$f = fopen("counter.yplit.txt", "r");
	$count = (int)fread($f, filesize("counter.yplit.txt"));
	$count++;
	$f = fopen("counter.yplit.txt", "w");
	fwrite($f, $count);
	return $count;
}

/*
*  S_hash(string)
*/
function s_hash( $str )
{
	$str = md5(md5(md5( $str ) . 'yplit' ));
	return $str;
}

/*
* show(  )
*/
function show(  )
{
	global $config, $db, $showthread, $page_title, $home_content, $result, $thread_title, $thread_counter, $thread_content, $lang ;
	require_once('skin/' . $config['skin'] . "/header.php");
	require_once('skin/' . $config['skin'] . "/content.php");
	require_once('skin/' . $config['skin'] . "/footer.php");
	
	echo $header;
	echo $content;
	echo $footer;
}

/*
* Short_title( $l = 20 )
*/
function short_title( $str, $l=20 )
{
	$title = substr($str,0,$l);
		if($title<$str)
		{
			$title .= "...";
		}
	return $title;
}
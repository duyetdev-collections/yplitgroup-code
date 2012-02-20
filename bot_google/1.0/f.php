<?php
/* Bot as Google-bot */
Class BOT{
//protected $contents = '';
const _AUTO_CHECK_LINK = true;

public $contents = '';
public $__siteInfo = array(); // url - host

public $link = array();
public $image = array();
public $_world_link = array();
public $_current_link = array();

public $__err = false;

public $badlink = array();
public $badhost = array();

/* Contruct */
public function BOT($url)
{
	//Check url
	if(empty($url)) return false;
	if(!preg_match('/^http|https|ftp:\/\//i', $url)) { $url = 'http://' . $url; }
	$this->__siteInfo['url'] = preg_replace("/(\/)+$/i", '', $url);
	//Host
	$this->__siteInfo['host'] = str_replace(array('http://','ftp://','https://'), '', $url);
	$this->__siteInfo['host'] = preg_replace('/^([A-z0-9.]+)(\/?.*)/i','$1',$this->__siteInfo['host']);
	$this->__siteInfo['host'] = preg_replace('/^([A-z0-9.]+)(\#?.*)/i','$1',$this->__siteInfo['host']);
	$this->__siteInfo['host'] = preg_replace('/^([A-z0-9.]+)(\??.*)/i','$1',$this->__siteInfo['host']);
	$this->__siteInfo['host'] = preg_replace('/^([A-z0-9.]+)(\\?.*)/i','$1',$this->__siteInfo['host']);
	$this->__siteInfo['host'] = preg_replace('/^([A-z0-9.]+)(\&?.*)/i','$1',$this->__siteInfo['host']);
	
	if(!empty($url))
	{
		$this->contents = $this->getContent($url);
		if($this->__err)die("<b>Error link!!!</b><br><i>Fail contents! Return content is empty!</i>");
	}
}

/* BOT::getContent() */
public function getContent($url)
{
	if(function_exists('file_get_contents'))
	{
		$c = @file_get_contents($url);
		if(empty($c)) {$this->__err=true;}
		else return $c;
	}
	else
	{
		// Different choose
		echo "Fail";
		return false;
	}
}

/* BOT::get($type) */
public function get( $type )
{
	$method = "get" . $type; 
	if (method_exists($this, $method))
	{
		if($method == 'getLink' or $method == 'getlink')
		{
			return $this->getLink();
		}
		elseif($method == 'getImage' or $method == 'getimage')
		{
			return $this->getImage();
		}
		else
		{
			// Different method
			return false;
		}
	}
	else
	{
		return false;
	}
}

/* BOT:getLink() */
protected function getLink()
{
	if(!empty($this->contents))
	{
		preg_match_all('/<a([^>]+)>(.*?)<\/a>/i', $this->contents, $links);
	}
	$this->getListLink($links);
	$this->_world_link();
	$this->_current_link();
}

/* BOT:getImage() */
protected function getImage()
{
	if(!empty($this->contents))
	{
		preg_match_all('/<img([^>]+)/?>/i', $this->contents, $images);
	}
	
	return !empty($images[1])?$images[1]:false;
}

/* BOT::getListLink() */
protected function getListLink($links)
{
	if(!is_array($links)) return false;
	$links = $links[1];
	foreach($links as $_links)
	{
		unset($l);
		preg_match('/href=(\'|")(.*?)(\'|")/i', $_links, $l);
		$this->link[] = $l[2];
	}
}

/* BOT::_world_link() */
protected function _world_link()
{
	if($this->link)
	{
		foreach($this->link as $_link)
		{
			if(!preg_match( '/^http:\/\/'. $this->__siteInfo['host'] .'/i',$_link) AND preg_match('/^http:\/\//i',$_link))
			{
				if( !in_array($_link, $this->badlink) )
				{
					$___link = parse_url($_link);
					if(!in_array($___link['host'],$this->badhost))
					{
						$this->_world_link[] = $_link;
					}
				}
			}
		}
	}
}

/* BOT::_current_link() */
protected function _current_link()
{
	if($this->link)
	{
		foreach($this->link as $_link)
		{
			$_link = trim($_link);
			if( !empty( $_link ))
			{
				if( !preg_match('/http:\/\//i',$_link) AND !preg_match('/^javascript:/i',$_link) AND!preg_match('/^#/i',$_link) )
				{
					$this->_current_link[] = $_link;
				}
				
				if(preg_match('/^'.$this->__siteInfo['host'].'/i',$_link))
				{
					$this->_current_link[] = $_link;
				}
			}
		}
		// Check link
		if( defined( '_AUTO_CHECK_LINK' ) )
		{
			$l_link_tmp = $this->_current_link;
			//@unset($this->_current_link);
			$this->_current_link = array();
			
			foreach( $l_link_tmp as $l_check )
			{
				if(__checkLink( $this->__siteInfo['url'] . '/' . $l_check ) )
				{
					$this->_current_link[] = $l_check;
				}
			}
		}
	}
}

/* BOT::__checkLink($url) */
public function __checkLink($url=NULL) 
{ 
	if($url == NULL) return false; 
	$ch = curl_init($url); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	$data = curl_exec($ch); 
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);//lay code tra ve cua http 
	curl_close($ch); 
	if($httpcode>=200 && $httpcode<300)
	{ 
		return true; //Link live
	}
	else 
	{ 
		return false; //Link die
	} 
}

/* BOT::__badlink($link) */
public function __badlink( $link = array() )
{
	if(!is_array($link))
	{
		$link = array($link);
	}
	foreach($link as $bad_link)
	{
		if(!empty($bad_link) and preg_match('/^http:\/\//is',$bad_link))
		{
			$this->badlink[] = $bad_link;
		}
	}
	return true;
}

/* BOT::__badhost($link) */
public function __badhost( $link = array() )
{
	if(!is_array($link))
	{
		$link = array($link);
	}
	foreach($link as $bad_link)
	{
		$bad_link = parse_url($bad_link);
		if(!empty($bad_link['host']))
		{
			$this->badhost[] = $bad_link['host'];
		}
	}
	return true;
}
 
}

?>
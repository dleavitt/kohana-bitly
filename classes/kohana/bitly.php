<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Bitly {
	
	public static $default 		= 'default';
	public static $instances 	= array();
	
	public static $base_url		= 'http://api.bit.ly/v3/';
	
	public static function instance($name = NULL, array $config = NULL)
	{
		if ($name === NULL)
		{
			$name = Bitly::$default;
		}
		
		if ( ! isset(Bitly::$instances[$name]))
		{
			if ($config === NULL)
			{
				$config = Kohana::$config->load('bitly')[$name];
			}
			
			new Bitly($name, $config);
		}
		
		return Bitly::$instances[$name];
	}
	
	protected $_instance;
	protected $_config;
	protected $_cache = array();
	
	public function __construct($name, array $config)
	{ 
		$this->_instance = $name;
		$this->_config = $config;
		Bitly::$instances[$name] = $this;
	}
	
	public function shorten($long_url)
	{
		if (isset($this->cache[$long_url]))
		{
			return $this->cache[$long_url];
		}
		try
		{
			$this->cache[$long_url] = $this->_request('shorten', array('longUrl' => $long_url));
			return $this->cache[$long_url]['url'];
		}
		catch (Exception $e)
		{
			Kohana::$log->add(Log::ERROR, "Bit.ly error: {$e->getMessage()} ($long_url)");
		}
		return $long_url;
	}
	
	protected function _request($method, array $params)
	{
		$params = array_merge(array(
			'login' => $this->_config['login'],
			'apiKey' => $this->_config['api_key'],
		), $params);
		
		$response = json_decode($this->file_get_contents_curl(self::$base_url.$method.'?'.http_build_query($params)));
		
		if (Arr::get($response, 'status_code') == 200)
		{
			return $response['data'];
		}
		else
		{
			throw new Bitly_Exception(Arr::get($response, 'status_txt'));
		}
	}

	function file_get_contents_curl($url) {
	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       

	    $data = array(
	    	'data' => curl_exec($ch),
	    	'status_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
	    	'status_txt' => curl_getinfo($ch, 'HTTP ERROR: ' . CURLINFO_HTTP_CODE)
	    );
	    curl_close($ch);

	    return $data;
	}
	
}

class Kohana_Bitly_Exception extends Exception {
	
}
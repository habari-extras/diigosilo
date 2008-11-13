<?php

/**
 *    Main class to interact with Diigo API.
 *
 *    @category   PHP
 *    @package    diigosilo
 *    @author     Pierre-Yves Gillier <pivwan@gmail.com>
 *    @copyright  2008 Pierre-Yves Gillier
 *    @license    http://www.apache.org/licenses/LICENSE-2.0.txt  Apache Software Licence 2.0
 *    @version    0.1
 *    @link       http://www.pivwan.net/weblog/plugin-diigosilo
 */

class DiigoAPI
{
	private $username;
	private $password;
	
	private $httpClient;
	
	const API_URI = "api2.diigo.com";
	
	public function __construct($username,$password)
	{
		// Get credentials
		$this->username = $username;
		$this->password = $password;
		
		// Init httpClient
		$this->httpClient = new HttpClient(self::API_URI);
		$this->httpClient->setUserAgent("Diigo Silo for Habari/".DIIGO_PLUGIN_VERSION);
		$this->httpClient->setAuthorization($this->username,$this->password);
		
		if(defined('UNITTESTING'))
			$this->httpClient->setDebug(true);
	}
	
	public function getTags()
	{
		
	}
	
	public function getBookmarks()
	{
		$params = array('users' => $this->username );
		if($this->httpClient->get("/bookmarks",$params)==true)
		{
			return $this->parseJSON($this->httpClient->getContent());
		}
		else
		{
			throw new Exception("WGET:"+$this->httpClient->getError());
		}
	}
	
	private function parseJSON($json)
	{
    if (!extension_loaded('json'))
    {
        include_once(dirname(__FILE__).'/JSON.class.php');
        $json = new JSON;
        $objs = $json->unserialize($json);
    }
    else
    {
        $objs = json_decode($json);
    }
    return($objs);    
	}
}
?>

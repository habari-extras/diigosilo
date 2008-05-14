<?php
/**
 *    Provides a silo to access Diigo bookmarks. 
 *
 *    @category   PHP
 *    @package    diigosilo
 *    @author     Pierre-Yves Gillier <pivwan@gmail.com>
 *    @copyright  2008 Pierre-Yves Gillier
 *    @license    http://www.apache.org/licenses/LICENSE-2.0.txt  Apache Software Licence 2.0
 *    @version    0.1
 *    @link       http://www.pivwan.net/weblog/plugin-diigosilo
 */

// Load used classes.
include_once(dirname(__FILE__).'/httpClient.class.php');
include_once(dirname(__FILE__).'/diigo.class.php');


define('DIIGO_PLUGIN_VERSION','0.1');

class DiigoSilo extends Plugin implements MediaSilo
{
	const SILO_NAME = 'Diigo';

	static $cache = array();

	/**
	* Provide plugin info to the system
	*/
	public function info()
	{
		return array('name' => 'Diigo Media Silo',
			'version' => DIIGO_PLUGIN_VERSION,
			'url' => 'http://www.pivwan.net/weblog/plugin-diigosilo/',
			'author' => 'Pierre-Yves "pivwan" Gillier',
			'authorurl' => 'http://www.pivwan.net/weblog/',
			'license' => 'Apache Software License 2.0',
			'description' => 'Implements Diigo integration',
			'copyright' => '2008',
			);
	}
	
	public function is_auth()
	{
		return true;
	}
	
	/**
	* Return basic information about this silo
	*     name- The name of the silo, used as the root directory for media in this silo
	*/
	public function silo_info()
	{
		if($this->is_auth()) {
			return array('name' => self::SILO_NAME);
		}
		else {
			return array();
		}
	}
	
	public function silo_dir($path)
	{
		$section = strtok($path, '/');
		$results = array();
		
		$diigo = new DiigoAPI(Options::get('diigosilo:username_' . User::identify()->id),Options::get('diigosilo:password_' . User::identify()->id));
		
		switch($section)
		{
			case 'bookmarks':
												$bookmarks = $diigo->getBookmarks();
												foreach($bookmarks as $bookmark)
												{
													$results[] = new MediaAsset(
																			self::SILO_NAME.'/bookmarks/'.base64_encode($bookmark->url),
																			false,
																			array(
																						'title' => $bookmark->title,'filetype'=>'diigo',
																						'link_url' => $bookmark->url,
																						)
																			);
												}
												break;
			case 'tags':
										//$tags = $diigo->getTags();
										$results[] = new MediaAsset(
												self::SILO_NAME . '/tags/' . "demo",
												true,
												array('title'=>'Tag de d&eacute;mo')
											);
										break;
			case '': 
								$results[] = new MediaAsset(
									self::SILO_NAME . '/bookmarks',
									true,
									array('title' => _t('Bookmarks'))
								);
								$results[] = new MediaAsset(
									self::SILO_NAME . '/tags',
									true,
									array('title' => _t('Tags'))
								);
								$results[] = new MediaAsset(
									self::SILO_NAME . '/lists',
									true,
									array('title' => _t('Lists'))
								);
								break;
		}
		
		return $results;
	}
	
	public function silo_get( $path, $qualities = null ) 
	{
	}
	
	public function silo_put( $path, $filedata )
	{
	}
	
	public function silo_delete( $path )
	{
	}
	
	public function silo_highlights() {}
	
	public function silo_permissions( $path ) {}
	
	
	// PLUGIN FUNCTIONS
	
 /**
	* Add actions to the plugin page for this plugin
	* The authorization should probably be done per-user.
	*
	* @param array $actions An array of actions that apply to this plugin
	* @param string $plugin_id The string id of a plugin, generated by the system
	* @return array The array of actions to attach to the specified $plugin_id
	*/
	public function filter_plugin_config($actions, $plugin_id)
	{
		if ($plugin_id == $this->plugin_id()) 
		{
			$actions[] = _t('Configure');
		}
		return $actions;
	}
	
	public function action_plugin_ui($plugin_id, $action)
	{
		if ($plugin_id == $this->plugin_id())
		{
			switch ($action)
			{
				case _t('Configure'):
					$ui = new FormUI(strtolower(get_class($this)));
					$username = $ui->add('text', 'username_'.User::identify()->id, _t('Username:'));
					$password = $ui->add('password', 'password_'.User::identify()->id, _t('Password:'));
					$ui->on_success(array($this, 'updated_config'));
					$ui->out();
				break;
			}
		}
	}
	public function updated_config($ui) 
	{
		return true;
	}
	
	public function action_admin_footer( $theme ) {
  if ($theme->admin_page == 'publish') {
    echo <<< DIIGO
    <script type="text/javascript">
      habari.media.output.diigo = function(fileindex, fileobj) {
        habari.editor.insertSelection('<a href="' + fileobj.link_url + '">' + fileobj.title + '</a>');
      }
      habari.media.preview.diigo = function(fileindex, fileobj) {
        var stats = '';
        return '<div class="mediatitle">' + fileobj.title + '</div><div class="mediastats"><a href="' + fileobj.link_url + '">Link</a></div>';
      }
    </script>
DIIGO;
  }
}
}
?>

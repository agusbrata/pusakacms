<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

use Symfony\Component\Yaml\Yaml;

/* load the MX_Controller class */
require APPPATH."third_party/MX/Controller.php";

class MY_Controller extends MX_Controller{

		var $data = array();

	public function __construct()
	{
		parent::__construct();

		$this->output->enable_profiler(false);

		// Set timezone
		date_default_timezone_set('Asia/Jakarta');

		// load library
		$this->load->library('users/ion_auth');

		// check if main config file exist
		if(!file_exists((SITE_PATH.'db/settings_site.yml'))){
			show_error('site.yml config file for your site is not found. Please create it first.');
		}
		if(!file_exists((SITE_PATH.'db/settings_system.yml'))){
			show_error('system.yml config file for your site is not found. Please create it first.');
		}

		// get all config file
		$config_file = array_filter(get_filenames(SITE_PATH.'db/'), function($file){
			return (strpos($file, 'settings_') !== FALSE);
		});

		foreach ($config_file as $confile) {
			$config = Yaml::parse(file_get_contents(SITE_PATH.'db/'.$confile));
			foreach ($config as $key => $value) {
				$this->config->set_item($key, $value);
				$this->data[$key] = $value;
			}
		}

		$this->config->set_item('page_title', $this->config->item('site_name'));

		if(! defined('PAGE_FOLDER')) define('PAGE_FOLDER', SITE_PATH.$this->config->item('page_folder'));
		if(! defined('POST_FOLDER')) define('POST_FOLDER', SITE_PATH.$this->config->item('post_folder'));
		if(! defined('LABEL_FOLDER')) define('LABEL_FOLDER', SITE_PATH.$this->config->item('label_folder'));
		if(! defined('NAV_FOLDER')) define('NAV_FOLDER', SITE_PATH.$this->config->item('nav_folder'));
		if(! defined('WIDGET_FOLDER')) define('WIDGET_FOLDER', SITE_PATH.$this->config->item('widget_folder'));

		if(! defined('POST_TERM')) define('POST_TERM', $this->config->item('post_term'));

		if(! defined('PLUGIN_FOLDER')) define('PLUGIN_FOLDER', APPPATH.'plugins/');

		// support compatibility with php < 5.3
		if(! defined('JSON_PRETTY_PRINT')) define('JSON_PRETTY_PRINT', 128);
	}

	function logged_in(){
		if($username = $this->session->userdata(SITE_SLUG.'_username')) {
			return true;
		}
		return false;
	}

	function call_event()
	{
		$args = func_get_args();
		$event_location = APPPATH.'events/';
		$event_class = array_shift($args).'_event';
		$event_method = array_shift($args);

		if(file_exists($event_location.$event_class.'.php')){
			include_once($event_location.$event_class.'.php');
			$obj = new $event_class;

			if(method_exists($obj, $event_method)) {
				return call_user_func_array(array($obj, $event_method), $args);
			}
		}
	}

}

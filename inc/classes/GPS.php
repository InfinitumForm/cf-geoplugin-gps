<?php
/**
 * Initialize settings
 *
 * @version       8.0.0
 *
 */
 
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_GPS')) : class CFGP_GPS extends CFGP_Global {
	
	// New API objects
	private $new_api_objects = array('street', 'street_number', 'city_code');
	
	private function __construct(){
		// Stop script when all data is on the place
		if( isset($_GET['gps']) && $_GET['gps'] == 1 ) {
			CFGP_U::setcookie('cfgp_gps', 1, (MINUTE_IN_SECONDS * CFGP_GPS_SESSION));
			$this->add_action('wp_enqueue_scripts', 'deregister_scripts', 99);
		}
		// Stop script when cookie is setup
		if( isset($_COOKIE['cfgp_gps']) && $_COOKIE['cfgp_gps'] == 1 ) {
			$this->add_action('wp_enqueue_scripts', 'deregister_scripts', 99);
		} else {
			// Do AJAX
			$this->add_action('wp_ajax_cf_geoplugin_gps_set', 'ajax_set');
			$this->add_action('wp_ajax_nopriv_cf_geoplugin_gps_set', 'ajax_set');
		}
		// Add new API objects
		$this->add_action('cfgp/api/return', 'add_new_api_objects', 10, 1);
		$this->add_action('cfgp/api/render/response', 'add_new_api_objects', 10, 1);
		$this->add_action('cfgp/api/results', 'add_new_api_objects', 10, 1);
		$this->add_action('cfgp/api/default/fields', 'add_new_api_objects', 10, 1);
		// Redirection control
		$this->add_action('template_redirect', 'template_redirect', 999, 0);
		// Clear some cache on the plugin save
		$this->add_action('cfgp/options/action/set', 'clear_cache_on_options_save', 10, 5);
		// Add debug tab to debug page
		if( defined('CFGP_GPS_DEBUG') && CFGP_GPS_DEBUG ) {
			$this->add_action('cfgp/debug/nav-tab/after', 'debug_page_nav_tab');
			$this->add_action('cfgp/debug/tab-panel/after', 'debug_page_tab_panel');
		}
	}
	
	/**
	 * Clear some cache on the plugin save
	 */
	public function clear_cache_on_options_save($options, $default_options, $name_or_array, $value, $clear_cache) {
		if($clear_cache) {
			CFGP_U::setcookie('cfgp_gps', 0, ((YEAR_IN_SECONDS*2)-CFGP_TIME));
		}
	}
	
	/**
	 * Redirection control
	 */
	public function template_redirect(){
		if( isset($_GET['gps']) && $_GET['gps'] == 1 ) {
			wp_safe_redirect( remove_query_arg('gps') ); exit;
		}
	}
	
	/**
	 * Add new API objects
	 */
	public function add_new_api_objects( $array = array() ) {
		foreach($this->new_api_objects as $object) {
			if( !isset($array[$object]) ) {
				$array[$object] = NULL;
			}
		}
		return $array;
	}
	
	/**
	 * Deregister scripts
	 */
	public function deregister_scripts() {
		wp_deregister_script( CFGP_GPS_NAME . '-gps' );
	}
	
	/**
	 * Add script to footer
	 */
	public function ajax_set() {
		// GPS data missing
		if(!isset($_REQUEST['data'])) {
			wp_send_json_error(array(
				'error'=>true,
				'error_message'=>__('GPS data missing.', CFGP_GPS_NAME)
			)); exit;
		}		
		// Gnerate session slug
		$ip_slug = CFGP_API::cache_key( CFGP_U::api('ip') );
		// Default results
		CFGP_U::api();
		$GEO = array();
		if( $transient = CFGP_DB_Cache::get("cfgp-api-{$ip_slug}") ) {
			$GEO = $transient;
		} else {
			wp_send_json_error(array(
				'error'=>true,
				'error_message'=>__('Could not retrieve geo data.', CFGP_GPS_NAME)
			)); exit;
		}
		// Return new data
		$returns = array('error'=>false);
		// Get new data
		if($_REQUEST['data']) {
			$GEO['gps'] = 1;
			foreach( CFGP_Options::sanitize($_REQUEST['data']) as $key => $value ) {
				
				if( in_array($key, array('address', 'latitude', 'longitude', 'region', 'state', 'street', 'street_number', 'district')) ) {
					$returns[$key]= $GEO[$key] = $value;
				}
				
				if($key === 'countryCode'){
					$returns['country_code']= $GEO['country_code'] = $value;
				} else if($key === 'countryName'){
					$returns['country']= $GEO['country'] = $value;
				} else if($key === 'cityName'){
					$returns['city']= $GEO['city'] = $value;
				} else if($key === 'cityCode'){
					$returns['city_code']= $GEO['city_code'] = $value;
				} else if($key === 'district'){
					$returns['district']= $GEO['district'] = $value;
				}
			}
		}
		// Debug
		if( defined('CFGP_GPS_DEBUG') && CFGP_GPS_DEBUG ) {
			$debug = get_option(CFGP_GPS_NAME . '-debug', array());
			if( empty($debug) ) {
				$debug = array();
			}
			$debug[]= CFGP_Options::sanitize($_REQUEST['data']);
			update_option(CFGP_GPS_NAME . '-debug', $debug, false);
		} else if(get_option(CFGP_GPS_NAME . '-debug')) {
			delete_option(CFGP_GPS_NAME . '-debug');
		}
		// Set new data
		if( !empty($returns) ) {
			$GEO = array_merge($GEO, $returns);
			
			CFGP_DB_Cache::set("cfgp-api-{$ip_slug}", $GEO, (MINUTE_IN_SECONDS * CFGP_SESSION));
			
			if( CFGP_U::dev_mode() ) {
				wp_send_json_success(array(
					'returns' => $returns,
					'debug' => array(
						'transient' => "cfgp-api-{$ip_slug}",
						'geo' => (array)$GEO,
						'request_data' => ( $_REQUEST['data'] ?? NULL )
					)
				), 200);
			} else {
				wp_send_json_success(array(
					'returns' => $returns
				), 200);
			}
			exit;
		}
		// Empty
		wp_send_json_error(array(
			'error'=>true,
			'error_message'=>__('No GPS data.', CFGP_GPS_NAME)
		)); exit;
	}
	
	/**
	 * Debug navigaton tab
	 */
	public function debug_page_nav_tab() { ?>
		<a href="javascript:void(0);" class="nav-tab" data-id="#gps-debug"><i class="cfa cfa-map-marker"></i><span class="label"> <?php _e('GPS', CFGP_NAME); ?></span></a>
	<?php }
	
	/**
	 * Debug tab container
	 */
	public function debug_page_tab_panel() { ?>
		<div class="cfgp-tab-panel cfgp-tab-panel-active" id="gps-debug">
			<?php CFGP_U::dump( get_option(CFGP_GPS_NAME . '-debug') ); ?>
		</div>
	<?php }
	
	/* 
	 * Instance
	 * @verson    8.0.0
	 */
	public static function instance() {
		$class = self::class;
		$instance = CFGP_Cache::get($class);
		if ( !$instance ) {
			$instance = CFGP_Cache::set($class, new self());
		}
		return $instance;
	}	
} endif;
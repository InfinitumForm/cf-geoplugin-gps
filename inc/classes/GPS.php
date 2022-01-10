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
	
	private function __construct(){
		$this->add_action('wp_ajax_cf_geoplugin_gps_set', 'ajax_set');
		$this->add_action('wp_ajax_nopriv_cf_geoplugin_gps_set', 'ajax_set');
		
		if( isset($_GET['gps']) && $_GET['gps'] == 1 ) {
			CFGP_U::setcookie('cfgp_gps', 1, (MINUTE_IN_SECONDS * CFGP_SESSION));
			$this->add_action('wp_enqueue_scripts', 'deregister_scripts', 99);
		}
		
		if( isset($_COOKIE['cfgp_gps']) && $_COOKIE['cfgp_gps'] == 1 ) {
			$this->add_action('wp_enqueue_scripts', 'deregister_scripts', 99);
		}
		
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
		check_ajax_referer( 'cf-geoplugin-gps-set', '_ajax_nonce' );
		
		if( wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'cf-geoplugin-gps-set' ) ) {
			echo -1; exit;
		}
		
		if(!isset($_REQUEST['data'])) {
			echo -2; exit;
		}
		
		// Gnerate session slug
		$ip_slug = str_replace('.', '_', CFGP_U::api('ip') );
		
		// Default results
		$GEO = $DNS = array();
		if( $transient = get_transient("cfgp-api-{$ip_slug}") ) {
			$GEO = $transient['geo'];
			$DNS = $transient['dns'];
		} else {
			echo -3; exit;
		}
		
		// Return new data
		$returns = array();
		
		// Get new data
		if($_REQUEST['data']) {
			$GEO['gps'] = 1;
			foreach( $_REQUEST['data'] as $key => $value ) {
				if(!empty($value) && isset($returns[$key])) {
					$returns[$key] = $this->sanitize($value);
				}
			}
		}
		
		if( !empty($returns) ) {
			
			$GEO = array_merge($GEO, $returns);
			
			set_transient("cfgp-api-{$ip_slug}", array(
				'geo' => (array)$GEO,
				'dns' => (array)$DNS
			), (MINUTE_IN_SECONDS * CFGP_SESSION));
			
			header('Content-Type: application/json');
			echo json_encode($returns); exit;
		} else {
			echo 0; exit;
		}
	}
	
	/**
	 * Sanitize string or array (FUTURE REMOVED)
	 *
	 * This functionality do automatization for the certain type of data expected in this plugin
	 */
	private function sanitize( $str ){
		if( is_array($str) )
		{
			$data = array();
			foreach($str as $key => $obj)
			{
				$data[$key]=$this->sanitize( $obj ); 
			}
			return $data;
		}
		else
		{
			$str = trim( $str );
			
			if(empty($str) && $str != 0)
				return NULL;
			else if(is_numeric($str))
			{
				if(intval( $str ) == $str)
					$str = intval( $str );
				else if(floatval($str) == $str)
					$str = floatval( $str );
				else
					$str = sanitize_text_field( $str );
			}
			else if(!is_bool($str) && in_array(strtolower($str), array('true','false'), true))
			{
				$str = ( strtolower($str) == 'true' );
			}
			else
			{
				$str = sanitize_text_field( $str );
			}
			
			return $str;
		}
	}
	
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
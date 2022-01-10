<?php
/**
 * Initialize settings
 *
 * @version       8.0.0
 *
 */
 
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_GPS_Init')) : final class CFGP_GPS_Init{
	
	private function __construct(){
		// Do translations
		add_action('plugins_loaded', array(&$this, 'textdomain'));
		
		// Call main classes
		$classes = apply_filters('cfgp_gps/init/classes', array(
			
		));
		
		$classes = apply_filters('cfgp_gps/init/included/classes', $classes);
		
		foreach($classes as $class){
			if( method_exists($class, 'instance') ){
				$class::instance();
			}
		}
		
		// Dynamic action
		do_action('cfgp_gps/init', $this);
	}
	
	/**
	 * Run dry plugin dependencies
	 * @since     8.0.0
	 */
	public static function dependencies(){
		// Enqueue Scripts
		add_action( 'wp_enqueue_scripts', array('CFGP_GPS_Init', 'wp_enqueue_scripts') );
		
		// Include file classes
		$includes = apply_filters('cfgp_gps/init/include_classes', array(
			CFGP_CLASS . '/Cache.php',					// Memory control class
			CFGP_CLASS . '/OS.php',						// Operating System info and tool class
			CFGP_CLASS . '/Defaults.php',				// Default values, data
			CFGP_CLASS . '/Utilities.php',				// Utilities
			CFGP_CLASS . '/Library.php',				// Library, data
			CFGP_CLASS . '/Form.php',					// Form class
			CFGP_CLASS . '/Options.php',				// Plugin option class
			CFGP_CLASS . '/Global.php',					// Global class
			CFGP_CLASS . '/IP.php',						// IP class
			CFGP_CLASS . '/API.php',					// API class
		));
		foreach($includes as $include){
			if( file_exists($include) ) {
				include_once $include;
			}
		}
		// Dynamic action
		do_action('cfgp_gps/init/dependencies');
	}
	
	/**
	 * Run plugin actions and filters
	 * @since     8.0.0
	 */
	public static function run() {
		$instance = self::instance();
		// Dynamic run
		do_action('cfgp_gps/init/run');
	}
	
	/**
	 * Load translations
	 * @since     8.0.0
	 */
	public function textdomain() {
		if ( is_textdomain_loaded( CFGP_GPS_NAME ) ) {
			unload_textdomain( CFGP_GPS_NAME );
		}
		
		// Get locale
		$locale = apply_filters( 'cfgp_plugin_locale', get_locale(), CFGP_GPS_NAME );
		
		// We need standard file
		$mofile = sprintf( '%s-%s.mo', CFGP_GPS_NAME, $locale );
		
		// Check first inside `/wp-content/languages/plugins`
		$domain_path = path_join( WP_LANG_DIR, 'plugins' );
		$loaded = load_textdomain( CFGP_GPS_NAME, path_join( $domain_path, $mofile ) );
		
		// Or inside `/wp-content/languages`
		if ( ! $loaded ) {
			$loaded = load_textdomain( CFGP_GPS_NAME, path_join( WP_LANG_DIR, $mofile ) );
		}
		
		// Or inside `/wp-content/plugin/cf-geoplugin/languages`
		if ( ! $loaded ) {
			$domain_path = CFGP_ROOT . '/languages';
			$loaded = load_textdomain( CFGP_GPS_NAME, path_join( $domain_path, $mofile ) );
			
			// Or load with only locale without prefix
			if ( ! $loaded ) {
				$loaded = load_textdomain( CFGP_GPS_NAME, path_join( $domain_path, "{$locale}.mo" ) );
			}

			// Or old fashion way
			if ( ! $loaded && function_exists('load_plugin_textdomain') ) {
				load_plugin_textdomain( CFGP_GPS_NAME, false, $domain_path );
			}
		}
		
		
	}
	
	
	/**
	 * Enqueue Scripts
	 * @since     8.0.0
	 */
	public static function wp_enqueue_scripts() {
		wp_register_script( CFGP_GPS_NAME . '-gps', CFGP_GPS_JS . '/cfgp-gps.js', array( 'jquery' ), CFGP_GPS_VERSION, true );
		wp_enqueue_script( CFGP_GPS_NAME . '-gps' );
		wp_localize_script(
			CFGP_GPS_NAME . '-gps',
			'CFGEO_GPS',
			array(
				'ajax_url'		=> admin_url( 'admin-ajax.php' ),
				'key'			=> CFGP_Options::get('map_api_key'),
				'language'		=> get_bloginfo('language'),
				'nonce'			=> wp_create_nonce( 'cf-geoplugin-gps-set' ),
				'label'			=> array(
					'ZERO_RESULTS'			=> __('There is no results for this search.',CFGP_GPS_NAME),
					'OVER_DAILY_LIMIT'		=> __('Your daily limit is reached. Check your billing settings.',CFGP_GPS_NAME),
					'OVER_QUERY_LIMIT'		=> __('Your account quota is reached.',CFGP_GPS_NAME),
					'REQUEST_DENIED'		=> __('Your request is denied.',CFGP_GPS_NAME),
					'INVALID_REQUEST'		=> __('Your send bad or broken request to you API call.',CFGP_GPS_NAME),
					'DATA_UNKNOWN_ERROR'	=> __('Request could not be processed due to a server error. The request may succeed if you try again.',CFGP_GPS_NAME),
					'PERMISSION_DENIED'		=> __('User denied the request for Geolocation.',CFGP_GPS_NAME),
					'POSITION_UNAVAILABLE'	=> __('Location information is unavailable.',CFGP_GPS_NAME),
					'TIMEOUT'				=> __('The request to get user location timed out.',CFGP_GPS_NAME),
					'UNKNOWN_ERROR'			=> __('An unknown error occurred.',CFGP_GPS_NAME),
					'not_supported'			=> __('Geolocation is not supported by this browser.',CFGP_GPS_NAME),
					'google_geocode'		=> __('Google Geocode: %s',CFGP_GPS_NAME),
				)
			)
		);
	}
	
	/**
	 * Run script on the plugin activation
	 * @since     8.0.0
	 */
	public static function activation() {
		return CFGP_Global::register_activation_hook(function(){
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			
			// Get global variables
			global $wpdb;
			
			// clear old cache
			CFGP_U::flush_plugin_cache();
			
			// Include important library
			if(!function_exists('dbDelta')){
				require_once ABSPATH . '/wp-admin/includes/upgrade.php';
			}
			
			// Add activation date
			if($activation = get_option(CFGP_GPS_NAME . '-activation')) {
				$activation[] = date('Y-m-d H:i:s');
				update_option(CFGP_GPS_NAME . '-activation', $activation, false);
			} else {
				add_option(CFGP_GPS_NAME . '-activation', array(date('Y-m-d H:i:s')), false);
			}

			// Generate unique ID
			if(!get_option(CFGP_GPS_NAME . '-ID')) {
				add_option(CFGP_GPS_NAME . '-ID', 'cfgp_'.CFGP_U::generate_token(55).'_'.CFGP_U::generate_token(4), false);
			}
		});
	}
	
	/**
	 * Run script on the plugin deactivation
	 * @since     8.0.0
	 */
	public static function deactivation() {
		return CFGP_Global::register_deactivation_hook(function(){
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			
			// Add deactivation date
			if($deactivation = get_option(CFGP_GPS_NAME . '-deactivation')) {
				$deactivation[] = date('Y-m-d H:i:s');
				update_option(CFGP_GPS_NAME . '-deactivation', $deactivation, false);
			} else {
				add_option(CFGP_GPS_NAME . '-deactivation', array(date('Y-m-d H:i:s')), false);
			}
		});
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
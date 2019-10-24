<?php
/**
 * @link              http://cfgeoplugin.com/
 * @since             1.0.0
 * @package           CF_Geoplugin_GPS
 *
 * @wordpress-plugin
 * Plugin Name:       GPS for CF Geo Plugin
 * Plugin URI:        http://cfgeoplugin.com/
 * Description:       WordPress GPS module for the CF Geo Plugin.
 * Version:           1.0.4
 * Author:            INFINITUM FORM
 * Author URI:        https://infinitumform.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cf-geoplugin-gps
 * Domain Path:       /languages
 * Network:           true
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
 
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }
// Find is localhost or not
if ( ! defined( 'CFGP_LOCAL' ) ) {
	if(isset($_SERVER['REMOTE_ADDR'])) {
		define('CFGP_LOCAL', in_array($_SERVER['REMOTE_ADDR'], array(
			'127.0.0.1',
			'::1'
		)));
	} else {
		define('CFGP_LOCAL', false);
	}
}

/**
 * DEBUG MODE
 *
 * This is need for plugin debugging.
 */
if ( defined( 'WP_DEBUG' ) ){
	if(WP_DEBUG === true || WP_DEBUG === 1)
	{
		if ( ! defined( 'WP_CF_GEO_DEBUG' ) ) define( 'WP_CF_GEO_DEBUG', true );
	}
}
if ( defined( 'WP_CF_GEO_DEBUG' ) ){
	if(WP_CF_GEO_DEBUG === true || WP_CF_GEO_DEBUG === 1)
	{
		error_reporting( E_ALL );
		if(function_exists('ini_set'))
		{
			ini_set('display_startup_errors',1);
			ini_set('display_errors',1);
		}
	}
}

$cfgp_version = NULL;
if(file_exists(dirname(__DIR__) . '/cf-geoplugin/cf-geoplugin.php'))
{
	// Main plugin file
	if ( ! defined( 'CFGP_FILE' ) )			define( 'CFGP_FILE', dirname(__DIR__) . '/cf-geoplugin/cf-geoplugin.php' );
	// Main Plugin root
	if ( ! defined( 'CFGP_ROOT' ) )			define( 'CFGP_ROOT', rtrim(plugin_dir_path(CFGP_FILE), '/') );
	// Current plugin version ( if change, clear also session cache )
	if(function_exists('get_file_data') && $plugin_data = get_file_data( CFGP_FILE, array('Version' => 'Version'), false ))
		$cfgp_version = $plugin_data['Version'];
	else if(preg_match('/\*[\s\t]+?version:[\s\t]+?([0-9.]+)/i',file_get_contents( CFGP_FILE ), $v))
		$cfgp_version = $v[1];
} else {
	// Main plugin file
	if ( ! defined( 'CFGP_FILE' ) )		define( 'CFGP_FILE', ABSPATH . '/wp-content/plugins/cf-geoplugin/cf-geoplugin.php' );
	// Main Plugin root
	if ( ! defined( 'CFGP_ROOT' ) )		define( 'CFGP_ROOT', ABSPATH . '/wp-content/plugins/cf-geoplugin' );
}
if ( ! defined( 'CFGP_VERSION' ) )		define( 'CFGP_VERSION', $cfgp_version);
// Main website
if ( ! defined( 'CFGP_STORE' ) )		define( 'CFGP_STORE', 'https://cfgeoplugin.com');

// Includes directory
if ( ! defined( 'CFGP_INCLUDES' ) )		define( 'CFGP_INCLUDES', CFGP_ROOT . '/includes' );
// Main plugin name
if ( ! defined( 'CFGP_NAME' ) )			define( 'CFGP_NAME', 'cf-geoplugin');
// Plugin session prefix (controlled by version)
if ( ! defined( 'CFGP_PREFIX' ) )		define( 'CFGP_PREFIX', 'cf_geo_'.preg_replace("/[^0-9]/Ui",'',CFGP_VERSION).'_');

// Plugin file
if ( ! defined( 'CFGP_GPS_FILE' ) )		define( 'CFGP_GPS_FILE', __FILE__ );
// Plugin root
if ( ! defined( 'CFGP_GPS_ROOT' ) )		define( 'CFGP_GPS_ROOT', rtrim(plugin_dir_path(CFGP_GPS_FILE), '/') );
// Plugin URL root
if ( ! defined( 'CFGP_GPS_URL' ) )		define( 'CFGP_GPS_URL', rtrim(plugin_dir_url( CFGP_GPS_FILE ), '/') );
// Plugin URL root
if ( ! defined( 'CFGP_GPS_ASSETS' ) )	define( 'CFGP_GPS_ASSETS', CFGP_GPS_URL . '/assets' );
// Timestamp
if( ! defined( 'CFGP_GPS_TIME' ) )		define( 'CFGP_GPS_TIME', time() );
// Session
if( ! defined( 'CFGP_GPS_SESSION' ) )	define( 'CFGP_GPS_SESSION', 5 );
// Plugin name
if ( ! defined( 'CFGP_GPS_NAME' ) )		define( 'CFGP_GPS_NAME', 'cf-geoplugin-gps');
$cfgp_gps_version = NULL;
if(function_exists('get_file_data') && $plugin_data = get_file_data( CFGP_GPS_FILE, array('Version' => 'Version'), false ))
	$cfgp_gps_version = $plugin_data['Version'];
else if(preg_match('/\*[\s\t]+?version:[\s\t]+?([0-9.]+)/i',file_get_contents( CFGP_GPS_FILE ), $v))
	$cfgp_gps_version = $v[1];
if ( ! defined( 'CFGP_GPS_VERSION' ) )	define( 'CFGP_GPS_VERSION', $cfgp_gps_version);

/* 
 * Construct functons and shortcodes for the displaying user informations
*/
if( !class_exists('CF_Geoplugin_GPS')) :

// Require geoplugin global
if(file_exists(CFGP_INCLUDES . '/class-cf-geoplugin-global.php')) {
	require CFGP_INCLUDES . '/class-cf-geoplugin-global.php';
} else {
	class CF_Geoplugin_Global {}
}

class CF_Geoplugin_GPS extends CF_Geoplugin_Global
{
	// Current version
	private $version = 0;
	// Current main version
	private $main_version = 0;
	// Options
	private $option = array();
	
	function __construct(){

		// Prevent errors and plugin load
		if(!method_exists('CF_Geoplugin_Global', 'get_instance')) return;
		if(!$this->check_activation()) return;
		
		// Get session
		$session = array();
		if( isset($_SESSION[ CFGP_PREFIX . 'api_session' ]) ) $session = $_SESSION[ CFGP_PREFIX . 'api_session' ];
		
		// Construct global properties
		$this->version = self::version();
		$this->version = self::main_version();
		$this->option = parent::get_option();
		
		// Add translation support
		$this->add_action('plugins_loaded', 'load_textdomain');
		
		// Add script to footer
		if(!isset($session['gps']) || (isset($session['gps']) && !$session['gps'])){
			$this->add_action( 'wp_enqueue_scripts', 'register_scripts' );
		}
		
		// Add shortcodes to CF Geo Plugin table
		$this->add_action('page-cf-geoplugin-shortcode-table-address', 'shortcode_table');
		$this->add_action('page-cf-geoplugin-beta-shortcode-table-address', 'beta_shortcode_table');
		
		// Load ajax for GPS data setup
		$this->add_action('wp_ajax_cf_geoplugin_gps_set', 'ajax_set');
		$this->add_action('wp_ajax_nopriv_cf_geoplugin_gps_set', 'ajax_set');
		$this->add_filter('cf_geeoplugin_api_get_geodata', 'api_get_geodata', 1, 1);
		$this->add_filter('cf_geeoplugin_api_render_response', 'api_render_response', 1, 1);
	}
	
	/**
	 * Add new fields to CF Geo Plugin shortcode
	 */
	function api_render_response($response){
		if(!isset($_SESSION[ CFGP_PREFIX . 'api_session' ])) return $response;
		
		$render = array();
		$session = $_SESSION[ CFGP_PREFIX . 'api_session' ];
		
		if(isset($session['street'])) $render['street'] = $session['street'];
		if(isset($session['street_number'])) $render['street_number'] = $session['street_number'];
		
		return $this->array_splice_after_key($response, 'city', $render);
	}
	
	/**
	 * Set new session to geodata API
	 */
	function api_get_geodata($response){
		if(isset($_SESSION[ CFGP_PREFIX . 'api_session' ]) && $_SESSION[ CFGP_PREFIX . 'api_session' ]['gps'])
		{
			$response = array_merge($response, $_SESSION[ CFGP_PREFIX . 'api_session' ]);
		}
		return $response;
	}
	
	/**
	 * Add beta shortcodes to CF Geo Plugin table
	 */
	function beta_shortcode_table( $str ){
		if(!isset($_SESSION[ CFGP_PREFIX . 'api_session' ])) return;
		
		$session = $_SESSION[ CFGP_PREFIX . 'api_session' ];
	?>
	<?php if(isset($session['street'])) : ?>
		<tr>
			<td><kbd>[cfgeo_street]</kbd> <i class="badge">(GPS)</i></td>
			<td><?php echo $session['street']; ?></td>
		</tr>
	<?php endif; ?>
	<?php if(isset($session['street_number'])) : ?>
		<tr>
			<td><kbd>[cfgeo_street_number]</kbd> <i class="badge">(GPS)</i></td>
			<td><?php echo $session['street_number']; ?></td>
		</tr>
	<?php endif; ?>
	<?php }
	
	/**
	 * Add shortcodes to CF Geo Plugin table
	 */
	function shortcode_table( $str ){
		if(!isset($_SESSION[ CFGP_PREFIX . 'api_session' ])) return;
		
		$session = $_SESSION[ CFGP_PREFIX . 'api_session' ];
	?>
	<?php if(isset($session['street'])) : ?>
		<tr>
			<td><kbd>[cfgeo return="street"]</kbd> <i class="badge">(GPS)</i></td>
			<td><?php echo $session['street']; ?></td>
		</tr>
	<?php endif; ?>
	<?php if(isset($session['street_number'])) : ?>
		<tr>
			<td><kbd>[cfgeo return="street_number"]</kbd> <i class="badge">(GPS)</i></td>
			<td><?php echo $session['street_number']; ?></td>
		</tr>
	<?php endif; ?>
	<?php }
	
	/**
	 * Add script to footer
	 */
	function ajax_set() {
		check_ajax_referer( 'cf-geoplugin-gps-set', '_ajax_nonce' );
		if(!isset($_REQUEST['data'])) wp_die(-1);
		
		$data = method_exists('CF_Geoplugin_Global', 'sanitize') ? parent::sanitize( $_REQUEST['data'] ) : self::____sanitize( $_REQUEST['data'] );
		
		$session_api = CFGP_PREFIX . 'api_session';
		$session = array();
		
		if( isset($_SESSION[ $session_api ]) )
		{
			$session = $_SESSION[ $session_api ];
		}
		
		$gps=array();
		foreach($data as $key => $val)
		{
			if(is_array($val)) continue;
			$gps[ $key ]=$val;
		}
		$gps['gps'] = 1;
		
		if(!empty($session) && !empty($gps))
		{
			$_SESSION[ $session_api ] = array_merge($session, $gps);
			$session_expire = CFGP_PREFIX . 'session_expire';
			$_SESSION[ $session_expire ] = (time() + (60 * 60 * 24));
		}
		wp_die(1);
	}
	
	/**
	 * Sanitize string or array (FUTURE REMOVED)
	 *
	 * This functionality do automatization for the certain type of data expected in this plugin
	 */
	private static function ____sanitize( $str ){
		if( is_array($str) )
		{
			$data = array();
			foreach($str as $key => $obj)
			{
				$data[$key]=self::sanitize( $obj ); 
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
	
	/**
	 * Register scripts
	 */
	function register_scripts() {
		wp_register_script( CFGP_GPS_NAME . '-gps', CFGP_GPS_ASSETS . '/js/wordpress-geoplugin-gps.js', array( 'jquery', CFGP_NAME . '-js-public' ), CFGP_GPS_VERSION, true );
		wp_localize_script(
			CFGP_GPS_NAME . '-gps',
			'CFGEO_GPS',
			array(
				'ajax_url'			=> self_admin_url( 'admin-ajax.php' ),
				'key'			=> $this->option['map_api_key'],
				'language'		=> get_bloginfo('language'),
				'nonce'			=> wp_create_nonce( "cf-geoplugin-gps-set" )
			)
		);
		wp_enqueue_script( CFGP_GPS_NAME . '-gps' );
	}
	
	/* 
	 * Get plugin version
	*/
	public static function version(){
		if(function_exists('get_file_data') && $plugin_data = get_file_data( CFGP_GPS_FILE, array('Version' => 'Version'), false ))
			return $plugin_data['Version'];
		else if(preg_match('/\*[\s\t]+?version:[\s\t]+?([0-9.]+)/i',file_get_contents( CFGP_GPS_FILE ), $v))
			return $v[1];
			
		return 0;
	}
	
	/* 
	 * Get main CF Geoplugin version
	*/
	public static function main_version(){
		if(function_exists('get_file_data') && $plugin_data = get_file_data( CFGP_FILE, array('Version' => 'Version'), false ))
			return $plugin_data['Version'];
		else if(preg_match('/\*[\s\t]+?version:[\s\t]+?([0-9.]+)/i',file_get_contents( CFGP_FILE ), $v))
			return $v[1];
			
		return 0;
	}
	
	/* 
	 * Hook for add_action()
	*/
	public function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1){
		if(!is_array($function_to_add))
			$function_to_add = array(&$this, $function_to_add);
			
		return add_action( (string)$tag, $function_to_add, (int)$priority, (int)$accepted_args );
	}
	
	/* 
	 * Hook for add_filter()
	*/
	public function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1){
		if(!is_array($function_to_add))
			$function_to_add = array(&$this, $function_to_add);
			
		return add_filter( (string)$tag, $function_to_add, (int)$priority, (int)$accepted_args );
	}
	
	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_textdomain() {
		
        $locale = apply_filters( 'plugin_locale', get_locale(), 'cf-geoplugin-gps' );
		$path = rtrim(plugin_dir_path(__FILE__),'/');

        if ( $loaded = load_textdomain( 'cf-geoplugin-gps', $path . '/languages' . '/' . 'cf-geoplugin-gps-' . $locale . '.mo' ) ) {
            return $loaded;
        } else {
            load_plugin_textdomain( 'cf-geoplugin-gps', false, $path . '/languages' );
        }
	}

	/**
	 * Insert element into arrays at specific position
	 */
	private function array_splice_after_key($array, $key, $array_to_insert)
	{
		$key_pos = array_search($key, array_keys($array));
		if($key_pos !== false){
			++$key_pos;
			$second_array = array_splice($array, $key_pos);
			$array = array_merge($array, $array_to_insert, $second_array);
		}
		return $array;
	}
	
	/**
	 * Init admin functions
	 */
	public static function admin_init (){
		// Display error message on the activation fail
		if(get_option('cf-geoplugin-gps-activation-message', false) !== false)
		{
			deactivate_plugins( plugin_basename( CFGP_GPS_FILE ) );
			add_action( 'admin_notices', function(){
				?>
				<div class="notice notice-error is-dismissible">
					<h2><?php _e('Activation Has Failed',CFGP_GPS_NAME); ?></h2>
					<p><?php echo get_option('cf-geoplugin-gps-activation-message', ''); ?></p>
				</div>
				<?php
				delete_option('cf-geoplugin-gps-activation-message');
			} );
			return;
		}
	}
	
	/**
	 * Activation function
	 */
	public static function activation(){
		if( !function_exists('get_plugin_data') ){
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		// dependent plugin
		$parent_plugin = 'cf-geoplugin/cf-geoplugin.php';

		// dependent plugin version
		$version_to_check = '7.7.0'; 

		$category_error = false;

		if(file_exists(WP_PLUGIN_DIR.'/'.$parent_plugin)){
			$parent_plugin_data = get_plugin_data( WP_PLUGIN_DIR.'/'.$parent_plugin);
			$category_error = (!version_compare ( $parent_plugin_data['Version'], $version_to_check, '>=') ? true : false);
		} else {
			update_option('cf-geoplugin-gps-activation-message', sprintf(__('You need first to install %1$s in order to use this %2$s.', CFGP_GPS_NAME), '<a href="https://wordpress.org/plugins/cf-geoplugin/" target="_blank">CF Geo Plugin</a>', '<b>CF Geo Plugin GPS addon</b>'));
			return;
		}  

		if ( $category_error ) {
			update_option('cf-geoplugin-gps-activation-message', sprintf(__('You need first to upgrade your %1$s to version %2$s or above in order to use this %3$s.', CFGP_GPS_NAME), '<b>CF Geo Plugin</b>', "<b>{$version_to_check}</b>", '<b>CF Geo Plugin GPS addon</b>'));
			return;
		}
		
		if(!is_plugin_active($parent_plugin))
		{
			update_option('cf-geoplugin-gps-activation-message', sprintf(__('%1$s need to be activated in order to use this %2$s.', CFGP_GPS_NAME), '<b>CF Geo Plugin</b>', '<b>CF Geo Plugin GPS addon</b>'));
			return;
		}
	}
	
	/**
	 * Deactivation function
	 */
	public static function deactivation(){
		if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
			if(function_exists('session_status') && session_status() == PHP_SESSION_NONE) {
				return;
			}
		}
		else if (version_compare(PHP_VERSION, '5.4.0', '>=') && version_compare(PHP_VERSION, '7.0.0', '<'))
		{
			if (function_exists('session_status') && session_status() == PHP_SESSION_NONE) {
				return;
			}
		}
		else
		{
			if(session_id() == '') {
				return;
			}
		}
		
		if(isset($_SESSION))
		{
			foreach($_SESSION as $key => $val)
			{
				if(strpos($key, CFGP_PREFIX) !== false)
				{
					unset($_SESSION[ $key ]);
				}
			}
			$_SESSION[CFGP_PREFIX . 'session_expire'] = (time() + (60 * CFGP_GPS_SESSION));
		}
	}
}
/**
 * Deactivation hook
 */
register_deactivation_hook( CFGP_GPS_FILE, array('CF_Geoplugin_GPS', 'deactivation'));
/**
 * Activation hook
 */
register_activation_hook( CFGP_GPS_FILE, array('CF_Geoplugin_GPS', 'activation') );
/* 
 * Initialize plugin
*/
add_action('init', function() {
	new CF_Geoplugin_GPS();
}, 2);
/* 
 * Initialize plugin in admin
*/
if(is_admin()){
	add_action('admin_init', array('CF_Geoplugin_GPS', 'admin_init'));
}
endif;
<?php
/**
 * @link              http://cfgeoplugin.com/
 * @since             1.0.0
 * @package           CF_Geoplugin_GPS
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Geo Plugin GPS addon
 * Plugin URI:        http://cfgeoplugin.com/
 * Description:       WordPress GPS module for the CF Geo Plugin.
 * Version:           1.0.0
 * Author:            Ivijan-Stefan Stipic
 * Author URI:        https://linkedin.com/in/ivijanstefanstipic
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

// Main plugin file
if ( ! defined( 'CFGP_FILE' ) )			define( 'CFGP_FILE', dirname(__DIR__) . '/cf-geoplugin/cf-geoplugin.php' );
// Current plugin version ( if change, clear also session cache )
$cfgp_version = NULL;
if(function_exists('get_file_data') && $plugin_data = get_file_data( CFGP_FILE, array('Version' => 'Version'), false ))
	$cfgp_version = $plugin_data['Version'];
else if(preg_match('/\*[\s\t]+?version:[\s\t]+?([0-9.]+)/i',file_get_contents( CFGP_FILE ), $v))
	$cfgp_version = $v[1];
if ( ! defined( 'CFGP_VERSION' ) )		define( 'CFGP_VERSION', $cfgp_version);
// Main website
if ( ! defined( 'CFGP_STORE' ) )		define( 'CFGP_STORE', 'https://cfgeoplugin.com');
// Main Plugin root
if ( ! defined( 'CFGP_ROOT' ) )			define( 'CFGP_ROOT', rtrim(plugin_dir_path(CFGP_FILE), '/') );
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
// Timestamp
if( ! defined( 'CFGP_GPS_TIME' ) )		define( 'CFGP_GPS_TIME', time() );
// Plugin name
if ( ! defined( 'CFGP_NAME' ) )			define( 'CFGP_GPS_NAME', 'cf-geoplugin-gps');

/* 
 * Construct functons and shortcodes for the displaying user informations
*/
if( !class_exists('CF_Geoplugin_GPS') && file_exists(CFGP_INCLUDES . '/class-cf-geoplugin-global.php')) :
require CFGP_INCLUDES . '/class-cf-geoplugin-global.php';

class CF_Geoplugin_GPS extends CF_Geoplugin_Global
{
	// Current version
	private $version = 0;
	// Current main version
	private $main_version = 0;
	// Options
	private $option = array();
	
	function __construct(){
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
		if(!isset($session['gps']) || (isset($session['gps']) && !$session['gps'])) $this->add_action('wp_footer', 'add_script');
		
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
	 * Addnew fields to CF Geo Plugin shortcode
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
		
		$data = $this->sanitize( $_REQUEST['data'] );
		
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
	 * Add script to footer
	 */
	function add_script() { ?>
<script type="text/javascript">
/* <![CDATA[ */
(function($){
	var info = [],
		redirect = function( url ){
			var X = setTimeout(function(){
				window.location.replace(url);
				return true;
			},300);

			if( window.location = url ){
				clearTimeout(X);
				return true;
			} else {
				if( window.location.href = url ){
					clearTimeout(X);
					return true;
				}else{
					clearTimeout(X);
					window.location.replace(url);
					return true;
				}
			}
			return false;
		},
		send_possition = function( position ){
			var latitude = position.coords.latitude,
				longitude = position.coords.longitude;
				
			$.get('https://maps.googleapis.com/maps/api/geocode/json',{
				key : '<?php echo $this->option['map_api_key']; ?>',
				language : '<?php echo get_bloginfo('language'); ?>',
				sensor : 'true',
				latlng : latitude + ',' + longitude
			}).done(function(data){
				if(data.status == 'OK')
				{
					var geo = {}, i, key;
					for(var i in data.results[0].address_components)
					{
						key = data.results[0].address_components[i].types[0];
						geo[key]={
							long_name : data.results[0].address_components[i].long_name,
							short_name : data.results[0].address_components[i].short_name
						}
					}
					
					if(geo.country){
						geo.countryCode = geo.country.short_name;
						geo.countryName = geo.country.long_name;
					}
					
					if(geo.locality){
						geo.cityName = geo.locality.long_name;
						geo.cityCode = geo.locality.short_name;
					}
					
					if(geo.political){
						geo.region = geo.political.long_name;
					}
					
					if(geo.route){
						geo.street = geo.route.long_name;
					}
					
					if(geo.street_number){
						geo.street_number = geo.street_number.long_name;
					}
					
					if(data.results[0].formatted_address) {
						geo.address = data.results[0].formatted_address;
					}
					
					if(data.results[0].geometry && data.results[0].geometry.location)
					{
						geo.latitude = data.results[0].geometry.location.lat;
						geo.longitude = data.results[0].geometry.location.lng;
						geo.place_id = data.results[0].geometry.place_id;
					}
					else
					{
						geo.latitude = latitude;
						geo.longitude = longitude;
					}

					$.post('<?php echo admin_url('admin-ajax.php'); ?>',{
						action : 'cf_geoplugin_gps_set',
						data : geo,
						_ajax_nonce : '<?php echo wp_create_nonce( "cf-geoplugin-gps-set" ); ?>'
					}).done(function(returns){ console.log(returns);
						if(returns == 1){
							location.reload();
						}
					});
				}
				else
				{
					var returns = null;
					switch(data.status)
					{
						case 'ZERO_RESULTS':
							returns = 'There is no results for this search.';
							break;
						case 'OVER_DAILY_LIMIT':
							returns = 'Your daily limit is reached. Check your billing settings';
							break;
						case 'OVER_QUERY_LIMIT':
							returns = 'Your account quota is reached.';
							break;
						case 'REQUEST_DENIED':
							returns = 'Your request is denied.';
							break;
						case 'INVALID_REQUEST':
							returns = 'Your sand bad or broken request to you API call.';
							break;
						case 'UNKNOWN_ERROR':
							returns = 'Request could not be processed due to a server error. The request may succeed if you try again.';
							break;
					}
					if(returns) console.log('Google Geocode: ' + returns);
				}
			});
		},
		display_error = function( error ){
			var returns = null;
			switch(error.code)
			{
				case error.PERMISSION_DENIED:
					returns = "User denied the request for Geolocation."
					break;
				case error.POSITION_UNAVAILABLE:
					returns = "Location information is unavailable."
					break;
				case error.TIMEOUT:
					returns = "The request to get user location timed out."
					break;
				case error.UNKNOWN_ERROR:
					returns = "An unknown error occurred."
					break;
			}
			
			if(returns) console.log('Google Geocode: ' + returns);
		},
		get_location = function(){
			if (navigator.geolocation)
			{
				navigator.geolocation.getCurrentPosition(send_possition, display_error);
			}
			else
			{
				console.log("Geolocation is not supported by this browser.");
			}
		}
	get_location();
}(jQuery || window.jQuery || Zepto || window.Zepto))
/* ]]> */
</script>
	<?php }
	
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
	 * Sanitize string or array
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
			
			if(empty($str))
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
	 * Insert element into arrays at specific position
	 */
	private function array_splice_after_key($array, $key, $array_to_insert)
	{
		$key_pos = array_search($key, array_keys($array));
		if($key_pos !== false){
			$key_pos++;
			$second_array = array_splice($array, $key_pos);
			$array = array_merge($array, $array_to_insert, $second_array);
		}
		return $array;
	}
	
	/**
	 * Activation function
	 */
	public static function activation(){
		
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
			$_SESSION[CFGP_PREFIX . 'session_expire'] = (time() + (60 * 5));
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
endif;
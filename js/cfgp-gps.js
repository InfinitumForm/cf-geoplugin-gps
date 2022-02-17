/**
 * GPS for CF Geo Plugin
 *
 * @link              http://cfgeoplugin.com/
 * @since             1.0.0
 * @version           1.0.1
 * @package           CF_Geoplugin_GPS
 * @author            INFINITUM FORM
 * @license           GPL-2.0+
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

;(function($){
	var info = [],
		// Send GPS position
		send_position = function( position ){
			var latitude = position.coords.latitude,
				longitude = position.coords.longitude;

			$.get('https://maps.googleapis.com/maps/api/geocode/json',{
				key : CFGEO_GPS.key,
				language : CFGEO_GPS.language,
				latlng : latitude + ',' + longitude
			}).done(function(data){
				if(data.status == 'OK')
				{
					var geo = {}, i, key;
					for(i in data.results[0].address_components)
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
					
					if(geo.administrative_area_level_1){
						geo.region = geo.administrative_area_level_1.long_name;
						geo.state = geo.administrative_area_level_1.long_name;
						geo.regionName = geo.administrative_area_level_1.long_name;
					}
					
					if(geo.political){
						geo.region = geo.political.long_name;
						geo.state = geo.political.long_name;
						geo.regionName = geo.political.long_name;
					}
					
					if(geo.postal_code){
						geo.zip = geo.postal_code.long_name;
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

					$.post(CFGEO_GPS.ajax_url,{
						action : 'cf_geoplugin_gps_set',
						data : geo
					}).done(function(returns){
						if(returns.success === true){
							var href = window.location.href;
							
							if(href.indexOf('?') > -1)
							{
								window.location.href = href + '&gps=1';
								location.href = href + '&gps=1';
								location = href + '&gps=1';
							}
							else
							{
								window.location.href = href + '?gps=1';
								location.href = href + '?gps=1';
								location = href + '?gps=1';
							}
							
							window.history.forward(1);
						}
					});
				}
				else
				{
					var returns = null;
					switch(data.status)
					{
						case 'ZERO_RESULTS':
							returns = CFGEO_GPS.label.ZERO_RESULTS;
							break;
						case 'OVER_DAILY_LIMIT':
							returns = CFGEO_GPS.label.OVER_DAILY_LIMIT;
							break;
						case 'OVER_QUERY_LIMIT':
							returns = CFGEO_GPS.label.OVER_QUERY_LIMIT;
							break;
						case 'REQUEST_DENIED':
							returns = CFGEO_GPS.label.REQUEST_DENIED;
							break;
						case 'INVALID_REQUEST':
							returns = CFGEO_GPS.label.INVALID_REQUEST;
							break;
						case 'UNKNOWN_ERROR':
							returns = CFGEO_GPS.label.DATA_UNKNOWN_ERROR;
							break;
					}
					if(returns)
					{
						if(typeof data.error_message != 'undefined')
							console.error(CFGEO_GPS.label.google_geocode.replace(/%s/g, data.error_message));
						else
							console.info(CFGEO_GPS.label.google_geocode.replace(/%s/g, returns));
					}
				}
			});
		},
		display_error = function( error ){
			var returns = null;
			switch(error.code)
			{
				case error.PERMISSION_DENIED:
					returns = CFGEO_GPS.label.PERMISSION_DENIED
					break;
				case error.POSITION_UNAVAILABLE:
					returns = CFGEO_GPS.label.POSITION_UNAVAILABLE
					break;
				case error.TIMEOUT:
					returns = CFGEO_GPS.label.TIMEOUT
					break;
				case error.UNKNOWN_ERROR:
					returns = CFGEO_GPS.label.UNKNOWN_ERROR
					break;
			}
			
			if(returns) {
				console.error(CFGEO_GPS.label.google_geocode.replace(/%s/g, returns));
			}
		},
		get_location = function(){
			if (navigator.geolocation)
			{
				navigator.geolocation.getCurrentPosition(send_position, display_error);
			}
			else
			{
				console.log(CFGEO_GPS.label.google_geocode);
			}
		}
	get_location();
}(jQuery || window.jQuery || Zepto || window.Zepto));
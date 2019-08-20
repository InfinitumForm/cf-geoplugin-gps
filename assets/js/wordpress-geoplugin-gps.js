/**
 * GPS for CF Geo Plugin
 *
 * @link              http://cfgeoplugin.com/
 * @since             1.0.0
 * @version           1.0.0
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
				key : CFGEO_GPS.key,
				language : CFGEO_GPS.language,
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

					$.post(CFGEO_GPS.ajax_url,{
						action : 'cf_geoplugin_gps_set',
						data : geo,
						_ajax_nonce : CFGEO_GPS.nonce
					}).done(function(returns){
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
							returns = 'Your send bad or broken request to you API call.';
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
}(jQuery || window.jQuery || Zepto || window.Zepto));
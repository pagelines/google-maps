<?php
/*
Section: Google Maps
Author: WunderWeb
Author URI: http://www.wunderweb.se
Version: 1.0
Description: A Google Maps section loaded with features. Add multiple pins, change colors, add custom pins, titles, description and set your custom height.
Class Name: GoogleMaps
Cloning: true
Workswith: main, templates, sidebar_wrap, content, morefoot
Filter: misc, full-width
*/



class GoogleMaps extends PageLinesSection {

	var $default_limit = 1;

	function section_styles(){
		wp_enqueue_script('google-maps-api', 'http://maps.googleapis.com/maps/api/js?sensor=false', array('jquery'));		
	}
	
	function section_foot(){
		
	}
	

	function section_opts(){
		$options = array();

		$options[] = array(

			'title' => __( 'Google Maps Configuration', 'google-maps' ),
			'type'	=> 'multi',
			'opts'	=> array(
				array(
					'key'			=> 'mappins_count',
					'type' 			=> 'count_select',
					'count_start'	=> 1,
					'count_number'	=> 12,
					'default'		=> 1,
					'label' 	=> __( 'Number of Pins on the map', 'google-maps' )
				),
				array(
					'key'			=> 'map_zoom',
					'type' 			=> 'count_select',
					'count_start'	=> 1,
					'count_number'	=> 20,
					'default'		=> '12',
					'label' 	=> __( 'Zoom level on map, where 1 is zoomed out.', 'google-maps' )
				),
				array(
					'key'			=> 'map_height',
					'type' 			=> 'text',
					'default'		=> '360',
					'label' 	=> __( 'Map height in pixels (without px).', 'google-maps' )
				),
				array(
					'key'			=> 'map_color',
					'type' 			=> 'color',
					'default'		=> 'ff0000',
					'label' 	=> __( 'Map color.', 'google-maps' )
				)
			)

		);

		$slides = ($this->opt('mappins_count')) ? $this->opt('mappins_count') : $this->default_limit;
	
		for($i = 1; $i <= $slides; $i++){

			$opts = array(

				array(
					'key'		=> 'mappin_title_'.$i,
					'label'		=> __( 'Pin Title', 'google-maps' ),
					'type'		=> 'text'
				),
				array(
					'key'		=> 'mappin_text_'.$i,
					'label'	=> __( 'Pin Text', 'google-maps' ),
					'type'	=> 'textarea'
				),
				array(
					'key'		=> 'mappin_address_'.$i,
					'label'	=> __( 'Pin Address', 'google-maps' ),
					'type'	=> 'text'
				),
			);

			$opts[] = array(
				'key'			=> 'mappin_image_'.$i,
				'label'			=> __( 'Pin Symbol', 'google-maps' ),
				'type'			=> 'image_upload',
				'sizelimit'		=> 800000,
			);
			
			$options[] = array(
				'title' 	=> __( 'Google Map Pin ', 'google-maps' ) . $i,
				'type' 		=> 'multi',
				'opts' 		=> $opts,
			);

		}

		return $options;
	}


   function section_template( ) { 
	
		$num = ($this->opt('mappins_count')) ? $this->opt('mappins_count') : $this->default_limit;
		$output = '';
		
		$zoom = ($this->opt('map_zoom')) ? $this->opt('map_zoom') : $this->default_zoom;
		$height = ($this->opt('map_height')) ? $this->opt('map_height') : '360';
		$color = ($this->opt('map_color')) ? $this->opt('map_color') : 'ff0000';
	
		for($i = 1; $i <= $num; $i++):

			$link = '';
			$title = ($this->opt('mappin_title_'.$i)) ? $this->opt('mappin_title_'.$i) : 'Map Pin '.$i; 
			$text = ($this->opt('mappin_text_'.$i)) ? $this->opt('mappin_text_'.$i) : ''; 
			$img = '';
			
			$attach_id = $this->opt('mappin_image_'.$i.'_attach_id');
			if($this->opt('mappin_image_'.$i)) {
				
				$full_img = $this->opt('mappin_image_'.$i); 
				
			} else 
				$full_img = '';
				
			if($this->opt('mappin_address_'.$i)) {
					
				$address = $this->opt('mappin_address_'.$i); 
					
			} else 
				$address = 'Varberg, Sverige';
			

			$output .= sprintf(
				'["<h5>%s</h5><p>%s</p>", "%s", "%s"],',
			$title,
			$text,
			$address,
			$full_img
			);
			
			

		 endfor;
	
	
	?>
	
	<div class="google-maps-wrap">
		
		<script type="text/javascript">
				    
				    var geocoder;
				    var map;
				    var markersArray = [];
				    var bounds;
				    var infowindow =  new google.maps.InfoWindow({
				        content: ''
				    });
				    
				    //plot initial point using geocode instead of coordinates (works just fine)
				    function initialize_<?php echo $map_id; ?>() {
				        geocoder = new google.maps.Geocoder();
				        bounds = new google.maps.LatLngBounds ();
				    
				        var myOptions = {
				            mapTypeId: google.maps.MapTypeId.ROADMAP,
				            scrollwheel: false,
				            navigationControlOptions: {
				                style: google.maps.NavigationControlStyle.SMALL
				            },
				            styles:[
				            	{ featureType: "road.geometry.landscape", stylers: [ { hue: "#<?php echo $color; ?>" } ] }
				            ]
				        };
				        map = new google.maps.Map(document.getElementById("googleMap"), myOptions);
				    
				        geocoder.geocode( { 'address': ''}, function(results, status) { 
				            if (status == google.maps.GeocoderStatus.OK) {
				                map.setCenter(results[0].geometry.location);
				    
				                marker = new google.maps.Marker({
				                    map: map,
				                    position: results[0].geometry.location
				                });
				    
				                bounds.extend(results[0].geometry.location);
				    
				                markersArray.push(marker);
				            }
				        });
				    
				        plotMarkers();
				    }
				    
				    var locationsArray = [
				    
				    	<?php echo $output; ?>
				        
				    ];
				    
				    function plotMarkers(){
				        var i;
				        for(i = 0; i < locationsArray.length; i++){
				            codeAddresses(locationsArray[i]);
				        }
				    }
				    
				    function codeAddresses(address){
				        geocoder.geocode( { 'address': address[1]}, function(results, status) { 
				            if (status == google.maps.GeocoderStatus.OK) {
				                marker = new google.maps.Marker({
				                    map: map,
				                    icon: address[2],
				                    position: results[0].geometry.location
				                });
				    
				                google.maps.event.addListener(marker, 'click', function() {
				                    infowindow.setContent(address[0]);
				                    infowindow.open(map, this);
				                });
				    
				                bounds.extend(results[0].geometry.location);
				    
				                markersArray.push(marker); 
				            }
				            else{
				                alert("Geocode was not successful for the following reason: " + status);
				            }
				    		
				    		
				            map.fitBounds(bounds);
				            zoomChangeBoundsListener = 
				                google.maps.event.addListenerOnce(map, 'bounds_changed', function(event) {
				                    if (this.getZoom()){
				                        this.setZoom(<?php echo $zoom; ?>);
				                    }
				            });
				            setTimeout(function(){google.maps.event.removeListener(zoomChangeBoundsListener)}, 2000);
				        });
				    }
				    
				    google.maps.event.addDomListener(window, 'load', initialize_<?php echo $map_id; ?>);
				  </script>
		<div id="googleMap" class="content-row" style="width:100%; height:<?php echo $height; ?>px;"></div>
	</div>

<?php }


}
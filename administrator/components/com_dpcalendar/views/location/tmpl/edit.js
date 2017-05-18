dpjQuery(document).ready(function(){
	dpjQuery('#jform_street, #jform_number, #jform_zip, #jform_city, #jform_country, #jform_province').bind('change', function(e) {
		dpjQuery("#jform_geocomplete").val(getAddresString());
		dpjQuery("#jform_geocomplete").trigger("geocode");
	});
	
	dpjQuery("#jform_geocomplete").geocomplete({
        map: ".map_canvas",
        location: getAddresString(),
        markerOptions: {
          draggable: true
        }
     });
	dpjQuery("#jform_geocomplete").bind("geocode:result", function(event, result){
		if (dpjQuery("#jform_geocomplete").data('initialized')) {
			setGeoResult(result);
		}
		dpjQuery("#jform_geocomplete").data('initialized', true);
	});
	dpjQuery("#jform_geocomplete").bind("geocode:dragged", function(event, latLng){
		dpjQuery.ajax({
			  url:"//maps.googleapis.com/maps/api/geocode/json?latlng="+latLng.lat()+","+latLng.lng()+"&sensor=true",
			  type: "POST",
			  success:function(res){
				 if(res.results[0].address_components.length){
					 setGeoResult(res.results[0]);
				 }
			  }
			});
    });
});

function getAddresString()
{
	var address = '';
	var street = '';
	var city = '';
	var zip = '';
	var province = '';
	var country = '';
	if(dpjQuery("#jform_street").val()){
		street = dpjQuery("#jform_street").val();
		
		if(dpjQuery("#jform_number").val()){
			street += ' ' + dpjQuery("#jform_number").val();
		}
		
		street += ', ';
	}
	if(dpjQuery("#jform_city").val()){
		city = dpjQuery("#jform_city").val();
		if(dpjQuery("#jform_zip").val()){
			city += ' ' + dpjQuery("#jform_zip").val();
		}
		
		city += ', ';
	}
	if(dpjQuery("#jform_province").val()){
		province = dpjQuery("#jform_province").val() + ', ';
	}
	if(dpjQuery("#jform_country").val()){
		country = dpjQuery("#jform_country").val() + ', ';
	}
	return street + city + province + country;
}

function setGeoResult(result)
{
	dpjQuery('#location-form #details input:not("#jform_title")').removeAttr('value');
	
	for(var i=0;i<result.address_components.length;i++){
		switch(result.address_components[i].types[0]){
			case 'street_number':
				dpjQuery("#jform_number").val(result.address_components[i].long_name);
			break;
			case 'route':
				dpjQuery("#jform_street").val(result.address_components[i].long_name);
			break;
			case 'locality':
				dpjQuery("#jform_city").val(result.address_components[i].long_name);
			break;
			case 'administrative_area_level_1':
				dpjQuery("#jform_province").val(result.address_components[i].long_name);
			break;
			case 'country':
				dpjQuery("#jform_country").val(result.address_components[i].long_name);
			break;
			case 'postal_code':
				dpjQuery("#jform_zip").val(result.address_components[i].long_name);
			break;
		}
	}
	
	if (typeof result.geometry.location.lat === 'function')
	{
		console.log(result.geometry.location);
		dpjQuery("#jform_latitude").val(result.geometry.location.lat());
		dpjQuery("#jform_longitude").val(result.geometry.location.lng());
	} else
	{		
		dpjQuery("#jform_latitude").val(result.geometry.location.lat);
		dpjQuery("#jform_longitude").val(result.geometry.location.lng);
	}
	

	if (dpjQuery("#jform_title").val() == '')
	{
		dpjQuery("#jform_title").val(result.formatted_address);
	}
	
	dpjQuery("#jform_geocomplete").val(result.formatted_address);
}

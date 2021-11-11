dpjQuery(document).ready(function() {
	dpjQuery('#scheduling-options, #jform_scheduling_monthly_options, #jform_scheduling_daily_weekdays').bind('click', function(e) {
		changeVisiblity();
		updateRuleFromForm();
	});
	dpjQuery('#jform_scheduling_end_date, #jform_scheduling_interval, #jform_scheduling_repeat_count').bind('change', function() {
		updateRuleFromForm();
	});
	dpjQuery('#jform_scheduling_weekly_days, #jform_scheduling_monthly_days, #jform_scheduling_monthly_week_days, #jform_scheduling_monthly_week').bind('change', function() {
		updateRuleFromForm();
	});
	dpjQuery('#jform_rrule').bind('change', function(e) {
		updateFormFromRule();
	});
	updateFormFromRule();

	dpjQuery('#scheduling-expert-button').click(function() {		
		dpjQuery('#scheduling-rrule').children().fadeToggle();
	});
	dpjQuery('#scheduling-rrule').children().hide();

	dpjQuery('#jform_location_ids').bind('change', function(e) {
		updateLocationFrame();
	});
	dpjQuery('#location-tab').click(function() {
		updateFormFromRule();
	});
	
	dpjQuery('#location-activator').click(function() {		
		dpjQuery('#location-form').fadeToggle();
	});
	dpjQuery('#location-remove').click(function() {		
		var data = {};
		data[dpjQuery('#location_token').val()] = '1';
		data['ajax'] = '1';
		data['cid'] = dpjQuery('#jform_location_ids option:selected').map(function() { return this.value }).get();
		dpjQuery.ajax({
			type: 'POST',
			url: 'index.php?option=com_dpcalendar&task=locationforms.trash',
			data: data,
			success: function (data) {
				var json = dpjQuery.parseJSON(data);
				if (json.success) {
					dpjQuery('#jform_location_ids option:selected').remove();
					dpjQuery('#jform_location_ids').trigger("liszt:updated");
					updateLocationFrame();
				}
				Joomla.renderMessages(json.messages);
			}
		});
	});

	dpjQuery('#location-save-button').click(function() {
		var data = {jform:{
				title: dpjQuery('#location_title').val(), 
				country: dpjQuery('#location_country').val(), 
				province: dpjQuery('#location_province').val(), 
				city: dpjQuery('#location_city').val(), 
				zip: dpjQuery('#location_zip').val(), 
				street: dpjQuery('#location_street').val(), 
				number: dpjQuery('#location_number').val(), 
				room: dpjQuery('#location_room').val(),
				state: 1,
				language: '*'}};
		data[dpjQuery('#location_token').val()] = '1';
		data['ajax'] = '1';
		data['id'] = 0;
		dpjQuery.ajax({
			type: 'POST',
			url: 'index.php?option=com_dpcalendar&task=locationform.save',
			data: data,
			success: function (data) {
				var json = dpjQuery.parseJSON(data);
				if (json.data.id != null && json.data.display != null) {
					dpjQuery('#jform_location_ids').append('<option value="'+json.data.id+'" selected="selected">'+json.data.display+'</option>');
					dpjQuery('#jform_location_ids').trigger("liszt:updated");
					updateLocationFrame();
				}
				Joomla.renderMessages(json.messages);
				dpjQuery('#location-form').fadeToggle();
			}
		});
	});
	dpjQuery('#location-cancel-button').click(function() {		
		dpjQuery('#location-form').fadeToggle();
	});
	dpjQuery('#location-form').hide();
	
	dpjQuery('#jform_all_day input').bind('click', function(e) {
		var input = dpjQuery(this);
		if (input.val() == 0) {
			dpjQuery('#jform_start_date_time, #jform_end_date_time').show();
		} else {
			dpjQuery('#jform_start_date_time, #jform_end_date_time').hide();
			
		}
		
		dpjQuery('#jform_all_day label').attr('class', 'btn');
		input.next().attr('class', 'btn btn-success');
	});
	dpjQuery('#jform_all_day label').attr('class', 'btn');
	if (dpjQuery('#jform_all_day0')[0].checked || (!dpjQuery('#jform_all_day0')[0].checked && !dpjQuery('#jform_all_day1')[0].checked)) {
		dpjQuery('#jform_all_day label[for="jform_all_day0"]').attr('class', 'btn btn-success');
	} else {
		dpjQuery('#jform_all_day label[for="jform_all_day1"]').attr('class', 'btn btn-success');		
	}
	
	// Attendance
	dpjQuery('#attend-state-checkbox').click(function() {		
		dpjQuery('.attend-control-group').fadeToggle();
	});
	
	if (!dpjQuery('#attend-state-checkbox').is(':checked')) {
		dpjQuery('.attend-control-group').hide();
	}
	
	dpjQuery('#dp-form-location-tab').on('show', function(e) {
		var initialized = dpjQuery('#dp-form-location-tab').data('map-initialized');
		if (!initialized) {
			updateLocationFrame();
			dpjQuery('#dp-form-location-tab').data('map-initialized', true)
		}
	});
});

function updateFormFromRule() {
	if (dpjQuery('#jform_rrule').val() == undefined) {
		return;
	}
	var frequency = null;
	dpjQuery.each(dpjQuery('#jform_rrule').val().split(';'), function() {
		var parts = this.split('=');
		if (parts.length > 1) {
			switch(parts[0]) {
				case 'FREQ':
					dpjQuery('#jform_scheduling input').each(function() {
						var sched = dpjQuery(this);
						if (parts[1] == sched.val()) {
							sched.attr('checked', 'checked');
							if (parts[1] == '0') {						
							} else {
								frequency = sched.val();
							}
							sched.next().attr('class', 'btn btn-success');
						} else {
							sched.next().attr('class', 'btn');							
						}
					});
					break;
				case 'BYDAY':
					dpjQuery.each(parts[1].split(','), function() {
						if (frequency == 'MONTHLY') {
							var pos = this.length;
							var day = this.substring(pos - 2, pos);
							var week = this.substring(0, pos - 2);
							
							if (week == -1) {
								week = 'last';
							}
							
							dpjQuery('#jform_scheduling_monthly_week option[value="'+week+'"]').prop('selected', true);				
							dpjQuery('#jform_scheduling_monthly_week_days option[value="'+day+'"]').prop('selected', true);				
						} else {
							dpjQuery('#jform_scheduling_weekly_days option[value="'+this+'"]').prop('selected', true);
						}
					});
					break;
				case 'BYMONTHDAY':
					dpjQuery('#jform_scheduling_monthly_options input[value="by_day"]').attr('checked', 'checked');
					dpjQuery.each(parts[1].split(','), function() {
						dpjQuery('#jform_scheduling_monthly_days option[value="'+this+'"]').prop('selected', true);				
					});
					break;
				case 'COUNT':
					dpjQuery('#jform_scheduling_repeat_count').val(parts[1]);
					break;
				case 'INTERVAL':
					dpjQuery('#jform_scheduling_interval').val(parts[1]);
					break;
				case 'UNTIL':
					var t = parts[1];
					dpjQuery('#jform_scheduling_end_date').val(t.substring(0, 4)+'-'+t.substring(4, 6)+'-'+t.substring(6, 8));
					break;
			}
		}
	});
	changeVisiblity();
}
function updateRuleFromForm() {
	var rule = '';
	if (dpjQuery('#jform_scheduling1')[0].checked) {
		rule = 'FREQ=DAILY';
		if (dpjQuery('#jform_scheduling_daily_weekdays1')[0].checked) {
			rule = 'FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR';			
		}
	}
	if (dpjQuery('#jform_scheduling2')[0].checked) {
		rule = 'FREQ=WEEKLY';
		
		var boxes = dpjQuery('#jform_scheduling_weekly_days option:selected');
		if (boxes.length > 0) {
			rule += ';BYDAY=';
			boxes.each(function() {
				rule += dpjQuery(this).val()+',';
			});
			rule = rule.slice(0, - 1);
		}
	}
	if (dpjQuery('#jform_scheduling3')[0].checked) {
		rule = 'FREQ=MONTHLY';
		if (dpjQuery('#jform_scheduling_monthly_options0')[0].checked) {			
			var boxes = dpjQuery('#jform_scheduling_monthly_days option:selected');
			if (boxes.length > 0) {
				rule += ';BYMONTHDAY=';
				boxes.each(function() {
					rule += dpjQuery(this).val()+',';
				});
				rule = rule.slice(0, - 1);
			}
		} else {
			var weeks = dpjQuery('#jform_scheduling_monthly_week option:selected');
			var trim = false;
			if (weeks.length > 0) {
				rule += ';BYDAY=';
				weeks.each(function() {
					var days = dpjQuery('#jform_scheduling_monthly_week_days option:selected');
					if (days.length > 0) {
						var week = dpjQuery(this).val();
						if (week == 'last') {
							week = -1;
						}
						days.each(function() {
							rule += week+dpjQuery(this).val()+',';
							trim = true;
						});
					}
				});
				if (trim)
					rule = rule.slice(0, - 1);
			}
		}
	}
	if (dpjQuery('#jform_scheduling4')[0].checked) {
		rule = 'FREQ=YEARLY';
	}
	if (rule.length > 1) {
		var interval = dpjQuery('#jform_scheduling_interval').val();
		if (interval > 0) {
			rule += ';INTERVAL='+interval;
		}
		var count = dpjQuery('#jform_scheduling_repeat_count').val();
		if (count > 0) {			
			rule += ';COUNT='+count;
		}
		var until = dpjQuery('#jform_scheduling_end_date').val();
		if (until != '0000-00-00' && until.length == 10) {			
			rule += ';UNTIL='+until.replace(/\-/g, '')+'T235900Z';
		}
	}
	dpjQuery('#jform_rrule').val(rule);
}

function updateLocationFrame() {
	var lat = 0; 
	var long = 0;
	if( typeof geoip_latitude === 'function' ) {
		lat = geoip_latitude(); 
		long = geoip_longitude(); 
	}
	
	var dpcalendarMap = new google.maps.Map(document.getElementById('event-location-frame'), {zoom: 4, mapTypeId: google.maps.MapTypeId.ROADMAP, center: new google.maps.LatLng(lat, long)});
	var dpcalendarMapBounds = new google.maps.LatLngBounds();
	
	dpjQuery('#jform_location_ids option:selected').each(function() {
		var content = dpjQuery(this).html();
		var parts = content.substring(content.lastIndexOf('[')+1, content.lastIndexOf(']')).split(':');
		if (parts.length < 2) return;
		if (parts[0] == 0 && parts[1] == 0) return;
		
		var l = new google.maps.LatLng(parts[0], parts[1]);
		var marker = new google.maps.Marker({position: l, map: dpcalendarMap, title: content});
	 	
	 	var infowindow = new google.maps.InfoWindow({content: content});
	 	google.maps.event.addListener(marker, 'click', function() {infowindow.open(dpcalendarMap, marker);});
	 	
	 	dpcalendarMapBounds.extend(l);
	 	dpcalendarMap.setCenter(dpcalendarMapBounds.getCenter());
	});
}

function changeVisiblity() {
	dpjQuery('#jform_scheduling label').attr('class', 'btn');
	
	// no scheduling
	if (dpjQuery('#jform_scheduling0')[0].checked) {
		dpjQuery('#scheduling-options-start').hide();
		dpjQuery('#scheduling-options-end').hide();
		dpjQuery('#scheduling-options-interval').hide();
		dpjQuery('#scheduling-options-repeat_count').hide();
		dpjQuery('#scheduling-expert-button').hide();

		dpjQuery('#jform_scheduling [for="jform_scheduling0"]').attr('class', 'btn btn-success');
	} else {			
		dpjQuery('#scheduling-options-start').show();
		dpjQuery('#scheduling-options-end').show();
		dpjQuery('#scheduling-options-interval').show();
		dpjQuery('#scheduling-options-repeat_count').show();
		dpjQuery('#scheduling-expert-button').show();
	}
	
	// daily
	if (dpjQuery('#jform_scheduling1')[0].checked) {
		dpjQuery('#scheduling-options-day').show();

		dpjQuery('#jform_scheduling [for="jform_scheduling1"]').attr('class', 'btn btn-success');
		
		dpjQuery('#jform_scheduling_daily_weekdays label').attr('class', 'btn');
		if (dpjQuery('#jform_scheduling_daily_weekdays1')[0].checked) {
			dpjQuery('#jform_scheduling_daily_weekdays [for="jform_scheduling_daily_weekdays1"]').attr('class', 'btn btn-success');
		} else {
			dpjQuery('#jform_scheduling_daily_weekdays [for="jform_scheduling_daily_weekdays0"]').attr('class', 'btn btn-success');
		}
	} else {			
		dpjQuery('#scheduling-options-day').hide();
	}
	
	// weekly
	if (dpjQuery('#jform_scheduling2')[0].checked) {
		dpjQuery('#scheduling-options-week').show();

		dpjQuery('#jform_scheduling [for="jform_scheduling2"]').attr('class', 'btn btn-success');
	} else {			
		dpjQuery('#scheduling-options-week').hide();
	}
	
	// monthly
	if (dpjQuery('#jform_scheduling3')[0].checked) {
		dpjQuery('.scheduling-options-month').show();
		dpjQuery('#jform_scheduling_monthly_options label').attr('class', 'btn');
		if (dpjQuery('#jform_scheduling_monthly_options0')[0].checked) {
			dpjQuery('#scheduling-options-month-week').hide();
			dpjQuery('#scheduling-options-month-week-days').hide();
			dpjQuery('#scheduling-options-month-days').show();

			dpjQuery('#jform_scheduling_monthly_options label[for="jform_scheduling_monthly_options0"]').attr('class', 'btn btn-success');
		} else {			
			dpjQuery('#scheduling-options-month-week').show();
			dpjQuery('#scheduling-options-month-week-days').show();
			dpjQuery('#scheduling-options-month-days').hide();

			dpjQuery('#jform_scheduling_monthly_options label[for="jform_scheduling_monthly_options1"]').attr('class', 'btn btn-success');
		}

		dpjQuery('#jform_scheduling [for="jform_scheduling3"]').attr('class', 'btn btn-success');
	} else {			
		dpjQuery('.scheduling-options-month').hide();
	}
	
	// yearly
	if (dpjQuery('#jform_scheduling4')[0].checked) {
		dpjQuery('#jform_scheduling [for="jform_scheduling4"]').attr('class', 'btn btn-success');
	}
}

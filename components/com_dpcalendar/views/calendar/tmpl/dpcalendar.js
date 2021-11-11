dpjQuery(document).ready(function() {
	dpjQuery(document).on('click', '#dpcalendar_component_date_picker_button', function(e) {
		dpjQuery('#dpcalendar_component_date_picker').datepicker('show');
	});
	dpjQuery(document).on('click', '#dpcalendar_component_print_button', function(e) {
		var loc=document.location.href.replace(/\?/,"\?layout=print&format=raw\&");
		if (loc==document.location.href)
			loc=document.location.href.replace(/#/,"\?layout=print&format=raw#");
		var printWindow = window.open(loc);
		printWindow.focus();
	});

	dpjQuery('#dpcalendar_view_toggle_status').bind('click', function(e) {
		dpjQuery('#dpcalendar_view_list').slideToggle('slow', function() {
			var oldImage = dpjQuery('#dpcalendar_view_toggle_status').attr('src');
			var gcalImage = oldImage;
			var path = oldImage.substring(0, oldImage.lastIndexOf('/'));

			if (!dpjQuery('#dpcalendar_view_list').is(":visible"))
				var gcalImage = path + '/down.png';
			else
				var gcalImage = path + '/up.png';

			dpjQuery('#dpcalendar_view_toggle_status').attr('src', gcalImage);
		  });
	});
	
    dpjQuery('#dpcal-create').click(function(){
    	dpjQuery('#editEventForm').submit();
    });
    dpjQuery('.dpcal-cancel').click(function(){
    	dpjQuery('#editEventForm').toggle();
    	dpjQuery('#editEventForm #jform_title').val('');
    	return false;
    });
    dpjQuery('#dpcal-edit').click(function(){
        dpjQuery('#editEventForm #task').val('');
        dpjQuery('#editEventForm').submit();
    });
    
    dpjQuery('body').click(function(e) {
    	var form = dpjQuery('#editEventForm');

        if (form.has(e.target).length === 0 && !dpjQuery('#ui-datepicker-div').is(':visible') && !dpjQuery(e.target).hasClass('ui-timepicker-selected')) {  	
        	form.hide();
        }
    });
});

function updateDPCalendarFrame(calendar) {
	if (calendar.checked) {
		dpjQuery('#dpcalendar_component').fullCalendar('addEventSource', calendar.value);
	} else {
		dpjQuery('#dpcalendar_component').fullCalendar('removeEventSource', calendar.value);
	}
}

/*
 * hash change function
 */
(function($,e,b){var c="hashchange",h=document,f,g=$.event.special,i=h.documentMode,d="on"+c in e&&(i===b||i>7);function a(j){j=j||location.href;return"#"+j.replace(/^[^#]*#?(.*)$/,"$1")}$.fn[c]=function(j){return j?this.bind(c,j):this.trigger(c)};$.fn[c].delay=50;g[c]=$.extend(g[c],{setup:function(){if(d){return false}$(f.start)},teardown:function(){if(d){return false}$(f.stop)}});f=(function(){var j={},p,m=a(),k=function(q){return q},l=k,o=k;j.start=function(){p||n()};j.stop=function(){p&&clearTimeout(p);p=b};function n(){var r=a(),q=o(m);if(r!==m){l(m=r,q);$(e).trigger(c)}else{if(q!==m){location.href=location.href.replace(/#.*/,"")+q}}p=setTimeout(n,$.fn[c].delay)}$.browser.msie&&!d&&(function(){var q,r;j.start=function(){if(!q){r=$.fn[c].src;r=r&&r+a();q=$('<iframe tabindex="-1" title="empty"/>').hide().one("load",function(){r||l(a());n()}).attr("src",r||"javascript:0").insertAfter("body")[0].contentWindow;h.onpropertychange=function(){try{if(event.propertyName==="title"){q.document.title=h.title}}catch(s){}}}};j.stop=k;o=function(){return a(q.location.href)};l=function(v,s){var u=q.document,t=$.fn[c].domain;if(v!==s){u.title=h.title;u.open();t&&u.write('<script>document.domain="'+t+'"<\/script>');u.close();q.location.hash=v}}})();return j})()})(jQuery,this);
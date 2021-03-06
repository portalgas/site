"use strict";

var modalMsg = '<div class="modal fade" id="modalMsg" role="dialog"><div class="modal-dialog modal-sm"><div class="modal-content"><div class="modal-body"><p>MSG</p></div><div class="modal-footer"><button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Close</button></div></div></div></div>';
  
function Modal(modalCallerId, modalHeader, modalBody, modalSubmitFunc, modalSubmitText) {

	if (!(this instanceof Modal)) {
		throw new TypeError("Modal constructor cannot be called as a function.");
	}
	
	this.init(modalCallerId, modalHeader, modalBody, modalSubmitFunc, modalSubmitText);
}
;
	
Modal.prototype = {
	constructor: Modal, //costruttore
	
	bindEvents: function () {
		var _this = this;
		
		/*console.log("Modal bindEvents");*/
		
        jQuery('#'+_this.modalCallerId).on("click", function (event) {
            event.preventDefault();
            _this.doModal();
        });
		
	},	
	
	bindEventsAfterAppend: function () {
		var _this = this;
		
		/*console.log("Modal bindEventsAfterAppend");*/
		
        jQuery('#'+_this.modalCallerId).on("click", function (event) {
            event.preventDefault();
            _this.doModal();
        });
		
        jQuery(_this.idModal).on("shown.bs.modal", function () {
            /*console.log("event show.bs.modal");*/

            jQuery(_this.idModal).find(".modal-header").html(_this.modalHeader);
            jQuery(_this.idModal).find(".modal-body").html(_this.modalBody);
            jQuery(_this.idModal).find(".modal-body").css("min-height", "50px");
            jQuery(_this.idModal).find(".modal-body").css("background", "url('/images/cake/ajax-loader.gif') no-repeat scroll center 0 transparent");

			_this.callUrl();
        });

        jQuery(_this.idModal).on("hide.bs.modal", function () {
            /*console.log("event hide.bs.modal");*/

            jQuery(_this.idModal).find(".modal-header").html("");            
            jQuery(_this.idModal).find(".modal-body").html("");
			
			jQuery(_this.idModal).detach();
        });			
	},

	doModal: function () {
		var html = '';
		
		html =  '<div class="modal fade" id="modalWindow" role="dialog">';
		html += '<div class="modal-dialog">';
		html += '<div class="modal-content">';
		html += '<div class="modal-header">';
		html += '<button type="button" class="close" data-dismiss="modal">&times;</button>';
		html += '<h4 class="modal-title">'+this.modalHeader+'</h4>';
		html += '</div>';
		html += '<div class="modal-body">';
		html += '<p>';
		html += this.modalBody;
		html += '</div>';
		html += '<div class="modal-footer">';
		if (this.modalSubmitText!='') {
			html += '<button type="button" class="btn btn-primary" data-dismiss="modal" onClick="'+this.modalSubmitFunc+'">'+this.modalSubmitText+'</button>';
		}
		html += '<button type="button" class="btn btn-success" data-dismiss="modal">Chiudi</button>'; 
		html += '</div>'; 
		html += '</div>'; 
		
		jQuery(html).appendTo('body');
		jQuery(this.idModal).modal('show');
		this.bindEventsAfterAppend();
	},
	
	callUrl: function() {
		
		var _this = this;
		
		if(_this.modalUrl=='')
			return;
			
		jQuery.ajax({
			type: "GET",
			url: this.modalUrl,
			dataType: "html",
		})
		.fail(function () {
			console.log("Errore di sistema!");
		})
		.done(function (response) {
			jQuery(_this.idModal).find(".modal-body").css("background", "none repeat scroll 0 0 transparent");
			jQuery(_this.idModal).find(".modal-body").html(response);
			/* console.log("Chiamata avvenuta con successo"); */ 
		});			
	},
			
    getUrl: function () {
        return jQuery("#" + this.modalCallerId).attr('data-attr-url');
    },
	
    init: function (modalCallerId, modalHeader, modalBody, modalSubmitFunc, modalSubmitText) {
		this.modalCallerId = modalCallerId;
        this.modalHeader = modalHeader;
        this.modalBody = modalBody;
        this.modalSubmitFunc = modalSubmitFunc;
        this.modalSubmitText = modalSubmitText;
        this.modalUrl = this.getUrl();
		this.idModal = '#modalWindow';
		
        /*console.log("Modal.init - modalHeader " + this.modalHeader + " modalBody " + this.modalBody);*/

        this.bindEvents();
    }
}
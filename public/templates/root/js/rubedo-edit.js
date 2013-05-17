/******************************
 * 		Global variables
 *****************************/
var contentId = "";
var imageId = "";
var cache = new Array();
var dateCache = new Array();
var object = null;
var errors = new Array();
var timeCache = new Array();
var numberCache = new Array();
var starEdit=false;
var EditMode=true;
var ratingCache=new Array();
/*****************************/

jQuery("body").css("cursor" , "default");

jQuery('#contentToolBar').css('top', '0');
jQuery('#contentToolBar').show();

CKEDITOR.on('instanceCreated', function(event) {
	var editor = event.editor, element = editor.element;
	editor.config.entities = false;
	editor.config.entities_latin = false;
	editor.config.language = jQuery("body").attr("data-language");
	
	// Customize CKEditor
	if (element.getAttribute("data-field-type") =="title" || element.getAttribute("data-field-type") =="text" || element.getAttribute("data-field-type") =="textfield" || element.getAttribute("data-field-type") =="textareafield") {
		
		//Minimal configuration for titles
		editor.on('configLoaded', function() {
			// Remove unnecessary plugins
			editor.config.removePlugins = 'colorbutton,find,flash,font,' + 'forms,iframe,image,newpage,removeformat,scayt,' + 'smiley,specialchar,stylescombo,templates,wsc';

			editor.getData=function(){return(editor.editable().getText());};
			editor.forcePasteAsPlainText = true;
			
			// Make toolbar
			editor.config.toolbar = [
				{ name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo' ] },
			];
		});
		
	} else if (element.getAttribute("data-field-type") =="CKEField"){
		
		var idAndField = (jQuery(element).attr("id")).split("_");
		var field = idAndField[1];

		if( element.getAttribute("data-cke-config") == "Standard"){
			editor.on('configLoaded', function() {
				// set standard configuration
				editor.config.toolbar = [
                   { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
                   { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']},
                   { name: 'colors', items: [ 'TextColor', '-','BGColor' ] },'/',
                   { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
                   { name: 'insert', items: [ 'Image',  '-', 'Table', 'SpecialChar', 'PageBreak', 'Link', "Rubedolink", 'Unlink'] },
                   { name: 'managing', items: [ 'Maximize','-','Undo', 'Redo'  ] }
               ];
				
				// set file and media explorer path
				editor.config.filebrowserImageBrowseUrl = "/backoffice/ext-finder?type=Image";
				editor.config.filebrowserImageUploadUrl = "/backoffice/ext-finder?type=Image";
				editor.config.extraPlugins = 'rubedolink';
			});
		} else if (element.getAttribute("data-cke-config") == "Basic") {
			editor.on('configLoaded', function() {
				// set standard configuration
				editor.config.toolbar = [
	                { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline','Strike', '-', 'RemoveFormat' ] },
	                { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock','-','Image']},
	                { name: 'colors', items: [ 'TextColor', '-','BGColor' ] },
	                { name: 'styles', items: [ 'Font', 'FontSize' ] }
                ];
				
				// set file and media explorer path
				editor.config.filebrowserImageBrowseUrl = "/backoffice/ext-finder?type=Image";
				editor.config.filebrowserImageUploadUrl = "/backoffice/ext-finder?type=Image";
			});
		} else {
			editor.on('configLoaded', function() {
				// set standard configuration
				editor.config.toolbar = [
					{ name: 'document', groups: [ 'mode', 'document', 'doctools' ], items: [ 'Source', '-', 'NewPage', 'Preview', 'Print', '-', 'Templates' ] },
					{ name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
					{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ], items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
					{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
					'/',
					{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']},
					{ name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
					'/',
					{ name: 'colors', items: [ 'TextColor', '-','BGColor' ] },
					{ name: 'tools', items: [ 'Maximize', '-','ShowBlocks' ] },
					{ name: 'links', items: [ 'Link', "Rubedolink", 'Unlink','-','Anchor' ] },
					{ name: 'insert', items: [ 'Image',  '-', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'Iframe' ] }
				];
				
				// set file and media explorer path
				editor.config.filebrowserImageBrowseUrl = "/backoffice/ext-finder?type=Image";
				editor.config.filebrowserImageUploadUrl = "/backoffice/ext-finder?type=Image";
				editor.config.extraPlugins = 'rubedolink';
			});
		}
		
	} else {
		
		editor.on('configLoaded', function() {
			// set standard configuration
			editor.config.toolbar = [
			    { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
	            { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']},
	            { name: 'colors', items: [ 'TextColor', '-','BGColor' ] },'/',
	            { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
	            { name: 'insert', items: [ 'Image',  '-', 'Table', 'SpecialChar', 'PageBreak', 'Link', "Rubedolink", 'Unlink'] },
	            { name: 'managing', items: [ 'Maximize','-','Undo', 'Redo'  ] }
	        ];
			
			// set file and media explorer path
			editor.config.filebrowserImageBrowseUrl = "/backoffice/ext-finder?type=Image";
			editor.config.filebrowserImageUploadUrl = "/backoffice/ext-finder?type=Image";
			editor.config.extraPlugins = 'rubedolink';
		});
		
	}
	
	//var targetId = element.getInputId();
	//element.editor= editor.replace(targetId,{toolbar:  myTBConfig, extraPlugins:'rubedolink',resize_enabled:false, filebrowserImageBrowseUrl:"ext-finder?type=Image", filebrowserImageUploadUrl:"ext-finder?type=Image"}); 
});

jQuery('#btn-edit').click(function() {
	swithToEditMode();
	starEdit=true;
});

jQuery('#btn-cancel').click(function() {
	var changed = checkIfDirty();
	var cacheChanged = 0;
	var dateCacheChanged = 0;
	var timeCacheChanged = 0;
	var numberCacheChanged = 0;
	
	/**
	 * Count modifications on images
	 */
	for(var i in cache){
		cacheChanged++;
	}
	
	/**
	 *  Count modifications on dates
	 */
	for(var contentId in dateCache) {
		dateCacheChanged++;
	}
	
	/**
	 * Count modifications on times
	 */
	for(var contentId in timeCache) {
		timeCacheChanged++;
	}
	
	/**
	 * Count modifications on numbers
	 */
	for(var contentId in numberCache) {
		numberCacheChanged++;
	}
	
	if (changed || cacheChanged > 0 || dateCacheChanged > 0 || timeCacheChanged > 0 || numberCacheChanged > 0) {
		jQuery('#confirm').modal();
	} else {
		swithToViewMode();
		location.reload();
	}
});

jQuery('#cancel-confirm').click(function() {
	//undoAllChanges();
	swithToViewMode();
	location.reload()
});
/**
 * Save button popover
 */
jQuery("#btn-save").mouseenter(function(){
	jQuery("#btn-save").popover("show");
});
jQuery("#btn-save").mouseleave(function(){
	jQuery("#btn-save").popover("hide");
})
/**
 * Ctrl+s
 * Save function
 */
$(document).keydown(function(event) {
	if(event.ctrlKey)
		{
		 if (event.which == 83 ) {
			 if(EditMode==true)
				 {
		     event.preventDefault();
		    jQuery('#btn-save').click();
				 }
		   }
		}
	});
jQuery('#btn-save').click(function() {
	var modified = false;
	/**
	 * Save CKE fields (Rich text & TextArea)
	 */
	for ( var i in CKEDITOR.instances) {
		if (CKEDITOR.instances[i].checkDirty()) {
			modified = true;
			// saving content
			/*
			 * Check if CKE instance id can be splitted
			 */
			var CKEId=CKEDITOR.instances[i].element.getId().split("#");
			if(CKEId.length>1)
				{
				//if CKE instance can be splitted, search all instance with same ID and add them to data
				var data=Array();
					for ( var z in CKEDITOR.instances) {
						var id=CKEDITOR.instances[z].element.getId().split("#");
						if(id.length>1){
							if(id[0]==CKEId[0]){
									data.push(CKEDITOR.instances[z].getData());
								}
						}
						//Remove dirty flag
						CKEDITOR.instances[z].resetDirty();
					}
					save(CKEId[0],data);
				}else{
					save(CKEDITOR.instances[i].element.getId(), CKEDITOR.instances[i].getData());
					//Remove dirty flag
					CKEDITOR.instances[i].resetDirty();
				}
			
			//Remove dirty flag
			
		}
	}
	/**
	 * Save rating fields
	 */
	for( var contentId in ratingCache) {
		modified = true;
		
		save(contentId, ratingCache[contentId])
		}
	
	
	
	/**
	 * Save images
	 */
	for( var id in cache ) {
		modified = true;
		save(id, cache[id].newImage);
	}
	
	/**
	 * Save dates
	 */
	for( var contentId in dateCache) {
		modified = true;
		save(contentId, dateCache[contentId].newDate)
		}
	
	/**
	 * save times
	 */
	for( var contentId in timeCache) {
		modified = true;
		save(contentId, timeCache[contentId].newTime);
	}
	
	/**
	 * save numbers
	 */
	for( var contentId in numberCache) {
		modified = true;
			save(contentId, numberCache[contentId].newNumber);
		
		
	}
	
	if(errors.length > 0) {
		notify('failure', 'Une erreur est survenue, il est possible que vos modifications soient perdues');
	} else if (modified == true){
		notify('success', 'Les données ont été sauvegardées.');
	}
	
	errors = new Array();
	/**
	 * save maps
	 */
	// for every maps
	if(typeof(gMap) != "undefined"){
		var maps = gMap.getAllInstances();
	    maps.forEach(function(map) {
	        save(map.id, map.getValues());
	    }); 
	}
	
	// switch to wiew mode
	swithToViewMode();
});


/***************************************************
 * 			jQuery for images editing
 **************************************************/

jQuery(".editable-img").click(function() {
	if(jQuery('#viewmode').css("display") == "none"){
		object = this;
		var idAndField = (object.id).split("_");
		var id = idAndField[0];
		var field = idAndField[1];
		
		var width = screen.width/2;
		var height = screen.height/2;
        var left = (screen.width-width)/2;
        var top = +((screen.height-height)/2);

		window.open(
		    "/backoffice/ext-finder?soloMode=true&contentId="+id+"",
		    "Médiathèque",
		    "menubar=no, status=no, scrollbars=no, top="+top+", left="+left+", width="+width+", height="+height+""
		);
	}
});

function saveImage(currentContentId, newImageId) {
	contentId = currentContentId;
	imageId = newImageId;

	if(typeof(cache[object.id]) == "undefined"){
		cache[object.id] = { "html" : jQuery(object).html(), "newImage" :imageId };
	} else {
		cache[object.id]["newImage"] = imageId;
	}
	
	jQuery("#"+object.id+" > img").attr("src", "/dam?media-id="+imageId);
	
	object = null;
	contentId = "";
	imageId = "";
}
/**************************************************/

/**************************************************
 * 			jQuery for date editing
 *************************************************/

jQuery(".date").click( function () {
	var currentDatePicker = jQuery(this).parent().context.id;
	jQuery("#"+currentDatePicker+" .datepicker").datepicker({
			regional: jQuery("body").attr("data-language"),
			dateFormat : "d MM yy",
			onSelect : function(date) {
				// Divided by 1000 to correspond with php format
				var serverDate = jQuery.datepicker.formatDate('@', jQuery("#"+currentDatePicker+" .datepicker").datepicker("getDate"))/1000;
				
				var html = jQuery("#"+currentDatePicker+" .datepicker").parent().html().split("<");
				var cachedHtml = html[0];
				
				if(typeof(cache[jQuery("#"+currentDatePicker+" .datepicker").parent().attr("id")]) == "undefined"){
					dateCache[jQuery("#"+currentDatePicker+" .datepicker").parent().attr("id")] = {"html" : cachedHtml, "newDate" : serverDate};
				} else {
					dateCache[jQuery("#"+currentDatePicker+" .datepicker").parent().attr("id")]["newDate"] = serverDate;
				}
				
				jQuery(this).parent().html("Le " + date + " <div class=\"datepicker\"></div>");
				
				jQuery("#"+currentDatePicker+" .datepicker").datepicker("destroy");
			}
		}
	);
});

/*************************************************/

/*************************************************
 * 			jQuery for time editing
 ************************************************/

jQuery(".time").click( function () {
	var currentTimePicker = jQuery(this).parent().context.id;
	var currentTime = "";
	var olderTime= "";
	var houresAreSet = false;
	
	jQuery("#"+currentTimePicker+" .timepicker").timepicker({
		regional: jQuery("body").attr("data-language"),
		showPeriodLabels: false,
		minutes: { interval: 15 },
		minuteText: 'Min',
		timeSeparator: ':',
		onSelect: function(time) {
			var html = jQuery("#"+currentTimePicker+" .currentTime").html();
			
			olderTime = html.trim();
			currentTime = time;
			
			var fullCurrentTime = currentTime.split(":");
			var currentHoures = fullCurrentTime[0];
			var currentMinutes = fullCurrentTime[1];
			
			var fullOlderTime = olderTime.split(":");
			var olderHoures = fullOlderTime[0];
			var olderMinutes = fullOlderTime[1];
			
			if(currentTime != olderTime) {
				jQuery("#"+currentTimePicker+" .currentTime").html(currentTime);
				
				if(currentHoures != olderHoures) {
					houresAreSet = true;
				}

				if(currentMinutes != olderMinutes) {
					if(typeof(timeCache[currentTimePicker]) == "undefined"){
						timeCache[currentTimePicker] = {time : jQuery("#"+currentTimePicker).attr("data-time"), newTime : currentTime};
					} else {
						timeCache[currentTimePicker]['newTime'] = currentTime;
					}
					
					jQuery("#"+currentTimePicker+" .timepicker").timepicker("destroy");
				}
			} else if(currentMinutes == olderMinutes && currentHoures == olderHoures && houresAreSet){
				if(typeof(timeCache[currentTimePicker]) == "undefined"){
					timeCache[currentTimePicker] = {time : jQuery("#"+currentTimePicker).attr("data-time"), newTime : currentTime};
				} else {
					timeCache[currentTimePicker]['newTime'] = currentTime;
				}
				
				jQuery("#"+currentTimePicker+" .timepicker").timepicker("destroy");
			}
		}
	});

	currentTime = jQuery("#"+currentTimePicker+" .currentTime").html().trim();
	jQuery("#"+currentTimePicker+" .timepicker").timepicker('setTime', currentTime);
});
/*************************************************/

/*************************************************
 * 			jQuery for rating editing
 ************************************************/

jQuery(".star-edit").click( function () {
	var rate=jQuery(this).parent();
	var rateId = jQuery(this).parent().attr("id");
	var newRate=jQuery(rate).attr("data-rate");
		
		ratingCache[rateId] = newRate;
	
	});


/************************************************/

/************************************************
 * 			jQuery for number editing
 ***********************************************/

jQuery(".number").click( function() {
	if(jQuery('#viewmode').css("display") == "none"){
		var currentNumberDiv = jQuery(this).parent().context.id;
		var currentNumber = jQuery("#"+currentNumberDiv+" > .currentNumber").html().trim();
		
		if(jQuery("#"+currentNumberDiv+" > .currentNumber").html() != "") {
			jQuery("#"+currentNumberDiv+" > .currentNumber").html("");
		
			jQuery("#"+currentNumberDiv).html(jQuery("#"+currentNumberDiv).html() + "<input class=\"numberSelector\" type=\"number\" value=\""+currentNumber+"\">");
		}
	}
});

jQuery( document ).on( 'blur', '.numberSelector', function () {
	var currentNumberDiv = jQuery(this).parent().context.parentNode.id;
	var newNumber = jQuery(this).val();
	
	if(newNumber != jQuery(this).attr("value")){
		if(typeof(numberCache[currentNumberDiv]) == "undefined"){
			numberCache[currentNumberDiv] = {"number" : jQuery(this).attr("value"), "newNumber" : newNumber};
		} else {
			numberCache[currentNumberDiv]["newNumber"] = newNumber;
		}
	}
	
	jQuery("#"+currentNumberDiv + " > .currentNumber").html(newNumber);
	jQuery("#"+currentNumberDiv + " .numberSelector").remove();
});


/***********************************************/

jQuery('.block').mouseover(function() {
	//jQuery(this).css('cursor', 'pointer');
	var position = jQuery(this).offset();
	jQuery('#blockToolBar').css(position);
	jQuery('#blockToolBar').show();
});

function swithToEditMode() {
	jQuery('.editable').attr('contenteditable', 'true');
	jQuery('.editable, .editable-img, .date, .time, .number').css('cursor', 'text');
	CKEDITOR.inlineAll();
	jQuery('#viewmode').hide();
	jQuery('#editmode').show();
	jQuery("#list-editmode").show();
	jQuery(".list-editmode").show();
	
	jQuery('.date').each(function() {
		jQuery(this).html(jQuery(this).html() + "<div class=\"datepicker\"></div>");
	});
	
	jQuery('.time').each(function() {
		jQuery(this).html(jQuery(this).html() + "<div class=\"timepicker\"></div>");
	});
	jQuery('.checkbox-edit').each(function() {
		jQuery(this).find("input").removeAttr("disabled");
	});
	jQuery('.checkbox-edit').each(function() {
		jQuery(this).find("input").removeAttr("disabled");
	});
	jQuery('.radiogroup-edit').each(function() {
		jQuery(this).find("input").removeAttr("disabled");
	});
	 starEdit=true;
	 EditMode=true;
}

function swithToViewMode() {
	jQuery('.editable, .editable-img, .date, .time, .number').css('cursor', 'default');
	for ( var i in CKEDITOR.instances) {
		CKEDITOR.instances[i].destroy(true);
	}
	jQuery('.editable').attr('contenteditable', 'false');
	jQuery('#viewmode').show();
	jQuery('#editmode').hide();
	jQuery("#list-editmode").hide();
	jQuery(".list-editmode").hide();
	jQuery('.checkbox-edit').each(function() {
		jQuery(this).find("input").attr("disabled","diabled");
	});
	jQuery('.checkbox-edit').each(function() {
		jQuery(this).find("input").attr("disabled","diabled");
	});
	jQuery('.radiogroup-edit').each(function() {
		jQuery(this).find("input").attr("disabled","diabled");
	});
	jQuery(".datepicker").remove();
	jQuery(".timepicker").remove();
	EditMode=false;
}

function checkIfDirty() {
	var changed = false;
	for ( var i in CKEDITOR.instances) {
		if (CKEDITOR.instances[i].checkDirty()) {
			changed = true;
		}
	}
	return changed;
}

function undoAllChanges() {
	for ( var i in CKEDITOR.instances) {
		if(CKEDITOR.instances[i].checkDirty()){
			undo(CKEDITOR.instances[i]);
		}
	}
	
	/**
	 * Undo modifications on images
	 */
	for(var i in cache) {
		jQuery("#"+i+"").html(cache[i]['html']);
	}
	
	/**
	 * Undo modifications on dates
	 */
	for(var contentId in dateCache) {
		jQuery("#"+contentId).html(dateCache[contentId]['html']);
	}
	
	/**
	 * Undo modifications on times
	 */
	for(var contentId in timeCache) {
		jQuery("#"+contentId+" .currentTime").html(timeCache[contentId]['time']);
	}
	
	/**
	 * Undo modifications on numbers
	 */
	for(var contentId in numberCache) {
		jQuery("#"+contentId+" .currentNumber").html(numberCache[contentId]['number']);
	}
	
	cache = new Array();
	dateCache = new Array();
	timeCache = new Array();
	numberCache = new Array();
}

function undo(editor) {
	editor.execCommand('undo');
}

function save(id, data) {
	jQuery.ajax({
	type : 'POST',
	url : "/xhr-edit",
	data : {
	'id' : id,
	'data' : data
	},
	"error" : function(jqXHR, textStatus, errorThrown) {
		var response = jqXHR.responseText;
		var responseObject = jQuery.parseJSON(response);
		var returnMsg = responseObject.msg;
		if (returnMsg == "Content already have a draft version") {
			returnMsg = 'Un brouillon empêche les modifications.';
		}
		errors.push(returnMsg);
	}
	});
}

function notify(notify_type, msg) {

	var alerts = jQuery('#alerts');
	alerts.append('<div id="alert"></div>');
	var alert = jQuery('#alert');
	alert.append('<a class="close" data-dismiss="alert" href="#">&times;</a>');
	alert.append(msg);

	if (notify_type == 'success') {

		alert.addClass('alert alert-success').fadeIn('fast');
	}
	if (notify_type == 'failure') {
		alert.addClass('alert alert-error').fadeIn('fast');
	}
}
function addContent(type,typeId,queryId){
	var siteUrl = getDomainName();
	/**
	 * Check query type
	 */
	if(type=="manual")
		{
		/*
		 * Set Css and Html for contentModal
		 */
		jQuery("#contentLabel").empty().html("Selectionnez le type de contenu à ajouter");
		jQuery("#contentBody").empty();
	    jQuery("#contentModal").css({
	    	"width":"auto",
	    	"height":"auto",
	    	"margin-left":"-10%",
	    	"margin-top":"auto"
	    });
		jQuery("#contentBody").append("<form name='modalfrom' id='modal-form'><select id='select-type-box'></select><div style='clear:both;'><button type='submit' id='btn-valid-form' class='btn pull-right'>Valider</button></div></form>")
		jQuery.ajax({
			"url" : "/backoffice/content-types/get-readable-content-types/",
			"async" : false,
			"type" : "GET",
			"dataType" : "json",
			"success" : function(msg) {
				for ( var i in msg) {
					jQuery("<option value='" + msg[i].id + "'>" + msg[i].type + "</option>").appendTo("#select-type-box");
				}
			}
			});
		/**
		 *Get type of content to add and call iframe
		 */
		jQuery('#btn-valid-form').click(function() {
			selectedTypeId = jQuery("#select-type-box").val();
			
		var modalUrl = "http://" + siteUrl + "/backoffice/content-contributor?typeId=" + selectedTypeId + "&queryId=" + queryId + "&current-page=" + jQuery('body').attr('data-current-page') + "&current-workspace=" + jQuery('body').attr('data-current-workspace');
			var iWidth=screen.availWidth*(90/100);
			var iHeight=screen.availHeight*(90/100);
			var availHeight=window.innerHeight*(90/100);
			var iFrameHeight=availHeight*(90/100);
			jQuery("#contentLabel").empty().html("Ajout de contenu");
		    jQuery("#contentBody").empty().html("<iframe  style='width:99%; height:"+(iFrameHeight)+"px; border:none;' src='" + modalUrl + "'></iframe>");
		    jQuery("#contentModal").css({
		    	"width":iWidth+"px",
		    	"height":iHeight+"px",
		    	"margin-left":-(iWidth/2)+"px",
		    	"margin-top":-((screen.availHeight-iHeight)/2)+"px"
		    });
		});
		}else{
			var modalUrl = "http://" + siteUrl + "/backoffice/content-contributor?typeId=" + typeId + "&queryId=" + queryId + "&current-page=" + jQuery('body').attr('data-current-page') + "&current-workspace=" + jQuery('body').attr('data-current-workspace');
			var iWidth=screen.availWidth*(90/100);
			var iHeight=screen.availHeight*(90/100);
			var availHeight=window.innerHeight*(90/100);
			var iFrameHeight=availHeight*(90/100);
			jQuery("#contentLabel").empty().html("Ajout de contenu");
		    jQuery("#contentBody").empty().html("<iframe style='width:99%; height:"+(iFrameHeight)+"px; border:none;' src='" + modalUrl + "'></iframe>");
		    jQuery("#contentModal").css({
		    	"width":iWidth+"px",
		    	"height":iHeight+"px",
		    	"margin-left":-(iWidth/2)+"px",
		    	"margin-top":-((screen.availHeight-iHeight)/2)+"px"
		    });
		   
		}
	jQuery("#contentModal").modal("show");	
}
function getDomainName() {
	return window.location.href.substr(7).substr(0, window.location.href.substr(7).indexOf("/"));
}
function destroyModal(){
	jQuery("#contentModal").modal("hide");	
}

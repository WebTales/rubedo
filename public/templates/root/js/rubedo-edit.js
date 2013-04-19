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

			// Make toolbar
			editor.config.toolbarGroups = [{
					name : 'clipboard',
					groups : [ 'clipboard' ]
				}, {
					name : 'undo'
				} 
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
	}
});

jQuery('#cancel-confirm').click(function() {
	undoAllChanges();
	swithToViewMode();
});

jQuery('#btn-save').click(function() {
	var modified = false;
	
	// for every modified content
	for ( var i in CKEDITOR.instances) {
		if (CKEDITOR.instances[i].checkDirty()) {
			modified = true;
			// saving content
			save(CKEDITOR.instances[i].element.getId(), CKEDITOR.instances[i].getData());
			
			//Remove dirty flag
			CKEDITOR.instances[i].resetDirty();
		}
	}
	
	/**
	 * Save images
	 */
	for( var i in cache ) {
		modified = true;
		var idAndField = i.split("_");
		var id = idAndField[0];
		var field = idAndField[1];
		
		confirmImage(id, cache[i].newImage, field);
	}
	
	/**
	 * Save dates
	 */
	for( var contentId in dateCache) {
		modified = true;
		confirmDate(contentId, dateCache[contentId].newDate);
	}
	
	/**
	 * save times
	 */
	for( var contentId in timeCache) {
		modified = true;
		confirmTime(contentId, timeCache[contentId].newTime);
	}
	
	/**
	 * save numbers
	 */
	for( var contentId in numberCache) {
		modified = true;
		confirmNumber(contentId, numberCache[contentId].newNumber);
	}
	
	if(errors.length > 0) {
		notify('failure', 'Une erreur est survenue, il est possible que vos modifications soient perdues');
	} else if (modified == true){
		notify('success', 'Les données ont été sauvegardées.');
	}
	
	errors = new Array();
	
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

function confirmImage(content, image, field) {
	var request = $.ajax({
		url: "/xhr-edit/save-image",
		type: "POST",
		data: {
			contentId : content,
			newImageId : image,
			field : field
		},
		dataType: "json"
	});
	 
	request.fail(function(jqXHR, textStatus) {
		errors.push(jQuery.parseJSON(jqXHR['responseText']));
	});
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

function confirmDate(id, date) {
	var idAndField = id.split("_");
	var contentId = idAndField[0];
	var fieldName = idAndField[1];
	
	var request = $.ajax({
		url: "/xhr-edit/save-date",
		type: "POST",
		data: {
			contentId : contentId,
			newDate : date,
			field : fieldName
		},
		dataType: "json"
	});
	 
	request.fail(function(jqXHR, textStatus) {
		errors.push(jQuery.parseJSON(jqXHR['responseText']));
	});
}

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

function confirmTime(contentId, newTime) {
	var idAndField = contentId.split("_");
	var contentId = idAndField[0];
	var fieldName = idAndField[1];
	
	var request = $.ajax({
		url: "/xhr-edit/save-time",
		type: "POST",
		data: {
			contentId : contentId,
			newTime : newTime,
			field : fieldName
		},
		dataType: "json"
	});
	 
	request.fail(function(jqXHR, textStatus) {
		errors.push(jQuery.parseJSON(jqXHR['responseText']));
	});
}

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

$( document ).on( 'blur', '.numberSelector', function () {
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

function confirmNumber(contentId, newNumber) {
	var idAndField = contentId.split("_");
	var contentId = idAndField[0];
	var fieldName = idAndField[1];
	
	var request = $.ajax({
		url: "/xhr-edit/save-number",
		type: "POST",
		data: {
			contentId : contentId,
			newNumber : newNumber,
			field : fieldName
		},
		dataType: "json"
	});
	 
	request.fail(function(jqXHR, textStatus) {
		errors.push(jQuery.parseJSON(jqXHR['responseText']));
	});
}

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
	
	jQuery(".datepicker").remove();
	jQuery(".timepicker").remove();
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
function modal(header, body, modalId, modalWidth, modalHeight) {
	var top = (100 - modalHeight) / 2;
	var left = (100 - modalWidth) / 2;
	var stringModalId = "#" + modalId;
	jQuery("<div id='" + modalId + "' class='modal hide fade'><div class='modal-header'> <a href='#' onclick='destroyModal(" + '"' + modalId + '"' + ")' class='close'>&times;</a>" + header + "</div><div id='modal-body-content' class='modal-body'>" + body + "</div></div>").appendTo(document.body);
	jQuery(".modal").css({
		"margin" : "0 0 0 0"
	});

	jQuery("#" + modalId).css({
	"width" : modalWidth + "%",
	"min-height" : modalHeight + "%",
	"max-height" : '90%',
	"top" : top + "%",
	"left" : left + "%",
	"overflow" : 'scroll'
	});

	jQuery("#" + modalId + " iframe").css({
	"width" : "99.9%",
	"height" : "90%",
	"border" : "none"
	});
	jQuery(".modal-header").css({
		"min-height" : "2.5%"
	});
	jQuery("#modal-body-content").css({
	"max-height" : jQuery("#add-content-window").height(),
	"height" : jQuery("#add-content-window").height()
	});
}

function createContentWindow(type, typeId, queryId) {
	var selectedTypeId = typeId;
	var siteUrl = getDomainName();
	if (type == "manual") {
		modal("<h3>Selectionnez le type de contenu à ajouter</h3>", "<form name='modalfrom' id='modal-form'><select id='select-type-box'></select></form>", "select-type-window", 30, 30);
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
		jQuery("<div class='form-actions'><a class='btn btn-primary' id='btn-valid-form' >Continue</a></div>").appendTo("#modal-form");
		jQuery("#select-type-window").modal('show');
		jQuery('#btn-valid-form').click(function() {
			selectedTypeId = jQuery("#select-type-box").val();
			destroyModal("select-type-window");
			var modalUrl = "http://" + siteUrl + "/backoffice/content-contributor?typeId=" + selectedTypeId + "&queryId=" + queryId + "&current-page=" + jQuery('body').attr('data-current-page') + "&current-workspace=" + jQuery('body').attr('data-current-workspace');
			modal("", "<iframe src='" + modalUrl + "'></iframe>", "add-content-window", 90, 90);

			jQuery("#add-content-window").modal('show');
		});

	}
	if (type == "simple") {

		var modalUrl = "http://" + siteUrl + "/backoffice/content-contributor?typeId=" + typeId + "&queryId=" + queryId + "&current-page=" + jQuery('body').attr('data-current-page') + "&current-workspace=" + jQuery('body').attr('data-current-workspace');
		modal("", "<iframe src='" + modalUrl + "'></iframe>", "add-content-window", 90, 90);
		jQuery("#add-content-window").modal('show');
	}
}
function destroyModal(modalId) {
	jQuery("#" + modalId).remove();
	jQuery(".modal-backdrop.fade.in").remove();
}
function getDomainName() {
	return window.location.href.substr(7).substr(0, window.location.href.substr(7).indexOf("/"));
}
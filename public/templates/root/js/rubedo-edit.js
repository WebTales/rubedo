/******************************
 * 		Global variables 
 *****************************/
var contentId = "";
var imageId = "";
var cache = new Array();
var dateCache = new Array();
var object = null;
var errors = new Array();
var asyncIncrementor= 0;
var timeCache = new Array();
var numberCache = new Array();
var checkboxCache = new Array();
var checkboxgroupCache = new Array();
var radiogroupCache = new Array();
var starEdit=false;
var EditMode=true;
var ratingCache=new Array();
/*****************************/

//Initialize cursor to default
jQuery("body").css("cursor" , "default");

jQuery('#contentToolBar').css('top', '0');
jQuery('#contentToolBar').show();

//Make CKEditor object
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

/**
 * JS for the "switch to edit mode" button in the administration toolbar
 */
jQuery('#btn-edit').click(function() {
	swithToEditMode();
	starEdit=true;
});

/**
 * JS for "cancel modifications" button when you are in editing mode
 */
jQuery('#btn-cancel').click(function() {
	var changed = checkIfDirty();
	var cacheChanged = 0;
	var dateCacheChanged = 0;
	var timeCacheChanged = 0;
	var checkboxCacheChanged = 0;
	var checkboxgroupCacheChanged = 0;
	var radiogroupCacheChanged = 0;
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
	 *  Count modifications on checkboxes
	 */
	for(var contentId in checkboxCache) {
		checkboxCacheChanged++;
	}
	/**
	 *  Count modifications on checkbox groups
	 */
	for(var contentId in checkboxgroupCache) {
		checkboxgroupCacheChanged++;
	}
	/**
	 *  Count modifications on radio groups
	 */
	for(var contentId in radiogroupCache) {
		radiogroupCacheChanged++;
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
	
	/**
	 * Open confirmation modal if there is some modifications
	 */
	if (changed || cacheChanged > 0 || dateCacheChanged > 0 || timeCacheChanged > 0 || numberCacheChanged > 0 || checkboxCacheChanged > 0 || checkboxgroupCacheChanged > 0 || radiogroupCacheChanged > 0) {
		jQuery('#confirm').modal();
	} else {
		swithToViewMode();
		location.reload();
	}
});


/**
 * JS for the "cancel confirmation" button in the modal when you don't whant to discard your modifications
 */
jQuery('#cancel-confirm').click(function() {
	//undoAllChanges();
	//swithToViewMode();
	location.reload();
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

/**
 * JS for "confirm modifications" button in the modal
 */
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
	 * Save checkboxes
	 */
	for( var contentId in checkboxCache) {
		modified = true;
		save(contentId, checkboxCache[contentId])
		}
	/**
	 * Save checkbox groups
	 */
	for( var contentId in checkboxgroupCache) {
		modified = true;
		save(contentId, checkboxgroupCache[contentId])
		}
	/**
	 * Save radio groups
	 */
	for( var contentId in radiogroupCache) {
		modified = true;
		save(contentId, radiogroupCache[contentId])
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

/*************************************************/

/*************************************************
 * 			jQuery for checkbox editing
 ************************************************/

jQuery(".checkbox-edit").click( function () {
	if(!jQuery(this).find("input").is(":disabled")){
		var newValue=jQuery(this).find("input").is(":checked");
		var checkboxId=jQuery(this).attr("id");
		checkboxCache[checkboxId]=newValue;
	}
	
	});
/*************************************************/

/*************************************************
 * 			jQuery for radiogroup editing
 ************************************************/

jQuery(".radiogroup-edit").click( function () {
	if(!jQuery(this).find("input").is(":disabled")){
		var radioGroupId=jQuery(this).attr("id");
		var newValue={ };
		jQuery(this).find("input").each(function(b,a){
			if(jQuery(this).is(":checked")){
				newValue[jQuery(this).attr("name")]=jQuery(this).attr("value");
			}
		});
		radiogroupCache[radioGroupId]=newValue;
	}
	
	});
/*************************************************/

/*************************************************
 * 			jQuery for checkboxgroup editing
 ************************************************/

jQuery(".checkboxgroup-edit").click( function () {
	if(!jQuery(this).find("input").is(":disabled")){
		var checkboxGroupId=jQuery(this).attr("id");
		var newValue={ };
		newValue[jQuery(this).find("input").attr("name")]=new Array();
		jQuery(this).find("input").each(function(b,a){
			if(jQuery(this).is(":checked")){
				newValue[jQuery(this).attr("name")].push(jQuery(this).attr("value"));
			}
		});
		
		checkboxgroupCache[checkboxGroupId]=newValue;
	}
	
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

/**
 * Allow to activate editing mode
 */
function swithToEditMode() {
	jQuery('#alerts').html("");
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
	jQuery('.checkboxgroupel-edit').each(function() {
		jQuery(this).find("input").removeAttr("disabled");
	});
	jQuery('.radiogroup-edit').each(function() {
		jQuery(this).find("input").removeAttr("disabled");
	});
	 starEdit=true;
	 EditMode=true;
}

/**
 * Allow to activate the standard mode
 */
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
	jQuery('.checkboxgroupel-edit').each(function() {
		jQuery(this).find("input").attr("disabled","diabled");
	});
	jQuery('.radiogroup-edit').each(function() {
		jQuery(this).find("input").attr("disabled","diabled");
	});
	jQuery(".datepicker").remove();
	jQuery(".timepicker").remove();
	EditMode=false;
}

/**
 * Chack if CKEFields had been modified
 */
function checkIfDirty() {
	var changed = false;
	for ( var i in CKEDITOR.instances) {
		if (CKEDITOR.instances[i].checkDirty()) {
			changed = true;
		}
	}
	return changed;
}

/**
 * Deprecated, now we reload the page
 * 
 * @deprecated
 */
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
	checkboxCache=new Array();
	checkboxgroupCache=new Array();
	radiogroupCache=new Array();
}

/**
 * Deprecated too
 * 
 * @deprecated
 */
function undo(editor) {
	editor.execCommand('undo');
}

/**
 * Save modifications on fields
 * 
 * @param id contain the content id and the concerned field (id_fieldName)
 * @param data contain the new value of the field
 */
function save(id, data) {
	asyncIncrementor=asyncIncrementor+1;
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
			asyncIncrementor=asyncIncrementor-1;
			if (asyncIncrementor==0){
				afterAllSavesAreDone();
			}
		},
		"success":function(data){
			asyncIncrementor=asyncIncrementor-1;
			if (data.success===false){
				errors.push(data.msg);
			}
			if (asyncIncrementor==0){
				afterAllSavesAreDone();
			}
		}
	});
}

/**
 * Wait until execution of queries and show notification
 */
function afterAllSavesAreDone(){
	if(errors.length > 0) {
		notify('failure', 'Une erreur est survenue, il est possible que vos modifications soient perdues');
	} else {
		notify('success', 'Les données ont été sauvegardées.');
	}
	errors = new Array();
	asyncIncrementor= 0;
}

/**
 * Allow to shox notifications in the admin toolbar
 * 
 * @param notify_type 
 * 			Must contain "failure" for an error notification
 * 			Must contain "success" for a success notification
 * @param msg contain the message to display in the notification
 */
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

/**
 * Allow to create a new content in a content list
 */
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
		jQuery("#select-type-box").empty();
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
		jQuery('#btn-cancel-ctselect-form').click(function() {
			jQuery("#contentTypeSelectModal").modal("hide");
			jQuery('#btn-valid-form').unbind();
			jQuery('#btn-cancel-ctselect-form').unbind();
		});
		
		jQuery('#btn-valid-form').click(function() {
			selectedTypeId = jQuery("#select-type-box").val();
			var modalUrl = "http://" + siteUrl + "/backoffice/content-contributor?typeId=" + selectedTypeId + "&queryId=" + queryId + "&current-page=" + jQuery('body').attr('data-current-page') + "&current-workspace=" + jQuery('body').attr('data-current-workspace');
			var availWidth=window.innerWidth*(90/100);
			var properWidth=Math.min(1000,availWidth);
		    jQuery("#contentBody").empty().html("<iframe style='width:100%;  height:80%; border:none;' src='" + modalUrl + "'></iframe>");
		    jQuery("#contentModal").attr("data-width",properWidth);
		    jQuery("#contentModal").modal("show");
		    jQuery("#contentModal").modal("loading");
		    jQuery("#contentTypeSelectModal").modal("hide");
		    jQuery('#btn-valid-form').unbind();
		    jQuery('#btn-cancel-ctselect-form').unbind();
		});
		
		jQuery("#contentTypeSelectModal").modal("show");
		}else{
			var modalUrl = "http://" + siteUrl + "/backoffice/content-contributor?typeId=" + typeId + "&queryId=" + queryId + "&current-page=" + jQuery('body').attr('data-current-page') + "&current-workspace=" + jQuery('body').attr('data-current-workspace');
			var availWidth=window.innerWidth*(90/100);
			var properWidth=Math.min(1000,availWidth);
		    jQuery("#contentBody").empty().html("<iframe style='width:100%;  height:80%; border:none;' src='" + modalUrl + "'></iframe>");
		    jQuery("#contentModal").attr("data-width",properWidth);
		    jQuery("#contentModal").modal("show");
		    jQuery("#contentModal").modal("loading");
		}
	
		
}

/**
 * return the domain name
 */
function getDomainName() {
	return window.location.href.substr(7).substr(0, window.location.href.substr(7).indexOf("/"));
}

/**
 * Close the modal if you don't need it anymore
 */
function destroyModal(){
	jQuery("#contentBody").empty();
	jQuery("#contentModal").modal("hide");	
	window.location.reload();
}

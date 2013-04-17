/******************************
 * 		Global variables
 *****************************/
var contentId = "";
var imageId = "";
var cache = new Array();
var object = null;
var errors = new Array();
/*****************************/

jQuery('#contentToolBar').css('top', '0');
jQuery('#contentToolBar').show();

CKEDITOR.on('instanceCreated', function(event) {
	var editor = event.editor, element = editor.element;
	editor.config.entities = false;
	editor.config.entities_latin = false;
	editor.config.language = jQuery("body").attr("data-language");
	
	// Customize CKEditor
	if (element.getAttribute("data-field-type") =="title" || element.getAttribute("data-field-type") =="text" || element.getAttribute("data-field-type") =="text-area") {
		
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
	
	for(var i in cache){
		cacheChanged++;
	}
	
	if (changed || cacheChanged > 0) {
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
	// for every modified content
	for ( var i in CKEDITOR.instances) {
		if (CKEDITOR.instances[i].checkDirty()) {
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
		var idAndField = i.split("_");
		var id = idAndField[0];
		var field = idAndField[1];
		
		confirmImage(id, cache[i].newImage, field);
	}
	
	if(errors.length > 0) {
		notify('failure', 'Une erreur est survenue, il est possible que vos modifications soient perdues');
	} else {
		notify('success', 'Les données ont été sauvegardées.');
	}
	
	errors = new Array();
	
	// for every maps
	//var maps = gMap.getAllInstances();
	//maps.forEach(function(map) {
	//	save(map.id, JSON.stringify(map.getValues()));
	//});
	
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

jQuery('.block').mouseover(function() {
	jQuery(this).css('cursor', 'pointer');
	var position = jQuery(this).offset();
	jQuery('#blockToolBar').css(position);
	jQuery('#blockToolBar').show();
});

function swithToEditMode() {
	jQuery('.editable').attr('contenteditable', 'true');
	CKEDITOR.inlineAll();
	jQuery('#viewmode').hide();
	jQuery('#editmode').show();
	jQuery("#list-editmode").show();
	jQuery(".list-editmode").show();
}

function swithToViewMode() {
	for ( var i in CKEDITOR.instances) {
		CKEDITOR.instances[i].destroy(true);
	}
	jQuery('.editable').attr('contenteditable', 'false');
	jQuery('#viewmode').show();
	jQuery('#editmode').hide();
	jQuery("#list-editmode").hide();
	jQuery(".list-editmode").hide();
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
	
	for(var i in cache) {
		jQuery("#"+i+"").html(cache[i]['html']);
	}
	
	cache = new Array();
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
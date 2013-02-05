
jQuery('#contentToolBar').css('top','0');
jQuery('#contentToolBar').show();


CKEDITOR.on( 'instanceCreated', function( event ) {
	var editor = event.editor,element = editor.element;
	editor.config.entities = false;
	editor.config.entities_latin = false;

	// Customize editors for headers and tag list.
	// These editors don't need features like smileys, templates, iframes etc.
	if ( element.is( 'h1', 'h2', 'h3' ) || element.getAttribute( 'id' ) == 'taglist' ) {
		// Customize the editor configurations on "configLoaded" event,
		// which is fired after the configuration file loading and
		// execution. This makes it possible to change the
		// configurations before the editor initialization takes place.
		editor.on( 'configLoaded', function() {

			editor.config.language = '{{ lang }}';
			//editor.config.extraPlugins ='stylesheetparser,rubedosave,rubedodiscard,rubedoelementspath';

			// Remove unnecessary plugins to make the editor simpler.
			editor.config.removePlugins = 'colorbutton,find,flash,font,' +
				'forms,iframe,image,newpage,removeformat,scayt,' +
				'smiley,specialchar,stylescombo,templates,wsc';

			// Rearrange the layout of the toolbar.
			editor.config.toolbarGroups = [
				{ name: 'clipboard',	groups: ['clipboard' ] },
				{ name: 'editing',		groups: [ 'basicstyles', 'links' ] },
				{ name: 'undo' }
			];
		});
	} else {
		editor.on( 'configLoaded', function() {
		// set file and media explorer path
		editor.config.filebrowserBrowseUrl = '/backoffice/resources/extFinder/app.html?CKEditor=CKEField-1132-inputEl&CKEditorFuncNum=2&langCode=fr';
		editor.config.filebrowserUploadUrl = '/backoffice/resources/extFinder/app.html?CKEditor=CKEField-1132-inputEl&CKEditorFuncNum=2&langCode=fr';
		});		
	}
});

jQuery('#btn-edit').click(function (){
	swithToEditMode();
});

jQuery('#btn-cancel').click(function (){
	var changed = checkIfDirty();
	if (changed) {
		jQuery('#confirm').modal();
	} else {
		swithToViewMode();
	}
});

jQuery('#cancel-confirm').click(function() {
	undoAllChanges();
	swithToViewMode();
});

jQuery('#btn-save').click(function (){
	// for every modified content
	for(var i in CKEDITOR.instances) {
		if (CKEDITOR.instances[i].checkDirty()) {
			// saving content
			save(CKEDITOR.instances[i].element.getId(),CKEDITOR.instances[i].getData());
		}
	}	
	// switch to wiew mode
	swithToViewMode();
});

jQuery('.block').mouseover(function() {
	jQuery(this).css('cursor','pointer');
	var position = jQuery(this).offset();
	jQuery('#blockToolBar').css(position);
	jQuery('#blockToolBar').show();
});

function swithToEditMode() {
	jQuery('.editable').attr('contenteditable','true');
	CKEDITOR.inlineAll();
	jQuery('#viewmode').hide();
	jQuery('#editmode').show();
	jQuery("#list-editmode").show();
}

function swithToViewMode() {
	for(var i in CKEDITOR.instances) {
		CKEDITOR.instances[i].destroy(true);
	} 
	jQuery('.editable').attr('contenteditable','false');
	jQuery('#viewmode').show();
	jQuery('#editmode').hide();
		jQuery("#list-editmode").hide();
}

function checkIfDirty() {
	var changed = false;
	for(var i in CKEDITOR.instances) {
		if (CKEDITOR.instances[i].checkDirty()) {
			changed = true;
		}
	}
	return changed;
}

function undoAllChanges() {
	for(var i in CKEDITOR.instances) {
		undo(CKEDITOR.instances[i]);
	}
}

function undo(editor) {
	if (editor.checkDirty()) {
		editor.execCommand('undo');
		undo(editor);
	}
}

function save(id, data) {
	jQuery.ajax({
		type : 'POST',
		url : "/xhr-edit",
		data : {
			'id' : id,
			'data' : data
		},
		"success": function (data, textStatus, jqXHR) {
			notify('success', 'Les données ont été sauvegardées.');
	    },
	    "error": function (jqXHR, textStatus, errorThrown) {
	    	var response = jqXHR.responseText;
	    	var responseObject = jQuery.parseJSON(response);
	    	var returnMsg = responseObject.msg;
	    	if(returnMsg == "Content already have a draft version"){
	    		returnMsg = 'Un brouillon empêche les modifications.';
	    	}
	    	notify('failure', returnMsg);
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
function modal(header,body,modalId,modalWidth,modalHeight)
{
	var top=(100-modalHeight)/2;
	var left=(100-modalWidth)/2;
var stringModalId="#"+modalId;
jQuery("<div id='"+modalId+"' class='modal hide fade'><div class='modal-header'> <a href='#' onclick='destroyModal("+'"'+modalId+'"'+")' class='close'>&times;</a>"+header+"</div><div id='modal-body-content' class='modal-body'>"+body+"</div></div>").appendTo(document.body);
jQuery(".modal").css({"margin":"0 0 0 0"});
jQuery("#"+modalId).css({
			"width":modalWidth+"%",
			"height":modalHeight+"%",
			"top":top+"%",
			"left":left+"%"
		});

jQuery("#"+modalId+" iframe").css({
			"width":"99.9%",
			"height":"90%",
			"border":"none"
		});
jQuery(".modal-header").css({"min-height":"2.5%"});
	jQuery("#modal-body-content").css({
			"max-height":jQuery("#add-content-window").width()
		});
}
function createContentWindow(type,typeId,queryId)
{
	var selectedTypeId=typeId;
	var siteUrl=getDomainName();
		if(type=="manual")
		{
			modal("<h3>Selectionnez le type de contenu à ajouter</h3>","<form name='modalfrom' id='modal-form'><select id='select-type-box'></select></form>","select-type-window",30,25);
			jQuery.ajax({
			"url":"/backoffice/content-types/get-readable-content-types/",
			"async":false,
			"type":"GET",
			"dataType":"json",
			"success":function(msg)
			{
				for(var i in msg )
				{
					jQuery("<option value='"+msg[i].id+"'>"+msg[i].type+"</option>").appendTo("#select-type-box");
				}
			}
		});
			jQuery("<div class='form-actions'><button type='submit' class='btn btn-primary' id='btn-valid-form' >Continue</button></div>").appendTo("#modal-form");
			jQuery("#select-type-window").modal('show');
			jQuery('#btn-valid-form').click(function(){
			selectedTypeId=jQuery("#select-type-box").val();
			destroyModal("select-type-window");
				var modalUrl="http://"+siteUrl+"/backoffice/resources/contentContributor/app.html?typeId="+selectedTypeId+"&queryId="+queryId;
				modal("","<iframe src='"+modalUrl+"'></iframe>","add-content-window",90,90);
				
				jQuery("#add-content-window").modal('show');
			});
			
		}
		if(type=="simple")
		{
			var modalUrl="http://"+siteUrl+"/backoffice/resources/contentContributor/app.html?typeId="+typeId+"&queryId="+queryId;
		modal("","<iframe src='"+modalUrl+"'></iframe>","add-content-window",90,90);
		jQuery("#add-content-window").modal('show');
		}
}
function destroyModal(modalId)
{
	jQuery("#"+modalId).remove();
	jQuery(".modal-backdrop.fade.in").remove();
}
function getDomainName()
{
	return window.location.href.substr(7).substr(0,window.location.href.substr(7).indexOf("/"));
}




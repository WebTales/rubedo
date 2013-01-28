
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
}

function swithToViewMode() {
	for(var i in CKEDITOR.instances) {
		CKEDITOR.instances[i].destroy(true);
	} 
	jQuery('.editable').attr('contenteditable','false');
	jQuery('#viewmode').show();
	jQuery('#editmode').hide();	
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
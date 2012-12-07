// navbar
//jQuery('#menu').css('margin-top','60px');

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

		// Remove unnecessary plugins to make the editor simpler.
		editor.config.removePlugins = 'colorbutton,find,forms,iframe,removeformat,scayt,smiley,specialchar,wsc';
		editor.config.extraPlugins ='stylesheetparser';
		editor.config.contentsCss = '/css/default.bootstrap.min.css';
		// Rearrange the layout of the toolbar.
		editor.config.toolbar = [
			{ name: 'document', items : [ 'RubedoSave','RubedoDiscard','NewPage','Templates','Print'] },
			{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
			{ name: 'editing', items : [ 'Find','Replace' ] },
			{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike' ] },
			{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote',
			'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'] },
			{ name: 'styles', items : [ 'Styles','Format'] },
			{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
			{ name: 'insert', items : [ 'Image','Flash','Table','Forms','HorizontalRule'] }
		];
			
		// file and media explorer
		editor.config.filebrowserBrowseUrl = '/ckfinder/ckfinder.html';
		editor.config.filebrowserImageBrowseUrl = '/ckfinder/ckfinder.html?type=Images';
		editor.config.filebrowserFlashBrowseUrl = '/ckfinder/ckfinder.html?type=Flash';
		editor.config.filebrowserUploadUrl = '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files&currentFolder=/archive/';
		editor.config.filebrowserImageUploadUrl = '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images&currentFolder=/cars/';
		editor.config.filebrowserFlashUploadUrl = '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';        
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

function save(id,data) {
	jQuery.post("/xhr-edit", { 'id': id, 'data': data},
   function(result) {
   });
}

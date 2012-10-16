/**
 * Rubedo save plugin.
 */

// Register the plugin with the editor.
// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.plugins.html
CKEDITOR.plugins.add( 'rubedosave',
{
	// The plugin initialization logic goes inside this method.
	// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.pluginDefinition.html#init
	init: function( editor )
	{
		// Define an editor command that saves the document. 
		// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.editor.html#addCommand
		editor.addCommand( 'rubedoSave',
			{
				// Define a function that will be fired when the command is executed.
				// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.commandDefinition.html#exec
				exec : function( editor )
				{    
					for(var i in CKEDITOR.instances) {
						CKEDITOR.instances[i].element.setHtml(CKEDITOR.instances[i].getData());
			    		CKEDITOR.instances[i].destroy(true);
					} 
				}
			});
		// Create a toolbar button that executes the plugin command. 
		// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.ui.html#addButton
		editor.ui.addButton( 'RubedoSave',
		{
			// Toolbar button tooltip.
			label: editor.lang.save,
			// Reference to the plugin command name.
			command: 'rubedoSave',
			// Button's icon file path.
			//icon: this.path + 'images/save.png'
			className: 'cke_button_save'
		} );
	}
} );
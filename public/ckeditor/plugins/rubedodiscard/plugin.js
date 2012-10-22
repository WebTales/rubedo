/**
 * Rubedo discard plugin.
 */

// Register the plugin with the editor.
// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.plugins.html
CKEDITOR.plugins.add( 'rubedodiscard',
{
	// The plugin initialization logic goes inside this method.
	// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.pluginDefinition.html#init
	init: function( editor )
	{
		// Define an editor command that saves the document. 
		// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.editor.html#addCommand
		editor.addCommand( 'rubedoDiscard',
			{
				// Define a function that will be fired when the command is executed.
				// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.commandDefinition.html#exec
				exec : function( editor )
				{    
					for(var i in CKEDITOR.instances) {
			    		CKEDITOR.instances[i].destroy(true);
					} 
				}
			});
		// Create a toolbar button that executes the plugin command. 
		// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.ui.html#addButton
		editor.ui.addButton( 'RubedoDiscard',
		{
			// Toolbar button tooltip.
			label: editor.lang.close,
			// Reference to the plugin command name.
			command: 'rubedoDiscard',
			// Button's icon file path.
			icon: this.path + 'images/discard.png'
			//className: 'cke_button_close'
		} );
	}
} );
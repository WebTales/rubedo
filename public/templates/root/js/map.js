jQuery(".mapPlaceholder").each(loadMap);

function loadMap()
{
	var id = jQuery(this).attr('id');
	var config=new Object();
	config.options=jQuery(this).attr("data-conf");
	config.title=jQuery(this).attr('data-title');
	config.text=jQuery(this).attr('data-value');
	if(jQuery(this).attr('data-field')){config.field=jQuery(this).attr('data-field');}else{config.field=false;}
	new gMap(config.options,id,config.title,config.text,config.field);
}
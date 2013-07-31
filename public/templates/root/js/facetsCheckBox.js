$(function(){
  $(".facetBox").change(function(){
  var item=$(this);    
  if(item.is(":checked"))
  {
       window.location = item.data("target") 
  }
  else
  {
	  window.location = item.data("target-off")  
  }        
 });
})
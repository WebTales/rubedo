var userFieldRemove =  function() {
    jQuery(this).parent().remove();
};
jQuery('.user-field-remove').one('click', userFieldRemove);

var userFieldAdd = function() {
    var ul = jQuery(this).parent().parent().find('ul');
    var i = 0;
    var lastIndex = ul.find('li:last').attr('data-index');
    if (typeof lastIndex != 'undefined') {
        i = parseInt(lastIndex) + 1;
    }
    ul.append('<li data-index="' + i + '">' + ul.attr('data-prototype').replace(/__index__/g, i) + '<i class="icon-minus user-field-remove"></i></li>');
    ul.find(':last').one('click', userFieldRemove);
    rubedoFields.safeLoadDatePicker(ul.children(':last').children('input[type="date"]'));
    rubedoFields.safeLoadRichText(ul.children(':last').children('textarea[data-richtext]'));
};

jQuery('.user-field-add').on('click', userFieldAdd);

var geocoder = new google.maps.Geocoder();
jQuery(".position-adress-field").change(function(){
    var value=jQuery(this).val();
    var parent=jQuery(this).parent();
    geocoder.geocode({
        'address' : value
    }, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            var target=results[0].geometry.location;
            var lat=target.lat();
            var lon=target.lng();
            parent.find(".position-latitude-field").val(lat);
            parent.find(".position-longitude-field").val(lon);
        } else {
            console.log("geocoding error");
        }
    });
});
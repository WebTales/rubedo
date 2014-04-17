var rubedoFields = {
    loadDatePicker: function (selector) {
        var params = { todayHighlight: true,
            format: "yyyy-mm-dd"};
        var dataLanguage = jQuery(selector).attr('data-language');
        if (typeof dataLanguage != 'undefined') {
            params.language = dataLanguage;
        }
        jQuery(selector).datepicker(params);
    },
    safeLoadDatePicker: function (selector) {
        if (jQuery(selector).attr('type') == 'date' && jQuery(selector).prop('type') == 'text')
        if (typeof jQuery.datepicker == 'undefined') {
            jQuery.when(
                    jQuery.getScript('/components/eternicode/bootstrap-datepicker/js/bootstrap-datepicker.js'),
                    jQuery('head').append('<link rel="stylesheet" type="text/css" href="/components/eternicode/bootstrap-datepicker/css/datepicker.css" />'),
                    jQuery.Deferred(function( deferred ){
                        jQuery( deferred.resolve );
                    })
                ).done(function(){
                    rubedoFields.loadDatePicker(selector);
                });
        } else {
            rubedoFields.loadDatePicker(selector);
        }
    },
    loadRichText: function (selector) {
        var id = jQuery(selector).attr('id');
        if (typeof id != 'undefined') {
            CKEDITOR.replace(id);
        }
    },
    safeLoadRichText: function (selector) {
        if (typeof CKEDITOR == 'undefined') {
            jQuery.when(
                    jQuery.getScript('/components/webtales/ckeditor/ckeditor.js'),
                    jQuery.Deferred(function( deferred ){
                        jQuery( deferred.resolve );
                    })
                ).done(function(){
                    rubedoFields.loadRichText(selector);
                });
        } else {
            rubedoFields.loadRichText(selector);
        }
    }
};
jQuery('input[type="date"]').each(function (){
    rubedoFields.safeLoadDatePicker(this);
});
jQuery('textarea[data-richtext]').each(function (){
    rubedoFields.safeLoadRichText(this);
});
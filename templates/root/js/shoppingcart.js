function addProductToCart (productId, variationId, amount) {
    var request2 = jQuery.ajax({
        url : window.location.protocol + '//'
            + window.location.host
            + '/blocks/'+jQuery('html').attr('lang')+'/shopping-cart/add-item-to-cart',
        type : "POST",
        data : {
            'current-page' : jQuery('body').attr('data-current-page'),
            'productId' : productId,
            'variationId': variationId,
            'amount':amount
        },
        dataType : "json"
    });

    request2.done(function(data) {
        jQuery("#shoppingcartholder").empty();
        jQuery("#shoppingcartholder").append(data.html);
        jQuery("#spcnboitems").text(data.totalItems);
        jQuery("#shoppingcart").effect("bounce");
    });

    request2.fail(function(jqXHR, textStatus) {
        console.log("error in adding item to cart");
    });
}


function removeProductFromCart () {
    console.log("test");
}

jQuery(".productbuybtn").click(function(){
    var productId=jQuery(this).attr("data-productid");
    var variationId=jQuery(this).attr("data-variationid");
    var amount=1;
    if ((!jQuery.isEmptyObject(productId))&&(!jQuery.isEmptyObject(variationId))){
        jQuery(this).effect("transfer",{to:"#shoppingcart",className: "ui-effects-transfer"},500,function(){
            addProductToCart (productId, variationId, amount);
        });
    }
});
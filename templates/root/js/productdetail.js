jQuery(document).ready(function(e) {
    jQuery(".productbuybox").each(function(){
        var mainBox=jQuery(this);
        var configData=JSON.parse(mainBox.attr("data-productproperties"));
        var initialVariationIdentifier=getParameterByName("variation");
        var initialVariation=false;
        if (!jQuery.isEmptyObject(initialVariationIdentifier)){
            configData.variations.forEach(function(variation){
                if (variation.id==initialVariationIdentifier){
                    initialVariation=variation;
                }
            });
        }
        if (!initialVariation){
            initialVariation=configData.variations[0];
        }
        if ('undefined' != typeof initialVariation.specialOffer && null != initialVariation.specialOffer) {
            mainBox.find(".productpricetext").text(initialVariation.specialOffer + " €");
        } else {
            mainBox.find(".productpricetext").text(initialVariation.price+" €");
        }
        mainBox.find(".productbuybtn").attr("data-variationid",initialVariation.id);
        mainBox.find(".productbuybtn").removeAttr("disabled");
        mainBox.find(".productavailabilitytext").text("");
        mainBox.find(".productavailabilitytext").addClass("text-info");
        mainBox.find(".productavailabilitytext").removeClass("text-error");
        if (configData.manageStock){
            var stock=initialVariation.stock;
            var complement;
            if (stock < configData.outOfStockLimit){
                complement=configData.resupplyDelay > 1 ? " days" : " day";
                mainBox.find(".productavailabilitytext").text("Out of stock : ressuplied before "+configData.resupplyDelay+ complement);
                if (configData.canOrderNotInStock=="false"){
                    mainBox.find(".productavailabilitytext").removeClass("text-info");
                    mainBox.find(".productavailabilitytext").addClass("text-error");
                    mainBox.find(".productbuybtn").attr("disabled","disabled");
                }
            } else {
                complement=configData.preparationDelay > 1 ? " days" : " day";
                mainBox.find(".productavailabilitytext").text("In stock : sent before "+configData.preparationDelay + complement);
            }
        }
        var currentConstraints={ };
        mainBox.find("select").each(function(){
            var possibilities = extractOptionPossibilities(configData.variations, jQuery(this).attr("name"),currentConstraints);
            var theCombo=jQuery(this);
            possibilities.forEach(function(possibility){
                theCombo.append("<option>"+possibility+"</option>");
            });
            theCombo.val(initialVariation[theCombo.attr("name")]);
            currentConstraints[theCombo.attr("name")]=theCombo.val();
            theCombo.change(function(){
                adaptToProductOptionsChange(mainBox,theCombo.attr("data-fieldindex"),configData);
            });
        })
    });
});

function extractOptionPossibilities (variations, optionName, constraints) {
    constraints = typeof constraints !== 'undefined' ? constraints : { };
    var result=[];
    variations.forEach(function(variation){
        var isOk=true;
        for (var constraint in constraints) {
            if (isOk && constraints.hasOwnProperty(constraint)){
                if (constraints[constraint]!=variation[constraint].toString()){
                    isOk=false;
                }
            }
        }
        if (isOk){
            var candidate=variation[optionName];
            if (result.indexOf(candidate)==-1){
                result.push(candidate);
            }
        }
    });
    return(result);

}

function adaptToProductOptionsChange (productBox, changedIndex,configData) {
    var currentConstraints={ };
    var variations=configData.variations;
    productBox.find("select").each(function(){
        var theCombo=jQuery(this);
        if (theCombo.attr("data-fieldindex")<=changedIndex){
            currentConstraints[theCombo.attr("name")]=theCombo.val();
        } else {
            var newPossibilities=extractOptionPossibilities(variations, theCombo.attr("name"), currentConstraints);
            theCombo.empty();
            newPossibilities.forEach(function(possibility){
                theCombo.append("<option>"+possibility+"</option>");
            });
            currentConstraints[theCombo.attr("name")]=theCombo.val();
        }

    });
    var newVariations=[];
    variations.forEach(function(variation){
        var isOk=true;
        for (var constraint in currentConstraints) {
            if (isOk&&(currentConstraints.hasOwnProperty(constraint))){
                if (currentConstraints[constraint]!=variation[constraint].toString()){
                    isOk=false;
                }
            }
        }
        if (isOk){
            newVariations.push(variation)
        }

    });
    if (newVariations.length==0){
        console.log("Error : inexistent variation");
    } else {
        if (newVariations.length>1){
            console.log("Warning : multiple variation possibilities");
        }
        var newVariation=newVariations[0];
        if ('undefined' != typeof newVariation.specialOffer && null != newVariation.specialOffer) {
            productBox.find(".productpricetext").text(newVariation.specialOffer + " €");
        } else {
            productBox.find(".productpricetext").text(newVariation.price+" €");
        }
        productBox.find(".productbuybtn").attr("data-variationid",newVariation.id);
        productBox.find(".productbuybtn").removeAttr("disabled");
        productBox.find(".productavailabilitytext").text("");
        productBox.find(".productavailabilitytext").addClass("text-info");
        productBox.find(".productavailabilitytext").removeClass("text-error");
        if (configData.manageStock){
            var stock=newVariation.stock;
            if (stock < configData.outOfStockLimit){
                productBox.find(".productavailabilitytext").text("Out of stock : ressuplied before "+configData.resupplyDelay+" days");
                if (configData.canOrderNotInStock=="false"){
                    productBox.find(".productavailabilitytext").removeClass("text-info");
                    productBox.find(".productavailabilitytext").addClass("text-error");
                    productBox.find(".productbuybtn").attr("disabled","disabled");
                }
            } else {
                productBox.find(".productavailabilitytext").text("In stock : sent before "+configData.preparationDelay+" days");
            }
        }

    }
}

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}
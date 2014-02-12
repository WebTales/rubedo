jQuery(document).ready(function(e) {
    jQuery(".productbuybox").each(function(){
        var mainBox=jQuery(this);
        var configData=JSON.parse(mainBox.attr("data-productproperties"));
        var initialVariation=configData.variations[0];
        mainBox.find(".productpricetext").text(initialVariation.price+" €");
        mainBox.find(".productbuybtn").attr("data-variationid",initialVariation.id);
        mainBox.find("select").each(function(){
            var possibilities = extractOptionPossibilities(configData.variations, jQuery(this).attr("name"));
            var theCombo=jQuery(this);
            possibilities.forEach(function(possibility){
                theCombo.append("<option>"+possibility+"</option>");
            });
            theCombo.val(initialVariation[theCombo.attr("name")]);
            theCombo.change(function(){
                adaptToProductOptionsChange(mainBox,theCombo.attr("data-fieldindex"),configData.variations);
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
            if (isOk&&(constraints.hasOwnProperty(constraint))){
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

function adaptToProductOptionsChange (productBox, changedIndex,variations) {
    var currentConstraints={ };
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
        productBox.find(".productpricetext").text(newVariation.price+" €");
        productBox.find(".productbuybtn").attr("data-variationid",newVariation.id);
    }
}

BlueprintIT.validation.validateField=function(input,allowempty){if((input.value=='')&&!allowempty){return false;}
return true;}
BlueprintIT.validation.validateAsNumber=function(input,allowempty,constraints){if(allowempty&&(input.value==''))
return true;return true;}
BlueprintIT.validation.validateAsEmail=function(input,allowempty,constraints){if(allowempty&&(input.value==''))
return true;return true;}
BlueprintIT.validation.validateAsRegex=function(input,allowempty,constraints){if(allowempty&&(input.value==''))
return true;return true;}
BlueprintIT.validation.validateForm=function(form,constraints,listener){var result=true;for(var i=0;i<form.elements.length;i++){var input=form.elements[i];var name=input.name;if(constraints[name]){var type;var empty=true;if(typeof constraints[name]=='string')
type=constraints[name];else{type=constraints[name].type;empty=constraints[name].allowempty;}
var valid=false;if(type=='number')
valid=BlueprintIT.validation.validateAsNumber(input,empty,constraints[field]);else if(type=='regex')
valid=BlueprintIT.validation.validateAsNumber(input,empty,constraints[field]);else if(type=='email')
valid=BlueprintIT.validation.validateAsNumber(input,empty,constraints[field]);else
valid=BlueprintIT.validation.validateField(input,empty);if(!valid){if(listener){result=false;if(listener.onValidationFailure)
listener.onValidationFailure(form,input,type,empty);}
else
return false;}}}
if(listener)
result=listener.onValidationComplete(form,result);return result;}
BlueprintIT.forms.submitForm = function(form, name, value) {
	var formel = document.forms[form];
	if (typeof(name) !== 'undefined') {
		var input = document.createElement("input");
		input.setAttribute("type", "hidden");
		input.setAttribute("name", name);
		input.setAttribute("value", value);
		formel.appendChild(input);
	}
	
	formel.submit();
}

BlueprintIT.forms.validateAndSubmit = function(form, constraints, listener) {
	if (BlueprintIT.validation.validateForm(document.forms[form], constraints, listener)) {
		document.forms[form].submit();
	}
}

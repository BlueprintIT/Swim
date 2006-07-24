BlueprintIT.dialog.Alert = {};

BlueprintIT.dialog.Alert.callback = function() {
	this.hide();
}
	
BlueprintIT.dialog.Alert.dialog = new YAHOO.widget.SimpleDialog("alertdlg", {
		width: "20em",
		effect: { effect: YAHOO.widget.ContainerEffect.FADE, duration: 0.5 },
		fixedcenter: true,
		modal: true,
		draggable: false,
		visible: false,
		underlay: "shadow",
		buttons: [ { text: "Ok", handler: BlueprintIT.dialog.Alert.callback }]
	});
	
BlueprintIT.dialog.Alert.show = function(title, message, icon) {
	if (!icon)
		icon = YAHOO.widget.SimpleDialog.ICON_WARN;
	BlueprintIT.dialog.Alert.dialog.setHeader(title);
	BlueprintIT.dialog.Alert.dialog.setBody(message);
	BlueprintIT.dialog.Alert.dialog.cfg.setProperty("icon", icon);
	BlueprintIT.dialog.Alert.dialog.render(document.body);
	BlueprintIT.dialog.Alert.dialog.show();
}


BlueprintIT.dialog.Alert={};BlueprintIT.dialog.Wait={};if(YAHOO.widget.Panel){BlueprintIT.dialog.Alert.callback=function(){this.hide();}
BlueprintIT.dialog.Alert.dialog=new YAHOO.widget.SimpleDialog("alertdlg",{width:"20em",effect:{effect:YAHOO.widget.ContainerEffect.FADE,duration:0.5},fixedcenter:true,modal:true,draggable:false,visible:false,underlay:"shadow",buttons:[{text:"Ok",handler:BlueprintIT.dialog.Alert.callback}]});BlueprintIT.dialog.Alert.show=function(title,message,icon){if(!icon)
icon=YAHOO.widget.SimpleDialog.ICON_WARN;BlueprintIT.dialog.Alert.dialog.setHeader(title);BlueprintIT.dialog.Alert.dialog.setBody(message);BlueprintIT.dialog.Alert.dialog.cfg.setProperty("icon",icon);BlueprintIT.dialog.Alert.dialog.render(document.body);BlueprintIT.dialog.Alert.dialog.show();}
BlueprintIT.dialog.Wait.dialog=new YAHOO.widget.Panel("loaddlg",{width:"240px",fixedcenter:true,underlay:"shadow",close:false,visible:false,draggable:false,modal:false});BlueprintIT.dialog.Wait.show=function(message){BlueprintIT.dialog.Wait.dialog.setHeader(message);BlueprintIT.dialog.Wait.dialog.setBody("<img src=\"/swim/shared/images/loading.gif\"/>");BlueprintIT.dialog.Wait.dialog.render(document.body);BlueprintIT.dialog.Wait.dialog.show();}
BlueprintIT.dialog.Wait.hide=function(){BlueprintIT.dialog.Wait.dialog.hide();}}
else{BlueprintIT.dialog.Alert.show=function(title,message,icon){alert(message);}
BlueprintIT.dialog.Wait.show=function(message){}
BlueprintIT.dialog.Wait.hide=function(){}}
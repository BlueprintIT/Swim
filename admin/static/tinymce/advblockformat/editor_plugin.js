
tinyMCE.importPluginLanguagePack('advblockformat','en,tr,he,nb,ru,ru_KOI8-R,ru_UTF-8,nn,fi,cy,es,is,pl');var TinyMCE_AdvBlockFormatPlugin={getInfo:function(){return{longname:'Advanced Block Format plugin',author:'Dave Townsend',authorurl:'http://www.blueprintit.co.uk',infourl:'http://www.blueprintit.co.uk',version:"1.0"};},formats:[{name:"Heading 1",tag:"h1",attributes:{}},{name:"Heading 2",tag:"h2",attributes:{}},{name:"Heading 3",tag:"h2",attributes:{}},{name:"Normal",tag:"p",attributes:{}},],editors:[],loading:false,loaded:false,request:false,initInstance:function(inst){var plugin=TinyMCE_AdvBlockFormatPlugin;plugin.editors.push(tinyMCE.getEditorId(inst.formTargetElementId));if(!plugin.loading){plugin.loading=true;var url=tinyMCE.getParam("advblockformat_stylesurl",null)
if(url){plugin.request=plugin.makeXMLHttpRequest();if(plugin.request){plugin.request.open("GET",url,true);plugin.request.send("");window.setTimeout(plugin.processReqChange,100);}
else
plugin.loaded=true;}}},getControlHTML:function(cn){var plugin=TinyMCE_AdvBlockFormatPlugin;switch(cn){case"advblockformat":var html='<select id="{$editor_id}_advblockformat" title="'+tinyMCE.getLang('lang_advblockformat_title')+'" name="{$editor_id}_advblockformat" onfocus="tinyMCE.addSelectAccessibility(event, this, window);" onchange="tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mceAdvFormatBlock\',false,this.options[this.selectedIndex].value);" class="mceSelectList">';if(!plugin.loaded){html+='<option disabled="disabled" value="-1">'+tinyMCE.getLang('lang_advblockformat_loading')+'</option>';}
else{html+='<option disabled="disabled" value="-1">'+tinyMCE.getLang('lang_advblockformat_unknown')+'</option>';for(var i in plugin.formats)
html+='<option value="'+i+'">'+plugin.formats[i].name+'</option>';}
html+='</select>';return html;}
return"";},execCommand:function(editor_id,element,command,user_interface,value){var plugin=TinyMCE_AdvBlockFormatPlugin;switch(command){case"mceAdvFormatBlock":if(plugin.loaded&&plugin.formats[value]){var editor=tinyMCE.getInstanceById(editor_id);var selection=editor.getSel();var doc=editor.getDoc();var nodes=plugin.getTextNodes(plugin.getSelectionStart(doc,selection),plugin.getSelectionEnd(doc,selection));var block=null;for(var i=0;i<nodes.length;i++){var next=tinyMCE.getParentBlockElement(nodes[i]);if(next!=block){oldformat=plugin.findMatchingFormat(next);if(oldformat){for(var attr in plugin.formats[oldformat].attributes){if(attr=="class")
next.className="";else
next.removeAttribute(attr);}}
block=next;}}
var newformat=plugin.formats[value];tinyMCE.execInstanceCommand(editor_id,"FormatBlock",user_interface,"<"+newformat.tag+">");var selection=editor.getSel();var doc=editor.getDoc();var nodes=plugin.getTextNodes(plugin.getSelectionStart(doc,selection),plugin.getSelectionEnd(doc,selection));var block=null;var changed=false;for(var i=0;i<nodes.length;i++){var next=tinyMCE.getParentBlockElement(nodes[i]);if(next!=block){if(next.tagName.toLowerCase()==newformat.tag){for(var attr in newformat.attributes){if(attr=="class")
next.className=newformat.attributes[attr];else
next.setAttribute(attr,newformat.attributes[attr]);changed=true;}}
else
alert("Possible issue, found a block "+next.tagName.toLowerCase()+" expected a "+newformat.tag);block=next;}}
if(changed)
tinyMCE.triggerNodeChange();}
return true;}
return false;},handleNodeChange:function(editor_id,node,undo_index,undo_levels,visual_aid,any_selection){var plugin=TinyMCE_AdvBlockFormatPlugin;var select=document.getElementById(editor_id+"_advblockformat");if(plugin.loaded&&select){var format=null;var editor=tinyMCE.getInstanceById(editor_id);var selection=editor.getSel();var doc=editor.getDoc();var nodes=plugin.getTextNodes(plugin.getSelectionStart(doc,selection),plugin.getSelectionEnd(doc,selection));var block=null;for(var i=0;i<nodes.length;i++){var next=tinyMCE.getParentBlockElement(nodes[i]);if(next!=block){var foundformat=plugin.findMatchingFormat(next);if(!foundformat){format=null;break;}
if(!block)
format=foundformat;else if(format!=foundformat){format=null;break;}
block=next;}}
if(format)
select.value=format;else
select.value=-1;}},setupContent:function(editor_id,body,doc){},onChange:function(inst){},handleEvent:function(e){return true;},cleanup:function(type,content,inst){return content;},findMatchingFormat:function(element){var tag=element.tagName.toLowerCase();for(var i in this.formats){if(this.formats[i].tag==tag){var match=true;for(var attr in this.formats[i].attributes){if(attr=="class"){if(element.className!=this.formats[i].attributes[attr]){match=false;break;}}
else if(element.getAttribute(attr)!=this.formats[i].attributes[attr]){match=false;break;}}
if(match)
return i;}}
return null;},seekRangeStart:function(context,seeker,range)
{if(!context.hasChildNodes)
return context;var lastel=context.firstChild;var check=lastel;while(check){if(check.nodeType==1){seeker.moveToElementText(check);var stcheck=seeker.compareEndPoints("StartToStart",range);var edcheck=seeker.compareEndPoints("EndToStart",range);if(stcheck>0)
return lastel;if(edcheck>=0)
return this.seekRangeEnd(check,seeker,range);lastel=check.nextSibling;}
check=check.nextSibling;}
return lastel;},seekRangeEnd:function(context,seeker,range)
{if(!context.hasChildNodes)
return context;var lastel=context.lastChild;var check=lastel;while(check){if(check.nodeType==1){seeker.moveToElementText(check);var stcheck=seeker.compareEndPoints("StartToEnd",range);var edcheck=seeker.compareEndPoints("EndToEnd",range);if(edcheck<0)
return lastel;if(stcheck<=0)
return this.seekRangeEnd(check,seeker,range);lastel=check.previousSibling;}
check=check.previousSibling;}
return lastel;},getSelectionStart:function(doc,selection)
{if(selection.getRangeAt)
return selection.getRangeAt(0).startContainer;var range=selection.createRange();var seeker=range.duplicate();seeker.moveToElementText(doc.body);if(seeker.compareEndPoints("StartToStart",range)==0)
return doc.body;return this.seekRangeStart(doc.body,seeker,range);},getSelectionEnd:function(doc,selection)
{if(selection.getRangeAt)
return selection.getRangeAt(0).endContainer;var range=selection.createRange();var seeker=range.duplicate();seeker.moveToElementText(doc.body);if(seeker.compareEndPoints("EndToEnd",range)==0)
return doc.body;return this.seekRangeEnd(doc.body,seeker,range);},getTextNodes:function(start,end)
{var nodes=[];if(!end)
end=start;var context=start;var ignorable=false;if(start.firstChild){context=start.firstChild;ignorable=true;}
var whitespace=/^\s*$/;while(true){if(context.nodeType==3){if(ignorable&&!whitespace.test(context.nodeValue))
ignorable=false;if(!ignorable)
nodes.push(context);}
if(context.firstChild){if(!ignorable){var backtrack=context;while((backtrack)&&(backtrack.nodeType==3)&&(whitespace.test(backtrack.nodeValue))){nodes.pop();backtrack=backtrack.previousSibling;}
ignorable=true;}
context=context.firstChild;continue;}
if(context==end)
return nodes;if(context.nextSibling)
context=context.nextSibling;else{if(!ignorable){var backtrack=context;while((backtrack)&&(backtrack.nodeType==3)&&(whitespace.test(backtrack.nodeValue))){nodes.pop();backtrack=backtrack.previousSibling;}
ignorable=true;}
while(!context.nextSibling){if(context==end)
return nodes;context=context.parentNode;if(!context)
return nodes;}
context=context.nextSibling;}}},makeXMLHttpRequest:function(){if(window.XMLHttpRequest){try{return new XMLHttpRequest();}
catch(e){return null;}}
else if(window.ActiveXObject){try{return new ActiveXObject("Msxml2.XMLHTTP");}
catch(e){try{return new ActiveXObject("Microsoft.XMLHTTP");}
catch(e){return null;}}}
return null;},processReqChange:function(){var plugin=TinyMCE_AdvBlockFormatPlugin;if(plugin.request.readyState==4){if(plugin.request.status==200){plugin.formats=[];var nodes=plugin.request.responseXML.documentElement.getElementsByTagName("Style");for(var i=0;i<nodes.length;i++){var style={};style.name=nodes[i].getAttribute("name");style.tag=nodes[i].getAttribute("element").toLowerCase();style.attributes={};var attrs=nodes[i].getElementsByTagName("Attribute");for(var j=0;j<attrs.length;j++){var name=attrs[j].getAttribute("name");var value=attrs[j].getAttribute("value");if(name!="style")
style.attributes[name]=value;}
plugin.formats.push(style);}}
for(var i in plugin.editors){var editor_id=plugin.editors[i];var select=document.getElementById(editor_id+"_advblockformat");if(select){select.innerHTML='';var option=document.createElement("option");option.setAttribute("disabled","disabled");option.setAttribute("value","-1");option.innerHTML=tinyMCE.getLang('lang_advblockformat_unknown');select.appendChild(option);for(var i in plugin.formats){option=document.createElement("option");option.setAttribute("value",i);option.innerHTML=plugin.formats[i].name;select.appendChild(option);}
var inst=tinyMCE.getInstanceById(editor_id);plugin.handleNodeChange(editor_id,inst.getFocusElement());}}
plugin.loaded=true;plugin.request=null;}
else
window.setTimeout(plugin.processReqChange,100);}};tinyMCE.addPlugin("advblockformat",TinyMCE_AdvBlockFormatPlugin);
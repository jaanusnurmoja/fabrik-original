var FbDropdown=new Class({Extends:FbElement,initialize:function(b,a){this.plugin="fabrikdropdown";this.parent(b,a);if(this.options.allowadd===true&&this.options.editable!==false){this.watchAddToggle();this.watchAdd()}},watchAddToggle:function(){var h=this.getContainer();var f=h.getElement("div.addoption");var b=h.getElement(".toggle-addoption");if(this.mySlider){var g=f.clone();var e=h.getElement(".fabrikElement");f.getParent().destroy();e.adopt(g);f=h.getElement("div.addoption");f.setStyle("margin",0)}this.mySlider=new Fx.Slide(f,{duration:500});this.mySlider.hide();b.addEvent("click",function(a){a.stop();this.mySlider.toggle()}.bind(this))},watchAdd:function(){var a;if(this.options.allowadd===true&&this.options.editable!==false){var d=this.element.id;var b=this.getContainer();b.getElement("input[type=button]").addEvent("click",function(i){var c=b.getElement("input[name=addPicklistLabel]");var f=b.getElement("input[name=addPicklistValue]");var g=c.value;if(f){a=f.value}else{a=g}if(a===""||g===""){alert(Joomla.JText._("PLG_ELEMENT_DROPDOWN_ENTER_VALUE_LABEL"))}else{var h=new Element("option",{selected:"selected",value:a}).set("text",g).inject(document.id(this.element.id));i.stop();if(f){f.value=""}c.value="";this.addNewOption(a,g)}}.bind(this))}},getValue:function(){if(!this.options.editable){return this.options.value}if(typeOf(this.element.get("value"))==="null"){return""}return this.element.get("value")},reset:function(){var a=this.options.defaultVal;this.update(a)},update:function(c){if(typeOf(c)==="string"){c=JSON.decode(c)}if(typeOf(c)==="null"){c=[]}this.getElement();if(typeOf(this.element)==="null"){return}this.options.element=this.element.id;if(!this.options.editable){this.element.set("html","");var b=$H(this.options.data);c.each(function(d){this.element.innerHTML+=b.get(d)+"<br />"}.bind(this));return}for(var a=0;a<this.element.options.length;a++){if(c.indexOf(this.element.options[a].value)!==-1){this.element.options[a].selected=true}else{this.element.options[a].selected=false}}this.watchAdd()},cloned:function(){if(this.options.allowadd===true&&this.options.editable!==false){this.watchAddToggle();this.watchAdd()}}});
var FbThumbs=new Class({Extends:FbElement,initialize:function(c,b,a){this.field=document.id(c);this.imagepath=Fabrik.liveSite+"plugins/fabrik_element/thumbs/images/";this.parent(c,b);this.element=document.id(c+"_div");this.thumb=a;this.spinner=new Asset.image(Fabrik.liveSite+"media/com_fabrik/images/ajax-loader.gif",{alt:"loading","class":"ajax-loader"});this.thumbup=document.id("thumbup");this.thumbdown=document.id("thumbdown");this.thumbup.addEvent("mouseover",function(d){this.thumbup.setStyle("cursor","pointer");this.thumbup.src=this.imagepath+"thumb_up_in.gif"}.bind(this));this.thumbdown.addEvent("mouseover",function(d){this.thumbdown.setStyle("cursor","pointer");this.thumbdown.src=this.imagepath+"thumb_down_in.gif"}.bind(this));this.thumbup.addEvent("mouseout",function(d){this.thumbup.setStyle("cursor","");if(this.options.myThumb==="up"){this.thumbup.src=this.imagepath+"thumb_up_in.gif"}else{this.thumbup.src=this.imagepath+"thumb_up_out.gif"}}.bind(this));this.thumbdown.addEvent("mouseout",function(d){this.thumbdown.setStyle("cursor","");if(this.options.myThumb==="down"){this.thumbdown.src=this.imagepath+"thumb_down_in.gif"}else{this.thumbdown.src=this.imagepath+"thumb_down_out.gif"}}.bind(this));this.thumbup.addEvent("click",function(d){this.doAjax("up")}.bind(this));this.thumbdown.addEvent("click",function(d){this.doAjax("down")}.bind(this))},doAjax:function(b){if(this.options.editable===false){var a=document.id("count_thumb"+b);this.spinner.inject(a);var c={option:"com_fabrik",format:"raw",task:"plugin.pluginAjax",plugin:"thumbs",method:"ajax_rate",g:"element",element_id:this.options.elid,row_id:this.options.row_id,elementname:this.options.elid,userid:this.options.userid,thumb:b,listid:this.options.listid};new Request({url:"",data:c,onComplete:function(f){f=JSON.decode(f);this.spinner.dispose();if(f.error){console.log(f.error)}else{if(f!==""){var e=document.id("count_thumbup");var g=document.id("count_thumbdown");var d=document.id("thumbup");var h=document.id("thumbdown");e.set("html",f[0]);g.set("html",f[1]);document.id(this.element.id).getElement("."+this.field.id).value=f[0].toFloat()-f[1].toFloat();if(f[0]===1){d.src=this.imagepath+"thumb_up_in.gif";h.src=this.imagepath+"thumb_down_out.gif"}else{d.src=this.imagepath+"thumb_up_out.gif";h.src=this.imagepath+"thumb_down_in.gif"}}}}.bind(this)}).send()}}});
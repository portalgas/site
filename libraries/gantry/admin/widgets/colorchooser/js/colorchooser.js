/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2012 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var GantryColorChooser={add:function(e,d){var b=e.replace(/-/,"_"),c;if(!window.moorainbow){window.moorainbow={};}var a=function(){var f=document.id(e);
f.getParent().removeEvent("mouseenter",a);c=new MooRainbow("myRainbow_"+e+"_input",{id:"myRainbow_"+e,startColor:document.id(e).get("value").hexToRgb(true)||[255,255,255],imgPath:GantryURL+"/admin/widgets/colorchooser/images/",transparent:d,onChange:function(g){if(g=="transparent"){f.getNext().getFirst().addClass("overlay-transparent").setStyle("background-color","transparent");
f.value="transparent";}else{f.getNext().getFirst().removeClass("overlay-transparent").setStyle("background-color",g.hex);f.value=g.hex;}if(this.visible){this.okButton.focus();
}}});c.hide();window.moorainbow["r_"+b]=c;c.okButton.setStyle("outline","none");document.id("myRainbow_"+e+"_input").addEvent("click",function(){(function(){c.okButton.focus();
}).delay(10);});f.addEvent("keyup",function(k){if(k){k=new Event(k);}if((this.value.length==4||this.value.length==7)&&this.value[0]=="#"){var i=new Color(this.value);
var j=this.value;var g=i.rgbToHsb();var h={hex:j,rgb:i,hsb:g};c.fireEvent("onChange",h);c.manualSet(h.rgb);}}).addEvent("set",function(g){this.value=g;
this.fireEvent("keyup");});f.getNext().getFirst().setStyle("background-color",c.sets.hex);GantryColorChooser.load("myRainbow_"+e);};if(b.contains("gradient")&&(b.contains("from")||b.contains("to"))){a();
}else{window.addEvent("domready",function(){document.id(e).getParent().addEvents({mouseenter:a,mouseleave:function(){this.removeEvent("mouseenter",a);}});
});}},load:function(a,b){if(b){document.id(a+"_input").getPrevious().value=b;document.id(a+"_input").getFirst().setStyle("background-color",b);}}};
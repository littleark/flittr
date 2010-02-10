/**
 * Flitter, waste your time enjoying twitter+flickr
 * @name flittering.js
 * @version 0.1
 * @author Carlo Zapponi - info@makinguse.com
 * @site http://flitter.makinguse.com
 * @date October 12, 2008
 * @license CC Attribution-Share Alike 3 - http://creativecommons.org/licenses/by-sa/3.0/us/
 * @example Visit http://flitter.makinguse.com to see it working
 * @requires jQuery 1.2+
 */
var ompic={};
//(function($){
		
		if(typeof console=='undefined') {
			console={
				log:function(){}
			}
		}
		ompic=$.extend(ompic,{
			init:function() {
				
				var options = jQuery.extend({
					id:null,
					key:"01cc21c83d6c92f5d00c8e4c1426c2ff",
					time:20
				}, arguments[1] || {});
				this.options  = options;
				this.current_img=null;
				this.current_twit=null;
				this.ready=true;
				this.loaded=false;
				//this.current_keyword="il OR la OR i OR un OR al OR sul OR al OR con";
				this.current_keyword="the";
				this.interval=null;
				this.current_twit={
					t:""
				};
				
				var screen={
					w:$(window).width(),
					h:$(window).height()
				};
				var def={
					w:$("#pic").width(),
					h:$("#pic").height()
				};
				var $this=this;
				$("#pic").bind("pic_loaded",function(e){
					$this.loaded=true;
					$("#next #loader").html("ready!");
					$this.enableNext();
					if($this.ready) {
						console.log("img loaded and ready");
						$this.showImg();
					} else {
						console.log("img loaded but not ready, so wait!");
					}

				});
				$("#pic").bind("tm_ready",function(e){
					$this.ready=true;
					if($this.interval!=null) {
						clearInterval($this.interval);
						$("#timer").hide().html($this.options.time);
					}
					if($this.loaded) {
						console.log("ready and img loaded");
						$this.showImg();
					} else {
						console.log("ready, but img not yet loaded, so wait!");
						if($("#pic img").length>0)
							$("#pic").css({
								opacity:0.5
							});
					}
				});
				this.menu={
					status:"closed",
					o_h:300,
					c_h:35
				};
				this.initMenu();
				this.disableNext();
			},
			initMenu:function() {
				var $this=this;
				$("#footer").animate({opacity:0.1},"slow").bind("mouseenter",function(e){
					$("#footer").animate({opacity:0.9},"fast")
				}).bind("mouseleave",function(e){
					$("#footer").animate({opacity:0.1},"fast")
				}).find("ul li a").click(function(e){
					console.log($this.menu.status);
					if ($this.menu.status=="closed") {
						$this.menu.status="opening";
						console.log("opening by "+($this.menu.o_h-$this.menu.c_h))
						$("#footer").animate({
							left:"+="+($this.menu.o_h-$this.menu.c_h)+"px"
						},"slow",function(){
								console.log("...")
								$("#footer ul li a").eq(0).html("c<br/>l<br/>o<br/>s<br/>e<br/>");
								$this.menu.status="opened";
							});
					} else if ($this.menu.status=="opened") {
						$this.menu.status="closing";
						console.log("closing by "+($this.menu.o_h-$this.menu.c_h))
						$("#footer").animate({
							left:"-="+($this.menu.o_h-$this.menu.c_h)+"px"
						},"slow",function(){
							//$("#footer #close").hide("slow");
							$("#footer ul li a").eq(0).html("m<br/>e<br/>n<br/>u<br/>");
							$this.menu.status="closed";
						});
					}
					return false;
				});
				
			},
			enableNext:function() {
				var $this=this;
				$("#next a").unbind("click").click(function(e){
					if(!$this.loading) {
						$this.loading=true;
						/*
						if($this.tm!=null)
							clearTimeout($this.tm);
						*/
						$("#pic").trigger("tm_ready");
						//$this.searchTiwtter();
					}
					return false;
				});
			},
			disableNext:function() {
				var $this=this;
				
				$("#next a").unbind("click").click(function(e){
					return false;
				});
			},
			searchTiwtter:function() {
				$("#next #loader").html("<span style=\"color:#555\">pre</span>loading...").show("slow");
				this.disableNext();
				//var request="http://localhost/flitter/flitter2.php"+((typeof this.current_twit.twts != 'undefined')?"?twts="+this.current_twit.twts:"");
				var request="http://www.podipodi.com/flitter/flitter.php"+((typeof this.current_twit.twts != 'undefined')?"?twts="+this.current_twit.twts:"");

				//$.getScript(request);
				ompic.deliveredPic({tags:["abc","cde"],twitter:{t:"ciao come va?",u:"carlo",twts:"1234"},flickr:{user:"carlo",title:"pizza",date:"2008-12-12",tag_mode:"any",uid:"12345",src:"http://localhost/flitter/pics/2930412011_4575292843.jpg"}});
				/*
				if(this.interval!=null) {
					clearInterval(this.interval);
					$("#timer").hide().html(this.options.time);
				}
				*/
				/*
				if($("#pic img").length>0)
					$("#pic").css({
						opacity:0.5
					});
				*/
			},
			deliveredPic:function(json) {
				console.log(json);

				this.current_img=json.flickr
				this.current_twit=json.twitter;
				this.current_tags=json.tags;
				
				var $this=this;
				var src=this.current_img.src;
				var img=new Image();
				img.src=src;
				if (img.complete) {
					//$this.showImg();
					$("#pic").trigger("pic_loaded");
				} else {
					img.onload = function() {
						//$this.showImg();
						$("#pic").trigger("pic_loaded");
					};
				}
				
			},
			updateTimer:function(start) {
				var $this=this;
				if(start) {
					this.time=this.options.time;
					$("#timer").show();
				}
				this.interval=setInterval(function(){
					$this.time--;
					$("#timer").html($this.time);
					if($this.time==0) {
						$("#pic").trigger("tm_ready");
					}
				},1000);
			},
			showImg:function() {
				var $this=this;
				$("#next a").show("slow");
				
				
				var screen={
					w:$(window).width(),
					h:$(window).height()
				};
				
				$("#pic").animate({
						opacity:0
				},function(){
					$("#pic span").remove();
					$("#pic > div").empty();
					$("<img></img>").attr("src",$this.current_img.src).appendTo("#pic > div");
					$("<div></div>").addClass("descr").html($this.current_img.title+" taken by <a href=\"http://www.flickr.com/photos/"+$this.current_img.uid+"\" target=\"_blank\">"+$this.current_img.user+"</a> on "+$this.current_img.date).appendTo("#pic > div");
					//$("<div></div>").addClass("descr").css({maxWidth:"80%",margin:"0 auto 5px",position:"abosolute"}).html("<span class=\"twit\">"+$this.current_twit.t+"</span> (<a href=\"http://www.twitter.com/"+$this.current_twit.u+"\"  target=\"_blank\">"+$this.current_twit.u+"</a>)<br/>("+$this.current_tags+")").prependTo("#pic > div");
					$("<div></div>").addClass("descr").css({maxWidth:"80%",margin:"0 auto 5px"}).html("<span class=\"twit\">"+$this.current_twit.t+"</span> (<a href=\"http://www.twitter.com/"+$this.current_twit.u+"\"  target=\"_blank\">"+$this.current_twit.u+"</a>)").prependTo("#pic > div");
					
					$("#pic").css({
						opacity:1,
						top:"-9999px"
					}).show();
					//console.log($("#pic").width());
					var pic={
						w:$("#pic").width(),
						h:$("#pic").height()
					};
					//console.log(pic.w);
					
					$("#pic").css({
						//left:((screen.w/2)-(pic.w)/2)+"px",
						top:((screen.h/2)-(pic.h)/2)+"px",
						opacity:0
					}).animate({opacity:1},function(){
						$this.loading=false;
						//console.log($("#pic").width());
						$this.updateTimer(true);
						//$this.tm=setTimeout(function(){ompic.searchTiwtter()},$this.options.time*1000);
						//$this.current_twit=null;
						$this.ready=false;
						$this.loaded=false;
						/*
						$this.tm=setTimeout(function(){
							$("#pic").trigger("tm_ready");
						},$this.options.time*1000);
						*/
						ompic.searchTiwtter();
					});
				});
				
				
			}
			
		});
		
	
	
		/*
		if(typeof(console)=='undefined') {
			alert("!")
			console={
				log:function(txt){}
			}
		}
		*/
		
//})(jQuery);

window.szCorePhp="index.php";$(window).resize(function(){$(".file-container").css("height",(document.body.clientHeight-160)+"px");$(".url-sub-container-outer").width(10000);$(".url-sub-container-outer").width($(".url-sub-container-inner").width());$(".url-sub-container-outer").css("float",$(".url-sub-container").width()>=$(".url-sub-container-outer").width()?"left":"right")}).load(function(){var A=window.location.href;if(A.indexOf("?")>=0){A=A.substr(A.indexOf("?")+1)}else{A=""}if(A.indexOf("#")>=0){A=A.substr(0,A.indexOf("#"))}if(A.indexOf("cd=")>=0){A=A.substr(A.indexOf("cd=")+3)}if(A.indexOf("&")>=0){A=A.substr(0,A.indexOf("&"))}if(A){window.location="index.html#"+A}else{reloadList(window.location.hash.replace(/#/g,""))}}).resize();function reloadList(A){$(".loading-cover").fadeTo(100,0.7);$.ajax({type:"GET",url:"index.php?rt=json&cd="+A+"&_="+(new Date()).getTime(),data:{},contentType:"application/json; charset=utf-8",dataType:"json",success:function(D){var E=new Array();for(var C=0,B=D.sub_dir_vitual.length;C<B;C++){D.sub_dir_vitual[C].type="sub_dir_vitual";E.push(D.sub_dir_vitual[C])}for(var C=0,B=D.sub_dir.length;C<B;C++){D.sub_dir[C].type="sub_dir";E.push(D.sub_dir[C])}for(var C=0,B=D.sub_file.length;C<B;C++){D.sub_file[C].type="sub_file";E.push(D.sub_file[C])}createView(D.cd,D.pd,E);$(".loading-cover").fadeOut(300);bindAction()},error:function(B){$(".loading-cover").fadeOut(300);bindAction();console.log(B)}})}function createView(G,B,F){var D="";var E={title:document.title,url:window.location.href,otherkey:""};window.history.pushState&&window.history.pushState(E,document.title,window.location.href);window.location="#"+G.url;document.title="TMS FILE EXPLORER - "+G.name;for(var C=0,A=B.length;C<A;C++){if(B[C].name&&B[C].url){D+='<div class="url-sub" data-url="'+B[C].url+'">'+B[C].name+'</div><div class="url-sub-spliter"></div>'}}D+='<div class="f_cls"></div>';$(".url-sub-container-inner").html(D);D="";for(var C=0,A=F.length;C<A;C++){D+='<div data-url="'+F[C].url+'" data-type="'+F[C].type+'" class="file-box-container"><div class="file-box"><div class="icon-'+(F[C].type=="sub_file"?"file":"dir")+'"></div><div class="info_container"><div class="file-name">'+F[C].name+'</div><div class="file-desc">'+new Date(F[C].time*1000).format("yyyy/MM/dd hh:mm:ss")+'</div></div><div class="f_cls"></div></div></div>'}D+='<div class="f_cls"></div>';$(".file-container").html(D);$(window).resize()}function bindAction(){$(".url-root").add(".url-sub").unbind("hover").hover(function(){$(this).addClass("url-hover")},function(){$(this).removeClass("url-hover")});$(".file-box-container").unbind("hover").hover(function(){$(this).addClass("file-box-hover")},function(){$(this).removeClass("file-box-hover")});$("[data-url]").unbind("click").click(function(){if($(this).data("url")!=null){if($(this).data("type")=="sub_file"){window.location=window.szCorePhp+"?cd="+$(this).data("url")}else{reloadList($(this).data("url"))}}})}Date.prototype.format=function(A){var C={"M+":this.getMonth()+1,"d+":this.getDate(),"h+":this.getHours(),"m+":this.getMinutes(),"s+":this.getSeconds(),"q+":Math.floor((this.getMonth()+3)/3),S:this.getMilliseconds()};if(/(y+)/.test(A)){A=A.replace(RegExp.$1,(this.getFullYear()+"").substr(4-RegExp.$1.length))}for(var B in C){if(new RegExp("("+B+")").test(A)){A=A.replace(RegExp.$1,(RegExp.$1.length==1)?(C[B]):(("00"+C[B]).substr((""+C[B]).length)))}}return A};
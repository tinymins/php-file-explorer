window.szCorePhp = 'index.php';
$(window).resize(function(){
	$('.file-container').css( 'height', ( $('body').height() - 160 ) + "px" );
	$('.url-sub-container-outer').width(10000);$('.url-sub-container-outer').width($('.url-sub-container-inner').width());
	$('.url-sub-container-outer').css( 'float', $('.url-sub-container').width() >= $('.url-sub-container-outer').width() ? 'left' : 'right' );
}).load(function(){
	reloadList(window.location.hash.replace(/#/g,''));
}).resize();
function reloadList(szUrl){
	$('.loading-img').add('.loading-cover').show();
	$.ajax({
		type: "GET",
		url: "index.php?rt=json&cd="+szUrl+'&_='+(new Date()).getTime(),
		data: {},
		contentType: "application/json; charset=utf-8",
		dataType: "json",
		success: function (data) {
			// Play with returned data in JSON format
			var tFiles = new Array();
			// 处理数据
			for(var i = 0, l = data['sub_dir_vitual'].length; i < l; i++){ data['sub_dir_vitual'][i].type='sub_dir_vitual';tFiles.push(data['sub_dir_vitual'][i]); }
			for(var i = 0, l = data['sub_dir'].length; i < l; i++){ data['sub_dir'][i].type='sub_dir';tFiles.push(data['sub_dir'][i]); }
			for(var i = 0, l = data['sub_file'].length; i < l; i++){ data['sub_file'][i].type='sub_file';tFiles.push(data['sub_file'][i]); }
			// 处理视图
			createView(data['cd'], data['pd'], tFiles);
			$('.loading-img').add('.loading-cover').hide();
			// 绑定跳转按钮事件
			$('[data-url]').unbind().click(function(){ if($(this).data('url')!=null) {
				if($(this).data('type')=='sub_file') window.location=window.szCorePhp+'?cd='+$(this).data('url'); else reloadList($(this).data('url')); 
			}});
		},
		error: function (msg) {
			$('.loading-img').add('.loading-cover').hide();
			// 绑定跳转按钮事件
			$('[data-url]').unbind().click(function(){ if($(this).data('url')!=null) {
				if($(this).data('type')=='sub_file') window.location=window.szCorePhp+'?cd='+$(this).data('url'); else reloadList($(this).data('url')); 
			}});
			console.log(msg);
		}
	});
}
function createView(tCd, tPd, tFiles){
	var szHtml = "";
	// 处理浏览器历史
	window.location="#"+tCd.url; window.history.pushState && window.history.pushState();
	document.title = 'TMS FILE EXPLORER - '+tCd.name;
	// 重建地址栏
	for(var i = 0, l = tPd.length; i < l; i++){ if(tPd[i].name && tPd[i].url) szHtml += '<div class="url-sub" data-url="'+tPd[i].url+'">'+tPd[i].name+'</div><div class="url-sub-spliter"></div>'; }
	szHtml += '<div class="f_cls"></div>';
	$('.url-sub-container-inner').html(szHtml);
	
	szHtml = "";
	// 重建文件列表
	for(var i = 0, l = tFiles.length; i < l; i++){
		szHtml += '<div data-url="'+tFiles[i].url+'" data-type="'+tFiles[i].type+'" class="file-box-container"><div class="file-box"><div class="icon-'+(tFiles[i].type=="sub_file"?'file':'dir')+'"></div><div class="info_container"><div class="file-name">'+tFiles[i].name+'</div><div class="file-desc">'+ new Date(tFiles[i].time*1000).format("yyyy/MM/dd hh:mm:ss")+'</div></div><div class="f_cls"></div></div></div>';
	}
	szHtml += '<div class="f_cls"></div>';
	$('.file-container').html(szHtml);
	
	$(window).resize();
}
// 对Date的扩展，将 Date 转化为指定格式的String   
// 月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符，   
// 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)   
// 例子：   
// (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423   
// (new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18   
Date.prototype.format = function(fmt) { //author: meizz   
  var o = {   
    "M+" : this.getMonth()+1,                 //月份   
    "d+" : this.getDate(),                    //日   
    "h+" : this.getHours(),                   //小时   
    "m+" : this.getMinutes(),                 //分   
    "s+" : this.getSeconds(),                 //秒   
    "q+" : Math.floor((this.getMonth()+3)/3), //季度   
    "S"  : this.getMilliseconds()             //毫秒   
  };   
  if(/(y+)/.test(fmt))   
    fmt=fmt.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length));   
  for(var k in o)   
    if(new RegExp("("+ k +")").test(fmt))   
  fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));   
  return fmt;   
}  
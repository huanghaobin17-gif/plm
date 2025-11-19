function opchild(id,title,href){//第一层弹窗使用msgbox
	var width=  1000; 
	var height= '100%'; 
				$.dialog({
                id: id,
				fixed: false, 
				drag: true, 
				resize:true, 
				lock:true,
				parent:this,
				zIndex:2000,
				height:height,
				width:width,
				title:title,
				max:false,
				min:false,
				top:'50%',
				focus:true,
				content:'url:'+href+'',
				close:function(){this.reload();}//关闭窗口时，刷新父页面
				}).max();
						}
function msgbox(id,title,href){
var width=  1000; 
var height= '100%'; 
				$.dialog({
                id: id,
				fixed: false, 
				drag: true, 
				resize:true, 
				height:height,
				width:width,
				zIndex:1976,
				title:title,
				max:false,
				min:false,
				top:'50%',
				focus:true,
				content:'url:'+href+'',
				close:function(){this.reload();}//关闭窗口时，刷新父页面
				}).max();
						}
function msgdiv(id,title,href){
var width=  1200; 
var height= '100%'; 
				$.dialog({
                id: id,
				fixed: false, 
				drag: true, 
				resize:true, 
				height:height,
				width:width,
				title:title,
				max:false,
				min:false,
				top:'50%',
				focus:true,
				content:'url:'+href+'',
				close:function(){this.reload();}//关闭窗口时，刷新父页面
				}).max_div();
						}
function msg_big(id,title,href){
var width= arguments[3] || 1200; 
var height= arguments[4] || 580; 
				$.dialog({
                id: id,
				fixed: false, 
				drag: true, 
				resize:true, 
				height:height,
				width:width,
				title:title,
				max:false,
				min:false,
				top:'50%',
				focus:true,
				content:'url:'+href+'',
				close:function(){this.reload();}//关闭窗口时，刷新父页面
				}).big_max();
						}
function msgautobox(id,title,href,w,h,t){
				$.dialog({
                id: id,
				fixed: true, 
				drag: true, 
				resize:true, 
				height:h,
				width:w,
				title:title,
				top:t,
				focus:true,
				content:'url:'+href+'',
				close:function(){this.reload();}//关闭窗口时，刷新父页面
				});
				 }
				 
function msgtip(title,content){
	var api = frameElement.api, W = api.opener;
	W.$.dialog({
    lock: true,
	title:title,
    content: content,
	parent:api,
    //icon: 'face-smile.png'
});
}
function msghtml(id,title,content,height){
	$.dialog({
	id: id,
    lock: true,
	title:title,
	height:height,
    content: content
});

}
function closebox(id){$.dialog({id: id,}).time(1);}
function bgcolor(id,color){id=document.getElementById(id);id.style.backgroundColor=color;}
function bgimage(id,image){id=document.getElementById(id);id.style.backgroundImage='url('+image+')';}

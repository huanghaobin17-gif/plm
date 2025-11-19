/**
 * Created by chouxiaoya on 2017/3/18.
 */
$(function(){
    $("#reNextPage").click(function(){
        var p = $(this).attr('title');
        var status = $(this).attr('data-status');
        var url = '/index.php/Home/Repair/moreRepaires/p/'+p+'/status/'+status;
        $.ajax({
            type:"GET",
            url:url,
            //返回数据的格式
            //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".
            beforeSend:function(){
                $("#loadRe").show();
            },
            //成功返回之后调用的函数
            success:function(msg){
                if(msg){
                    $("#rePend").append(msg);
                    $("#loadRe")[0].style.display='none';
                    var p = parseInt($("#reNextPage").attr('title'));
                    p = p+1;
                    $("#reNextPage").attr('title',p);
                }else{
                    $("#reNextPage").html('没有更多数据了');
                    $("#loadRe")[0].style.display='none';
                }
            },
            //调用出错执行的函数
            error: function(){
                //请求出错处理
                alert('服务器繁忙');
            }
        });
    });
    $("#asNextPage").click(function(){
        var p = $(this).attr('title');
        var url = '/index.php/Home/Repair/moreAssets/p/'+p;
        $.ajax({
            type:"GET",
            url:url,
            //返回数据的格式
            //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".
            beforeSend:function(){
                $("#loadAs").show();
            },
            //成功返回之后调用的函数
            success:function(msg){

                if(msg){
                    $("#asPend").append(msg);
                    $("#loadAs")[0].style.display='none';
                    var p = parseInt($("#asNextPage").attr('title'));
                    p = p+1;
                    console.log(p);
                    $("#asNextPage").attr('title',p);
                }else{
                    $("#asNextPage").html('没有更多数据了');
                    $("#loadAs")[0].style.display='none';
                }
            },
            //调用出错执行的函数
            error: function(){
                //请求出错处理
                alert('服务器繁忙');
            }
        });
    });
});
/**
 * Created by 邓锦龙 on 2017/3/22.
 */
$(function(){
    $("#hisApNextPage").click(function(){
        var p = $(this).attr('title');
        var url = '/index.php/Home/Repair/gtApproveLists/type/his/p/'+p;
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
                    $("#hisAp").append(msg);
                    $("#loadRe")[0].style.display='none';
                    var p = parseInt($("#hisApNextPage").attr('title'));
                    p = p+1;
                    console.log(p);
                    $("#hisApNextPage").attr('title',p);
                }else{
                    $("#hisApNextPage").html('没有更多数据了');
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
    $("#nowApNextPage").click(function(){
        var p = $(this).attr('title');
        var url = '/index.php/Home/Repair/gtApproveLists/type/now/p/'+p;
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
                    $("#nowAp").append(msg);
                    $("#loadNowRe")[0].style.display='none';
                    var p = parseInt($("#nowApNextPage").attr('title'));
                    p = p+1;
                    console.log(p);
                    $("#nowApNextPage").attr('title',p);
                }else{
                    $("#nowApNextPage").html('没有更多数据了');
                    $("#loadNowRe")[0].style.display='none';
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
function getDetail(repid,assid) {
    window.location.href = "/index.php/Home/Repair/getApproveDetail/assid/"+assid+"/repid/" + repid;
}
function getDecDetail(repid) {
    window.location.href = "/index.php/Home/Repair/getDecApproveDetail/repid/" + repid;
}
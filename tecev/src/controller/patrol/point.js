/**
 * Created by jinlong on 2017/3/12.
 */
function checkLevel(level){
    //获取已选取表格的level
    var arr = [];
    $('#assTable tr td:nth-child(5)').each(function (key, value) {
        if(key != 0){
            arr.push($(this).html());
        }
    });
    for(var i=0;i<arr.length;i++){
        var op = arr[i].indexOf(level);
        if(op >= 0){
            var id = "tr_"+i;
            $("#"+id).removeAttr('checked');
            $("#"+id).attr('disabled','disabled');
        }else{
            var id = "tr_"+i;
            $("#"+id).removeAttr('disabled');
        }
    }
}
function addPoint()
{
    //获取设置的设备ID
    var assids =[];
    $('input[name="assidSelected"]:checked').each(function(){
        assids.push($(this).val());
    });
    if(assids.length == 0){
        $.dialog({
            icon: 'error_24.gif',
            title:false,
            heght: '5em',
            time: 1,
            content: '请选择保养设备'
        });
        return false;
    }
    //获取设置的级别
    var level           = $("select[name='level']").val();
    if(level == -1){
        $.dialog({
            icon: 'error_24.gif',
            title:false,
            heght: '5em',
            time: 1,
            content: '请选择保养级别'
        });
        return false;
    }
    //获取已选择的点内容
    var alreadySelected = $("select[name='alreadySelected']").val();
    if(!alreadySelected){
        $.dialog({
            icon: 'error_24.gif',
            title:false,
            heght: '5em',
            time: 1,
            content: '请选择点内容'
        });
        return false;
    }
    //数据验证通过
    var data = {
        assids:assids,
        level:level,
        alreadySelected:alreadySelected
    };
    var url = admin_name+'/point_add.html';
    $.ajax({
        type:"POST",
        url:url,
        data:data,
        dataType:"json",
        //返回数据的格式
        //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".
        //成功返回之后调用的函数
        success:function(data){
            if(data.error == 400){
                $.dialog({
                    icon: 'error_24.gif',
                    title:false,
                    heght: '5em',
                    time: 1,
                    content: data.msg
                });
            }else{
                $.dialog({
                    icon: 'success_l.gif',
                    title:false,
                    heght: '5em',
                    time: 1,
                    content: data.msg,
                    close:function(){
                        window.location.reload();
                    }
                });
            }
        },
        //调用出错执行的函数
        error: function(){
            //请求出错处理
            $.dialog({
                icon: 'error_24.gif',
                title:false,
                heght: '5em',
                time: 1,
                content: '服务器繁忙'
            });
        }
    });
}
function updatePoint()
{
    //获取设置的设备ID
    var assid = $("input[name='assid']").val();
    //获取设置的级别
    var level           = $("select[name='level']").val();
    if(level == -1){
        $.dialog({
            icon: 'error_24.gif',
            title:false,
            heght: '5em',
            time: 1,
            content: '请选择保养级别'
        });
        return false;
    }
    //获取已选择的点内容
    var alreadySelected = []; //定义数组
    $("#select2 option").each(function(){ //遍历全部option
        var txt = $(this).val(); //获取option的内容
        alreadySelected.push(txt); //添加到数组中
    });
    if(alreadySelected.length == 0){
        $.dialog({
            icon: 'error_24.gif',
            title:false,
            heght: '5em',
            time: 1,
            content: '请选择点内容'
        });
        return false;
    }
    //数据验证通过
    var data = {
        assids:assid,
        level:level,
        alreadySelected:alreadySelected
    };
    var url = admin_name+'/point_update.html';
    $.ajax({
        type:"POST",
        url:url,
        data:data,
        //dataType:"json",
        //返回数据的格式
        //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".
        //成功返回之后调用的函数
        success:function(data){
            if(data.error == 400){
                $.dialog({
                    icon: 'error_24.gif',
                    title:false,
                    heght: '5em',
                    time: 1,
                    content: data.msg
                });
            }else{
                $.dialog({
                    icon: 'success_l.gif',
                    title:false,
                    heght: '5em',
                    time: 1,
                    content: data.msg
                });
            }
        },
        //调用出错执行的函数
        error: function(){
            //请求出错处理
            $.dialog({
                icon: 'error_24.gif',
                title:false,
                heght: '5em',
                time: 1,
                content: '服务器繁忙'
            });
        }
    });
}

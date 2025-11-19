function addUser(){
    //验证用户名

    var username = $("input[name='username']").val();
    username = trim(username,'g');
    if(username == ''){
        $.dialog({
            icon: 'error_24.gif',
            title:false,
            heght: '5em',
            time: 1,
            content: '用户名不能为空'
        });
        return false;
    }
    //验证密码和确认密码
    var password = $("input[name='password']").val();
    password = trim(password,'g');
    if(password == ''){
        $.dialog({
            icon: 'error_24.gif',
            title:false,
            heght: '5em',
            time: 1,
            content: '密码不能为空'
        });
        return false;
    }
    var checkpassword = $("input[name='checkpassword']").val();
    checkpassword = trim(checkpassword,'g');
    if(checkpassword == ''){
        $.dialog({
            icon: 'error_24.gif',
            title:false,
            heght: '5em',
            time: 1,
            content: '确认密码不能为空'
        });
        return false;
    }
    //验证密码是否一致
    if(password != checkpassword){
        $.dialog({
            icon: 'error_24.gif',
            title:false,
            heght: '5em',
            time: 1,
            content: '两次输入密码不一致'
        });
        return false;
    }
    //验证联系方式
    var telephone = $("input[name='telephone']").val();
    telephone = trim(telephone,'g');
    if(telephone == ''){
        $.dialog({
            icon: 'error_24.gif',
            title:false,
            heght: '5em',
            time: 1,
            content: '手机号码不能为空'
        });
        return false;
    }else{
        if(!checkTel(telephone)){
            $.dialog({
                icon: 'error_24.gif',
                title:false,
                heght: '5em',
                time: 1,
                content: '请输入正确的手机号码'
            });
            return false;
        }
    }
    //验证角色
    var roleid = $("select[name='roleid']").val();
    if(roleid == 0){
        $.dialog({
            icon: 'error_24.gif',
            title:false,
            heght: '5em',
            time: 1,
            content: '请选择角色'
        });
        return false;
    }
    //验证科室
    var chk_value =[];
    $('input[name="departid"]:checked').each(function(){
        chk_value.push($(this).val());
    });
    if(chk_value==null){
        $.dialog({
            icon: 'error_24.gif',
            title:false,
            heght: '5em',
            time: 1,
            content: '请选择科室'
        });
    }
    /*
   /*var departid[] = $("input[name='departid']").val();
   alert(departid);

   if(departid == null){
       $.dialog({
           icon: 'error_24.gif',
           title:false,
           heght: '5em',
           time: 1,
           content: '请选择科室'
       });
       return false;
   }*/
    //数据验证通过
    var gender = $("input[name='gender']:checked").val();
    var status = $("input[name='status']:checked").val();
    var remark = $("textarea[name='remark']").val();
    var url = admin_name+'/Setup/add.html';
    console.log(remark);
    //console.log(departid);
    //return false;
    var data = {
        username:username,
        password:password,
        gender:gender,
        telephone:telephone,
        roleid:roleid,
        departid:chk_value,
        status:status,
        remark:remark
    };
   $.ajax({
        type:"POST",
        url:url,
        data:data,
        //返回数据的格式
        //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".
        //beforeSend:function(){
            //$.dialog.tips('数据加载中...',600,'loading.gif');
        //},
        //成功返回之后调用的函数
        success:function(data){
            if(data == 1){
                $.dialog({
                    icon: 'success_l.gif',
                    title:false,
                    heght: '5em',
                    time: 1,
                    content: '添加成功',
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

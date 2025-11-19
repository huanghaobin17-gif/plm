layui.define(function(exports){
    layui.use(['table', 'upload', 'form', 'tablePlug'], function () {
        var $ = layui.jquery, upload = layui.upload;
        var table = layui.table;
        var form = layui.form;
        var layer = layui.layer, tablePlug = layui.tablePlug;
        upload.render({
            elem: '#uploadDepartsFile', //绑定元素
            url: $(this).attr('data-url'),
            title: '上传文件',
            method: 'POST',
            contentType: 'application/json; charset=utf-8',
            ext: 'xls|xlsx|xlsm',
            type: 'file',
            unwrap: false,
            auto: true,
            data: {"type": "upload"},
            multiple: true,
            before: function (input) {
                //返回的参数item，即为当前的input DOM对象
                layer.load(2);
            },
            done: function (res) {
                //上传完毕回调
                layer.closeAll('loading');
                if(res.status == 1){
                    layer.msg(res.msg,{icon : 1,time:2000},function(){
                        //刷新表格数据
                        table.reload('batchDepartLists', {
                            url: batchAddDepartment
                            ,where: {"type":"getData"}
                            ,page: {
                                curr: 1 //重新从第 1 页开始
                            }
                        });
                    });
                }else{
                    layer.msg(res.msg,{icon : 2,time:2000});
                }
            }
            , error: function () {
                //请求异常回调
                layer.closeAll('loading');
                layer.msg('网络异常，请稍后再重试！',{icon : 2},1000);
            }
        });
        //到出模板
        $("#exploreAssetsModel").on('click', function () {
            window.location.href = 'batchAddDepartment.html?type=exploreDepartModel';
        });
        //获取数据
        table.render({
            elem: '#batchDepartLists'
            , limits: [5, 10, 20, 50, 100, 200,1000]
            , loading: true
            , limit: 10
            , url: batchAddDepartment //数据接口
            , page: {
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            , where: {
                sort: 'hospital_code asc,departnum'
                , order: 'asc'
                , type: 'getData'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'addtime' //排序字段，对应 cols 设定的各字段名
                , type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            }
            , method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            , request: {
                pageName: 'page' //页码的参数名称，默认：page
                , limitName: 'limit' //每页数据量的参数名，默认：limit
            } //如果无需自定义请求参数，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            , cols: [[ //表头
                {
                    type:  'checkbox',
                    fixed: 'left'
                }, {
                    field: 'tempid',
                    title: '序号',
                    width: 50,
                    fixed: 'left',
                    align: 'center',
                    type:  'space',
                    unresize:false,
                    templet: function(d){
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'hospital_code',
                    title: '<i style="color: red;">* </i>医院代码',
                    width: 100,
                    event: 'hospital_code',
                    fixed: 'left',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'departnum',
                    title: '<i style="color: red;">* </i>科室编码',
                    width: 100,
                    event: 'departnum',
                    fixed: 'left',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'parent_departnum',
                    title: '上级科室编码(没有则为0)',
                    width: 200,
                    event: 'departnum',
                    fixed: 'left',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'department',
                    title: '<i style="color: red;">* </i>科室名称',
                    width: 180,
                    fixed: 'left',
                    event: 'department',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'address',
                    title: '<i style="color: red;">* </i>所在位置',
                    width: 180,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {field: 'departrespon', title: '科室负责人', width: 150, edit: 'text', unresize: false, align: 'center'}
                , {field: 'assetsrespon', title: '设备负责人', width: 150, edit: 'text', unresize: false, align: 'center'}
                , {field: 'departtel', title: '科室电话', width: 150, edit: 'text', unresize: false, align: 'center'}
                , {field: 'operation', title: '操作', fixed: 'right', align: 'center'}
            ]]
            , done: function (res, curr, count) {
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
            }
        });

        //监听排序
        table.on('sort(batchDepartData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('batchDepartLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });
        //监听工具条
        table.on('tool(batchDepartData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var data = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = batchAddDepartment;
            var params = {};
            params['tempid'] = data.tempid;
            params.type = 'updateData';
            if(layEvent === 'delTmpDeparts'){ //删除
                //do something
                params.type = $(this).attr('data-type');
                layer.confirm('确定删除该条数据吗？', {icon: 3, title:$(this).html()+'【'+data.department+'】'}, function(index){
                    $.ajax({
                        type:"POST",
                        url:url,
                        data:params,
                        //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                        beforeSend:function(){
                            layer.load(1);
                        },
                        //成功返回之后调用的函数
                        success:function(data){
                            layer.closeAll('loading');
                            if(data.status == 1){
                                layer.msg(data.msg,{icon : 1,time:1000},function(){
                                    table.reload('batchDepartLists', {
                                        url: batchAddDepartment
                                        ,where: {"type":"getData"}
                                        ,page: {
                                            curr: 1 //重新从第 1 页开始
                                        }
                                    });
                                });
                            }else{
                                layer.msg(data.msg,{icon : 2});
                            }
                        },
                        //调用出错执行的函数
                        error: function(){
                            //请求出错处理
                            layer.msg('服务器繁忙', {icon: 5});
                        }
                    });
                    layer.close(index);
                });
            }else if(layEvent === 'hospital_code'){
                var olddata = removeHtmlTag(data.hospital_code);
                layer.prompt({
                    formType: 2
                    ,title: '修改科室名称为 【'+ data.department +'】 的医院代码'
                    ,value: olddata
                }, function(value, index){
                    if(!$.trim(value)){
                        layer.msg('医院代码不能为空',{icon : 2,time:2000});
                        return false;
                    }
                    params[obj.event] = value;
                    //更新数据库
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: url,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status == 1) {
                                layer.close(index);
                                //同步更新表格和缓存对应的值
                                obj.update({
                                    hospital_code: value
                                });
                            }else{
                                layer.msg(data.msg,{icon : 2,time:2000});
                            }
                        },
                        error: function () {
                            layer.msg("网络访问失败",{icon : 2,time:2000});
                        }
                    });
                });
            }else if(layEvent === 'departnum'){
                var olddata = removeHtmlTag(data.departnum);
                layer.prompt({
                    formType: 2
                    ,title: '修改科室名称为 【'+ data.department +'】 的科室编码'
                    ,value: olddata
                }, function(value, index){
                    if(!$.trim(value)){
                        layer.msg('科室编码不能为空',{icon : 2,time:2000});
                        return false;
                    }
                    params[obj.event] = value;
                    //更新数据库
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: url,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status == 1) {
                                layer.close(index);
                                //同步更新表格和缓存对应的值
                                obj.update({
                                    departnum: value
                                });
                            }else{
                                layer.msg(data.msg,{icon : 2,time:2000});
                            }
                        },
                        error: function () {
                            layer.msg("网络访问失败",{icon : 2,time:2000});
                        }
                    });
                });
            }else if(layEvent === 'department'){
                var olddata = removeHtmlTag(data.department);
                layer.prompt({
                    formType: 2
                    ,title: '修改科室名称为 【'+ data.department +'】 的科室名称'
                    ,value: olddata
                }, function(value, index){
                    if(!$.trim(value)){
                        layer.msg('科室编码不能为空',{icon : 2,time:2000});
                        return false;
                    }
                    params[obj.event] = value;
                    //更新数据库
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: url,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status == 1) {
                                layer.close(index);
                                //同步更新表格和缓存对应的值
                                obj.update({
                                    department: value
                                });
                            }else{
                                layer.msg(data.msg,{icon : 2,time:2000});
                            }
                        },
                        error: function () {
                            layer.msg("网络访问失败",{icon : 2,time:2000});
                        }
                    });
                });
            }
        });
        //监听单元格编辑
        table.on('edit(batchDepartData)', function(obj){
            var value = $.trim(obj.value); //得到修改后的值
            var data = obj.data; //得到所在行所有键值
            var cloum = $.trim(obj.field); //得到字段
            var index = data.LAY_TABLE_INDEX + 1;
            var flag = true;
            var params = {};
            params['tempid'] = data.tempid;
            params[cloum] = value;
            params.type = 'updateData';
            //不能为空的字段
            var keyvalue = [];
            keyvalue['address']      = '科室位置';
            keyvalue['departrespon'] = '科室负责人';
            keyvalue['assetsrespon'] = '设备负责人';
            keyvalue['departtel']    = '科室电话';
            if(!value){
                for(var item in keyvalue){
                    if(item == cloum){
                        layer.msg('修改失败！'+keyvalue[cloum]+'不能为空！',{icon : 2,time:2000});
                        flag = false;
                        return false;
                    }
                }
            }
            if (params.departtel){
                if (!checkTel(params.departtel)){
                    layer.msg('科室电话号码格式不正确',{icon : 2,time:2000});
                    flag = false;
                }
            }
            if (flag){
                //更新数据库
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: batchAddDepartment,
                    data: params,
                    dataType: "json",
                    success: function (data) {
                        if (data.status != 1) {
                            layer.msg(data.msg,{icon : 2,time:2000});
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败",{icon : 2,time:2000});
                    }
                });
                return false;
            }
        });
        //批量删除数据
        $('#batchDel').on('click',function(){
            var checkStatus = table.checkStatus('batchDepartLists');
            var data = checkStatus.data;
            if(data.length == 0){
                layer.msg('请选择要删除的数据！',{icon : 2,time:1000});
                return false;
            }
            var tempid = '';
            var params = {};
            params.type = 'delTmpDeparts';
            for(j = 0,len=data.length; j < len; j++) {
                tempid += data[j]['tempid']+',';
            }
            params.tempid = tempid;
            layer.confirm('确定删除选中的数据吗？', {icon: 3, title:'批量删除数据'}, function(index){
                $.ajax({
                    type:"POST",
                    url:batchAddDepartment,
                    data:params,
                    beforeSend:function(){
                        layer.load(1);
                    },
                    //成功返回之后调用的函数
                    success:function(data){
                        layer.closeAll('loading');
                        if(data.status == 1){
                            layer.msg(data.msg,{icon : 1,time:1000},function(){
                                table.reload('batchDepartLists', {
                                    url: batchAddDepartment
                                    ,where: {"type":"getData"}
                                    ,page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            });
                        }else{
                            layer.msg(data.msg,{icon : 2});
                        }
                    },
                    //调用出错执行的函数
                    error: function(){
                        //请求出错处理
                        layer.msg('服务器繁忙', {icon: 5});
                    }
                });
                layer.close(index);
            });
        });
        //保存选中数据
        $('#uploadSel').on('click',function(){
            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
            var checkStatus = table.checkStatus('batchDepartLists');
            var data = checkStatus.data;
            if(data.length == 0){
                layer.msg('请选择要保存的数据！',{icon : 2,time:1000});
                return false;
            }
            var tempid = '';
            var params = {};
            params.type = 'save';
            for(j = 0,len=data.length; j < len; j++) {
                tempid += data[j]['tempid']+',';
            }
            params.tempid = tempid;
            $.ajax({
                type:"POST",
                url:batchAddDepartment,
                data:params,
                beforeSend:function(){
                    layer.load(1);
                },
                //成功返回之后调用的函数
                success:function(data){
                    layer.closeAll('loading');
                    if(data.status == 1){
                        layer.msg(data.msg,{icon : 1,time:2000},function(){
                            table.reload('batchDepartLists', {
                                url: batchAddDepartment
                                ,where: {"type":"getData"}
                                ,page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        });
                    }else{
                        layer.msg(data.msg,{icon : 2});
                    }
                },
                //调用出错执行的函数
                error: function(){
                    //请求出错处理
                    layer.msg('服务器繁忙', {icon: 5});
                }
            });
            layer.close(index);
        });

        //保存当页数据
        $('#uploadAll').on('click',function(){
            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
            var tempid = '';
            var params = {};
            params.type = 'save';
            var tr = $('.layui-table-main').find('tr');
            $.each(tr,function(){
                $.each($(this).find('td'),function () {
                    if($(this).attr('data-field') == 'tempid'){
                        tempid += ($(this).attr('data-content'))+',';
                    }
                });
            });
            if(tempid == ''){
                layer.msg('没有要保存的数据！',{icon : 2,time:1000});
                return false;
            }
            params.tempid = tempid;
            $.ajax({
                type:"POST",
                url:batchAddDepartment,
                data:params,
                //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                beforeSend:function(){
                    layer.load(1);
                },
                //成功返回之后调用的函数
                success:function(data){
                    layer.closeAll('loading');
                    if(data.status == 1){
                        layer.msg(data.msg,{icon : 1,time:2000},function(){
                            table.reload('batchDepartLists', {
                                url: batchAddDepartment
                                ,where: {"type":"getData"}
                                ,page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        });
                    }else{
                        layer.msg(data.msg,{icon : 2});
                    }
                },
                //调用出错执行的函数
                error: function(){
                    //请求出错处理
                    layer.msg('服务器繁忙', {icon: 5});
                }
            });
            layer.close(index);
        });
    });
    exports('controller/basesetting/integratedSetting/batchadddepartment', {});
});

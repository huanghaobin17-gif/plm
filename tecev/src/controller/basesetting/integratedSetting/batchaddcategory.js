layui.define(function(exports){
    layui.use(['table', 'upload', 'form', 'tablePlug'], function () {
        var $ = layui.jquery, upload = layui.upload;
        var table = layui.table;
        var form = layui.form;
        var layer = layui.layer, tablePlug = layui.tablePlug;
        //执行实例
        var uploadInst = upload.render({
            elem: '#uploadCatesFile', //绑定元素
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
                        table.reload('batchCatesLists', {
                            url: batchAddCategory
                            ,where: {"type":"getData"}
                            ,page: {
                                curr: 1 //重新从第 1 页开始
                            }
                        });
                    });
                }else{
                    layer.msg(res.msg,{icon : 2,time:3000});
                }
            }
            , error: function () {
                //请求异常回调
                layer.closeAll('loading');
                layer.msg('网络异常，请稍后再重试！',{icon : 2},1000);
            }
        });
        //到出模板
        $("#exploreCatesModel").on('click', function () {
            window.location.href = 'batchAddCategory.html?type=exploreCatesModel';
        });
        //获取数据
        table.render({
            elem: '#batchCatesLists'
            , limits: [600]
            , loading: true
            , limit: 600
            , url: batchAddCategory //数据接口
            , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            , where: {
                sort: 'parentid'
                , order: 'asc'
                , type: 'getData'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'tempid' //排序字段，对应 cols 设定的各字段名
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
                    width: 60,
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
                    width: 120,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'fcatenum',
                    title: '<i style="color: red;">* </i>父分类编号',
                    width: 120,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'scatenum',
                    title: '<i style="color: red;">* </i>子分类编号',
                    width: 120,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'category',
                    title: '<i style="color: red;">* </i>分类名称',
                    width: 260,
                    edit: 'text',
                    event: 'category',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'remark',
                    title: '品名举例',
                    width: 260,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }, {field: 'operation', title: '操作', fixed: 'right', minWidth: 60, align: 'center'}
            ]]
            , done: function (res, curr, count) {
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
            }
        });
        //监听排序
        table.on('sort(batchCatesData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('batchCatesLists', {
                where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: 'parentid'
                    , order: 'asc'
                    , type: 'getData'
                }
            });
        });
        //监听工具条
        table.on('tool(batchCatesData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            console.log(66);
            var data = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = batchAddCategory;
            var params = {};
            params['tempid'] = data.tempid;
            params['type']   = 'delTmpData';
            if(layEvent === 'delCategory'){ //删除
                //do something
                layer.confirm('如删除父类数据，会把其子类的数据一并删除，确定删除选中的数据吗？', {icon: 3, title:$(this).html()+'【'+data.category+'】'}, function(index){
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
                                    table.reload('batchCatesLists', {
                                        url: batchAddCategory
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
            }
        });
        //监听单元格编辑
        table.on('edit(batchCatesData)', function(obj){
            var value = $.trim(obj.value); //得到修改后的值
            var data = obj.data; //得到所在行所有键值
            var cloum = $.trim(obj.field); //得到字段
            var index = data.LAY_TABLE_INDEX + 1;
            var params = {};
            params['tempid'] = data.tempid;
            params[cloum] = value;
            params.type = 'updateData';
            //更新数据库
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: batchAddCategory,
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status == 1) {
                        if(cloum == 'fcatenum'){
                            table.reload('batchCatesLists', {
                                where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                                    sort: 'parentid'
                                    , order: 'asc'
                                    , type: 'getData'
                                }
                            });
                        }
                    }else{
                        layer.msg(data.msg,{icon : 2,time:2500},function () {
                            table.reload('batchCatesLists', {
                                where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                                    sort: 'parentid'
                                    , order: 'asc'
                                    , type: 'getData'
                                }
                            });
                        });
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2,time:2000});
                }
            });
        });
        //监听表格复选框选择
        // table.on('checkbox(batchAssetsData)', function(obj){
        //     console.log(obj)
        // });
        var $ = layui.$, active = {
            getCheckData: function(){ //获取选中数据
                var checkStatus = table.checkStatus('batchCatesLists');
                var data = checkStatus.data;
                layer.alert(JSON.stringify(data));
            }
            ,getCheckLength: function(){ //获取选中数目
                var checkStatus = table.checkStatus('batchCatesLists')
                    ,data = checkStatus.data;
                layer.msg('选中了：'+ data.length + ' 个');
            }
            ,isAll: function(){ //验证是否全选
                var checkStatus = table.checkStatus('batchCatesLists');
                layer.msg(checkStatus.isAll ? '全选': '未全选')
            }
        };
        //批量删除数据
        $('#batchDel').on('click',function(){
            var checkStatus = table.checkStatus('batchCatesLists');
            var data = checkStatus.data;
            if(data.length == 0){
                layer.msg('请选择要删除的数据！',{icon : 2,time:1000});
                return false;
            }
            var tempid = '';
            var params = {};
            params.type = 'delTmpData';
            for(j = 0,len=data.length; j < len; j++) {
                tempid += data[j]['tempid']+',';
            }
            params.tempid = tempid;
            console.log(params);
            layer.confirm('如删除父类数据，会把其子类的数据一并删除，确定删除选中的数据吗？', {icon: 3, title:'批量删除数据'}, function(index){
                $.ajax({
                    type:"POST",
                    url:batchAddCategory,
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
                                table.reload('batchCatesLists', {
                                    url: batchAddCategory
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
            var checkStatus = table.checkStatus('batchCatesLists');
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
                url:batchAddCategory,
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
                            table.reload('batchCatesLists', {
                                url: batchAddCategory
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
                url:batchAddCategory,
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
                            table.reload('batchCatesLists', {
                                url: batchAddCategory
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
    exports('controller/basesetting/integratedSetting/batchaddcategory', {});
});

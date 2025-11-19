layui.define(function(exports){
    layui.use(['table', 'upload', 'form', 'laydate', 'tablePlug'], function () {
        var $ = layui.jquery, upload = layui.upload;
        var table = layui.table;
        var form = layui.form;
        var layer = layui.layer;
        var laydate = layui.laydate
            , tablePlug = layui.tablePlug;
        //先更新页面部分需要提前渲染的控件
        form.render();
        laydate.render({
            elem: '#next_date_input',
            festival: true,
            min: '2'
        });
        //获取数据
        var tablens = table.render({
            elem: '#batchSaveMeteringList'
            , limits: [2000]
            , loading: true
            , limit: 2000
            , url: admin_name+'/Metering/batchSaveMetering.html' //数据接口
            , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            , where: {
                order: 'desc'
                , type: 'batchEditGetData'
                , mpid: mpid
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                type: 'desc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
            , cols: [header]
            , done: function (res, curr, count) {
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
            }
        });
        //监听选择框变换
        form.on('select(showSelectFields)',function (data) {
            var params = {};
            params.mpid = mpid;
            params.showFields = data.value;
            if(!data.value){
                return false;
            }
            params.type = 'getFieldsData';
            params.type2 = 'getHeader';
            params.title = $('select[name="fields"]').find("option:selected").text();
            var flag = false;
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Metering/batchSaveMetering.html',
                data: params,
                dataType: "json",
                beforeSend:beforeSend,
                async:false,
                success: function (data) {
                    if (data.status == 1) {
                        flag = true;
                        header = data.header;
                        params.type2 = '';
                        tablens.reload( {
                            url: admin_name+'/Metering/batchSaveMetering.html'
                            ,where: params
                            ,page: {
                                curr: 1 //重新从第 1 页开始
                            }
                            ,cols: [ //表头
                                header
                            ]
                        });
                    }else{
                        layer.msg(data.msg,{icon : 2,time:1000});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                },
                complete:complete
            });
            if(flag){
                //显示修改区域代码
                $('.not-show').hide();
                $('#'+data.value).show();
            }
        });
        form.on('submit(saveEdit)',function (data) {
            var rfParams = {};
            rfParams.mpid = mpid;
            rfParams.type = 'batchEditGetData';
            rfParams.title = $('select[name="fields"]').find("option:selected").text();
            var cloum = data.field; //得到字段
            var params = {};
            params['mpid'] = mpid;
            params['field'] = cloum;
            params['type'] = 'batchEditUpdateData';
            //更新数据库
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Metering/batchSaveMetering.html',
                data: params,
                dataType: "json",
                success: function (res) {
                    if (res.status == 1) {
                        rfParams.showFields = res.field;
                        layer.msg(res.msg,{icon : 1,time:1000},function () {
                            tablens.reload( {
                                url: admin_name+'/Metering/batchSaveMetering.html'
                                ,where: rfParams
                                ,page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                                ,cols: [ //表头
                                    header
                                ]
                            });
                        });
                    }else{
                        layer.msg(res.msg,{icon : 2,time:2000});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2,time:2000});
                }
            });
        });
        //新增分类
        $('#addMCategory').click(function () {
            console.log(1);
            layer.open({
                type: 1,
                title: '新增计量分类',
                area: ['450px', '300px'],
                offset: 'auto',
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#addMCategoryBody'),
                end: function () {
                    $('textarea[name="categorysTitle"]').val('');
                }
            });
        });
        //确认添加分类
        form.on('submit(addCategorys)', function (data) {
            if (!data.field.categorysTitle) {
                layer.msg("请输入分类名称", {icon: 2}, 1000);
                return false;
            }
            var categorysTitle=data.field.categorysTitle.split("\n");
            data.field.categorysTitle='';
            $.each(categorysTitle,function (k,v) {
                if(v){
                    data.field.categorysTitle+=','+v;
                }
            });
            var params = {};
            params.categorys = data.field.categorysTitle;
            params.type = 'addCategorys';
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Metering/addMetering',
                data: params,
                dataType: "json",
                beforeSend: beforeSend,
                success: function (data) {
                    if (data.status === 1) {
                        layer.msg(data.msg, {
                            icon: 1,
                            time: 1000
                        }, function () {
                            var html = '';
                            $.each(data.result, function (key, value) {
                                html += '<option value="' + value.mcid + '">' + value.mcategory + '</option>';
                            });
                            $('select[name="mcategory"]').html(html);
                            form.render('select');
                        });
                    }else{
                        layer.msg(data.msg, {icon: 2}, 1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2}, 1000);
                },
                complete: complete
            });
            return false;
        });
    });
    exports('controller/ametering/metering/batchSaveMetering', {});
});

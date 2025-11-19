layui.define(function(exports){

    layui.use(['table', 'upload', 'form', 'laydate', 'tablePlug'], function () {
        var $ = layui.jquery, upload = layui.upload;
        var table = layui.table;
        var form = layui.form;
        var layer = layui.layer;
        var laydate = layui.laydate
            , tablePlug = layui.tablePlug;

        //日期
        laydate.render(dateConfig('#date'));
        laydate.render(dateConfig('#date1'));
        laydate.render(dateConfig('#date2'));
        laydate.render(dateConfig('#date3'));
        laydate.render(dateConfig('#date10'));

        //先更新页面部分需要提前渲染的控件
        form.render();
        //获取数据
        var tablens = table.render({
            elem: '#batchEditAssetsLists_l'
            , limits: [2000]
            , loading: true
            , limit: 2000
            , url: admin_name+'/Lookup/batchEditAssets.html' //数据接口
            , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            , where: {
                sort: 'A.adddate'
                , order: 'desc'
                , type: 'batchEditGetData'
                , assid: assid
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'assid' //排序字段，对应 cols 设定的各字段名
                , type: 'desc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
            params.assid = assid;
            params.showFields = data.value;
            params.type = 'batchEditGetData';
            params.type2 = 'getHeader';
            params.title = $('select[name="fields"]').find("option:selected").text();
            var flag = false;
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Lookup/batchEditAssets.html',
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
                            url: admin_name+'/Lookup/batchEditAssets.html'
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
            rfParams.assid = assid;
            rfParams.type = 'batchEditGetData';
            rfParams.title = $('select[name="fields"]').find("option:selected").text();

            var cloum = data.field; //得到字段
            var params = {};
            params['assid'] = assid;
            params['field'] = cloum;
            params['type'] = 'batchEditUpdateData';
            //更新数据库
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Lookup/batchEditAssets.html',
                data: params,
                dataType: "json",
                success: function (res) {
                    if (res.status == 1) {
                        rfParams.showFields = res.field;
                        layer.msg(res.msg,{icon : 1,time:1000},function () {
                            tablens.reload( {
                                url: admin_name+'/Lookup/batchEditAssets.html'
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
    });
    exports('controller/assets/lookup/batchedit', {});
});

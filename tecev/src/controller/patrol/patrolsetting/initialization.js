layui.define(function(exports){
    //判断搜索建议的位置
    position = '';
    if (Math.floor($("#LAY-patrol-patrolsetting-initialization .layui-form-item").width()/$("#LAY-patrol-patrolsetting-initialization .layui-inline").width()) == 3){
        position = '';
    }else {
        position = 1;
    }
    function getType() {
        var type = '';
        $("input[name='type']:checked").each(function () {
            type += ',' + $(this).val();
        });
        if (type.substr(0, 1) == ',') {
            type = type.substr(1);
        }
        return type;
    }

    layui.use(['layer', 'form', 'table', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, table = layui.table, suggest = layui.suggest, tablePlug = layui.tablePlug;

        //初始化搜索建议插件
        suggest.search();

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();
        //定义一个全局空对象
        var gloabOptions = {};
        //数据表格
        table.render({
            elem: '#initialization'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,title: '设备初始化模板列表'
            ,url: admin_name+'/PatrolSetting/initialization' //数据接口
            ,where: {
                type:getType()
                ,sort: 'assid'
                ,order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            ,initSort: {
                field: 'assid' //排序字段，对应 cols 设定的各字段名
                ,type: 'desc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            }
            ,method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            ,request: {
                pageName: 'page' //页码的参数名称，默认：page
                ,limitName: 'limit' //每页数据量的参数名，默认：limit
            } //如果无需自定义请求参数，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            , page: {
                groups: 10 //只显示 1 个连续页码
                ,prev:'上一页'
                ,next:'下一页'
            },
            toolbar: '#LAY-patrol-patrolsetting-initializationToolbar',
            defaultToolbar: ['filter','exports']
            ,cols: [[ //表头
                {
                    type: 'checkbox', fixed: 'left'
                }, {
                    field: 'assid',
                    title: '序号',
                    width: 60,
                    fixed: 'left',
                    align: 'center',
                    style: 'background-color: #f9f9f9;',
                    type:  'space',
                    unresize:false,
                    templet: function(d){
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'assnum',
                    hide: get_now_cookie(userid + cookie_url + '/assnum') == 'false' ? true : false,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    title: '设备编号',
                    width: 140,
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'assets',
                    title: '设备名称',
                    hide: get_now_cookie(userid+cookie_url+'/assets')=='false'?true:false,
                    width: 160,
                    unresize: false,
                    sort: true,
                    align: 'center'
                }
                , {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格/型号', width: 120, unresize: false, align: 'center'}
                , {field: 'category',hide: get_now_cookie(userid+cookie_url+'/category')=='false'?true:false, title: '设备分类', width: 150, unresize: false, align: 'center'}
                , {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '使用科室', width: 100, unresize: false, align: 'center'}
                , {field: 'buy_price',hide: get_now_cookie(userid+cookie_url+'/buy_price')=='false'?true:false, title: '原值(元)', width: 100, unresize: false, align: 'center'}
                , {field: 'assetstype',hide: get_now_cookie(userid+cookie_url+'/assetstype')=='false'?true:false, title: '设备类型', width: 100, unresize: false, align: 'center'}
                , {field: 'maintenance',hide: get_now_cookie(userid+cookie_url+'/maintenance')=='false'?true:false, title: '维保状态', width: 100, unresize: false, align: 'center'}
                , {
                    field: 'name',
                    hide: get_now_cookie(userid + cookie_url + '/name') == 'false' ? true : false,
                    title: '保养模板名称',
                    width: 220,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'default_name',
                    hide: get_now_cookie(userid + cookie_url + '/default_name') == 'false' ? true : false,
                    title: '默认模板名称',
                    width: 140,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'operation',
                    style: 'background-color: #f9f9f9;',
                    title: '操作',
                    fixed: 'right',
                    minWidth: 70,
                    align: 'center'
                }
            ]]
        });

        form.on('checkbox', function(data){
            var type=$(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
            var key=data.elem.name;
            var status=data.elem.checked;
            document.cookie=userid+cookie_url+'/'+key+'='+status+"; expires=Fri, 31 Dec 9999 23:59:59 GMT";
        }
           // 
        });

        //操作栏按钮
        table.on('tool(initializationData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent){
                case 'settingTemplate'://编辑主设备详情
                    top.layer.open({
                        id: 'settingTemplates',
                        type: 2,
                        title: '设定模板【' + rows.assets + '】',
                        area: ['75%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?assid='+rows.assid+'&assnum='+rows.assnum],
                        end: function () {
                            if(flag){
                                table.reload('initialization', {
                                    url: admin_name+'/PatrolSetting/initialization'
                                    ,where: gloabOptions
                                    ,page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            }
                        },
                        cancel:function(){
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
            }
        });

        //列排序
        table.on('sort(initializationData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('initialization', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    type:getType()
                    ,sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });

        //搜索按钮
        form.on('submit(initializationSearch)', function(data){
            gloabOptions = data.field;
            gloabOptions.type=getType();
            var table = layui.table;
            table.reload('initialization', {
                url: admin_name+'/PatrolSetting/initialization'
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        table.on('toolbar(initializationData)', function(obj){
            var event =  obj.event,
                url = $(this).attr('data-url'),
                flag = 1;

            switch(event){
                case 'batchSetttingTemplate'://批量设定模板
                    var table = layui.table;
                    var checkStatus = table.checkStatus('initialization');
                    //获取选中行数量，可作为是否有选中行的条件
                    var length = checkStatus.data.length;
                    if(length == 0){
                        top.layer.msg('请先至少勾选一个设备',{icon:2});
                        return false;
                    }
                    var assnum = '';
                    var assid = '';
                    var tpid = '';
                    $.each(checkStatus.data,function(j,val){
                        tpid += ','+val.tpid;
                        assid += ','+val.assid;
                        assnum += ','+val.assnum;
                    });
                    top.layer.open({
                        id: 'batchSetttingTemplates',
                        type: 2,
                        title: $(this).html(),
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        area: ['75%', '100%'],
                        maxmin:true,
                        closeBtn: 1,
                        content:[url+'?assid='+assid+'&tpid='+tpid+'&assnum='+assnum],
                        end: function () {
                            if(flag){
                                table.reload('initialization', {
                                    url: admin_name+'/PatrolSetting/initialization'
                                    ,where: gloabOptions
                                    ,page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            }
                        },
                        cancel:function(){
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
            }
        });

        //设备名称搜索建议
        $("#initializationAssets").bsSuggest(
            returnAssets()
        );

        //设备编号搜索建议
        $("#initializationAssnum").bsSuggest(
            returnAssnum()
        );

        //科室搜索建议
        $("#initializationDepartment").bsSuggest(
            returnDepartment()
        );

        //分类搜索建议
        $("#initializationCategory").bsSuggest(
            returnCategory('',position)
        );
        //急救资产多选框
        form.on('checkbox(isfirstaid)', function(data){
            if (data.elem.checked == true){
                $("input[name='checkFirstaid']").val('1')
            }else {
                $("input[name='checkFirstaid']").val('')
            }
        });
        //特种资产多选框
        form.on('checkbox(isspecial)', function(data){
            if (data.elem.checked == true){
                $("input[name='checkSpecial']").val('1')
            }else {
                $("input[name='checkSpecial']").val('')
            }
        });
    });
    exports('patrol/patrolsetting/initialization', {});
});
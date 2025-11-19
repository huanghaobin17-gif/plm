layui.define(function(exports){
    layui.use(['admin', 'layer', 'form', 'formSelects', 'laydate', 'table', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, formSelects = layui.formSelects, laydate = layui.laydate,
            table = layui.table, suggest = layui.suggest, tablePlug = layui.tablePlug;
        //渲染多选下拉
        formSelects.render();
        //初始化搜索建议插件
        suggest.search();

        layer.config(layerParmas()); 

        formSelects.render('suppliers_type', selectParams(1));
        formSelects.btns('suppliers_type', selectParams(2));

        //先更新页面部分需要提前渲染的控件
        form.render();
        //录入时间元素渲染
        laydate.render({
            elem: '#stat-date' //指定元素
        });
        laydate.render({
            elem: '#over-date' //指定元素
        });


        var rowspandiv = '<table class="certificate_table"><tr><td colspan="3" class="QualificationsTd">厂商资质</td></tr><tr><td class="licenceTd">证照名称</td><td class="situationTd">已提交</td><td class="termTd">有效期至</td></tr></table>';
        //定义一个全局空对象
        var gloabOptions = {};
        table.render({
            elem: '#offlineSuppliersList'
            //,height: '600'
            , limits: [5,10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: offlineSuppliersListUrl //数据接口
            , where: {
                sort: 'olsid',
                order: 'DESC'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'olsid' //排序字段，对应 cols 设定的各字段名
                , type: 'DESC' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
            , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            //,page: true //开启分页
            , toolbar: '#LAY-OfflineSuppliers-OfflineSuppliers-offlineSuppliersListbar'
            , defaultToolbar: false
            , cols: [[ //表头
                {type: 'checkbox', fixed: 'left',unresize: true,width: 65,align: 'center',style: 'background-color: #f9f9f9;'},
                {field: 'olsid', fixed: 'left',title: '序号',width: 65,align: 'center',type: 'space',templet: function (d) {return d.LAY_INDEX;}},
                {field: 'sup_num', title: '厂商编号', width: 100, align: 'center'},
                {field: 'sup_name', title: '厂商名称', width: 300, align: 'center'},
                {field: 'suppliers_type', title: '厂商类型', width: 250, align: 'center'},
                {field: 'salesman_name', title: '业务联系人', width: 120, align: 'center'},
                {field: 'salesman_phone', title: '业务联系人电话', width: 150, align: 'center'},
                //{field: '', title: '综合评价', width: 100, align: 'center'},
                {field: 'filetd', title: rowspandiv, align: 'center', width: 510},
                {field: 'operation', title: '操作', minWidth: 120, fixed: 'right', align: 'center'}
            ]]
            , done: function (res) {
                //避免因为选项卡原因(会出现很多同名的元素)获取到其他页面的元素 先找到对应页面的父元素
                var thisTableList=$('#LAY-OfflineSuppliers-offlineSuppliersList');
                var filetd=thisTableList.find("td[data-field='filetd']");
                var t_height='0';
                if(filetd){  
                    $.each(filetd, function (key, value) {
                        var html='';
                        if(res.rows[key].file){
                            html = '<table class="table_center_td certificate_table">';
                            $.each(res.rows[key].file, function (result_k, result_v) {
                                html += '<tr><td class="licenceTd">' + result_v.licence + '</td><td class="situationTd">' + result_v.situation + '</td><td class="termTd">' + result_v.term + '</td></tr>';
                            });
                            html += '</table>';
                        }else{
                            html = '<table class="table_center_td certificate_table"><tr><td class="licenceTd">&nbsp;</td><td class="situationTd"></td><td class="termTd"></td></tr>';
                        }
                        $(value).find('div').html(html);
                        $(value).css('padding', 0);
                        //获取精确的表格高度
                        var height = $(thisTableList).find("td[data-field='filetd']")[key].getBoundingClientRect().height.toFixed(2);
                        t_height = Number(t_height)+Number(height);
                        height = height + 'px';
                        $(thisTableList.find('.layui-table-fixed-r').find('td')[key]).css('height', height);
                        key = key*2+1;
                        $(thisTableList.find('.layui-table-fixed-l').find('td')[key]).css('height', height);
                    });
                    $(thisTableList.find('.layui-table-fixed-r')).css('height', '100%');
                        $(thisTableList.find('.layui-table-fixed-r')).css('height', '100%');
                        $(thisTableList.find('.layui-table-fixed-l .layui-table-body')).css('height', t_height + 'px');
                        $(thisTableList.find('.layui-table-fixed-r .layui-table-body')).css('height', t_height + 'px');
                }
                var table = $('.QualificationsTd').parents('th');
                table.css('padding', 0);
            }

        });


        table.on('tool(offlineSuppliersData)', function (obj) {
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event;
            var flag = 1;
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'editSuppliers'://编辑主设备详情
                    top.layer.open({
                        id: 'editSuppliers',
                        type: 2,
                        title: '编辑厂商信息【' + rows.sup_name + '】',
                        area: ['790px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?olsid=' + rows.olsid],
                        end: function () {
                            if (flag) {
                                table.reload('offlineSuppliersList', {
                                    url: offlineSuppliersListUrl
                                    , where: gloabOptions
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            }
                        },
                        cancel: function () {
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
                case 'showDetails':
                    top.layer.open({
                        id: 'showSuppliersDetails',
                        type: 2,
                        title: '查看厂家信息【' + rows.sup_name + '】',
                        area: ['790px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url]
                    });
                    break;
                case 'delSuppliers':
                    layer.confirm('确定删除厂商【'+rows.sup_name+'】？', {icon: 3, title: '删除厂商'}, function (index) {
                        var params = {};
                        params['olsid'] = rows.olsid;
                        $.ajax({
                            type: "POST",
                            url: url,
                            data: params,
                            //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                            beforeSend: function () {
                                layer.load(1);
                            },
                            //成功返回之后调用的函数
                            success: function (data) {
                                if (data.status === 1) {
                                    layer.msg(data.msg, {icon: 1, time:1500}, function () {
                                        table.reload('offlineSuppliersList', {
                                            url: offlineSuppliersListUrl
                                            , where: gloabOptions
                                            , page: {
                                                curr: 1 //重新从第 1 页开始
                                            }
                                        });
                                    });
                                } else {
                                    layer.msg(data.msg, {icon: 2});
                                }
                            },
                            //调用出错执行的函数
                            error: function () {
                                //请求出错处理
                                layer.msg('服务器繁忙', {icon: 5});
                            },
                            complete: function () {
                                layer.closeAll('loading');
                            }
                        });
                        layer.close(index);
                    });
                    break;
            }
        });

        table.on('toolbar(offlineSuppliersData)', function (obj) {
            var event = obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch (event) {
                case 'addSuppliers'://新增厂家
                    top.layer.open({
                        id: 'addSuppliers',
                        type: 2,
                        title: $(this).html(),
                        scrollbar: false,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['790px', '100%'],
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            if (flag) {
                                table.reload('offlineSuppliersList', {
                                    url: offlineSuppliersListUrl
                                    , where: gloabOptions
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            }
                        },
                        cancel: function () {
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
                case 'editOfflineSupplier'://修改所有厂商的类型为全类型
                var checkStatus = table.checkStatus('offlineSuppliersList');
                    //获取选中行数量，可作为是否有选中行的条件
                var length = checkStatus.data.length;
                if (length == 0) {
                   top.layer.msg('请选择要修改的厂商', {icon: 2});
                   return false;
                }
                    var id = '';
                    for (var i = 0; i < length; i++) {
                        var tmpId = checkStatus.data[i]['olsid'];
                        id += tmpId + ',';
                    }
                    id = id.substring(0, id.length - 1);
                $('#olsids').attr('value',id);
                layer.open({
                    id: 'oldtypes',
                    type: 1,
                    title: '批量修改厂商类型',
                    area: ['450px', '400px'],
                    offset: 'auto',
                    shade: false,
                    shadeClose:true,
                    anim:5,
                    resize:false,
                    scrollbar:false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: $('#oldtype'),
                    end: function () {
                        //console.log('end');
                    },
                    success: function(layero, index){
                        //layer.close(index);
                        console.log(layero);
                        console.log(index);
                    }
                });
                break;
            }
        });
        //监听提交厂商类型
            form.on('submit(saveold)', function(data){
                var p = data.field;
                console.log(p);
                if(!p.olsids){
                    layer.msg("请选择要修改的厂商",{icon : 2,time:2000});
                    return false;
                }
                if(!p.suppliers_type){
                    layer.msg("请选择要修改的类型",{icon : 2,time:2000});
                    return false;
                }
                p.action ='savetype';
                var url = admin_name+'/OfflineSuppliers/editOfflineSupplier.html';
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: url,
                    data: p,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg,{icon : 1,time:1000},function(){ 
                                layer.closeAll('page');
                                table.reload('offlineSuppliersList', {
                                    url: offlineSuppliersListUrl
                                    , where: gloabOptions
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            });
                        }else{
                            layer.msg(data.msg,{icon : 2,time:2000});
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败",{icon : 2,time:2000});
                    }
                });
                return false;
            });

        //搜索按钮
        form.on('submit(offlineSuppliersListSearch)', function(data){
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('offlineSuppliersList', {
                url: offlineSuppliersListUrl
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //厂商名称搜索建议
        $("#auppliersListSupName").bsSuggest(
            getOfflineSuppliersName('')
        );
    });
    exports('offlineSuppliers/offlineSuppliers/offlineSuppliersList', {});
});

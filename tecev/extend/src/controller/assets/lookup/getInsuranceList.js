layui.define(function(exports){
    var gloabOptions = {};
    function getInsurance() {
        return $('input[name="insurance"]').val();
    }
    function getGuarantee() {
        return $('input[name="guarantee"]').val();
    }
    layer.config(layerParmas());

    layui.use(['table', 'suggest', 'laydate', 'form', 'formSelects', 'tablePlug'], function () {
        var table = layui.table, suggest = layui.suggest, laydate = layui.laydate, form = layui.form, formSelects = layui.formSelects, tablePlug = layui.tablePlug;
        laydate.render(dateConfig('#getInsuranceListBuyStartDate'));
        laydate.render(dateConfig('#getInsuranceListBuyEndDate'));
        laydate.render(dateConfig('#getInsuranceListStartDate'));
        laydate.render(dateConfig('#getInsuranceListEndDate'));
        //初始化搜索建议插件
        suggest.search();
        //渲染
        form.render();

        //渲染所有多选下拉
        formSelects.render('getInsuranceListDepartment', selectParams(1));
        formSelects.btns('getInsuranceListDepartment', selectParams(2), selectParams(3));

        //第一个实例
        table.render({
            elem: '#getInsuranceList'
            //,height: '600'
            , limits: [20, 50, 100]
            , loading: true
            , limit: 20
            ,title: '设备参保列表'
            , height: 'full-100' //高度最大化减去差值
            , url: getInsuranceList //数据接口
            , where: {
                insurance:getInsurance,
                guarantee:getGuarantee,
                sort: 'assid'
                , order: 'asc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'assid' //排序字段，对应 cols 设定的各字段名
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
            , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            },
            toolbar: 'true',
            defaultToolbar: ['filter','exports']
            , cols: [[ //表头
                {
                    field: 'assid',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'assnum',
                    hide: get_now_cookie(userid + cookie_url + '/assnum') == 'false' ? true : false,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    title: '设备编号',
                    width: 160,
                    align: 'center'
                }
                , {field: 'assets',hide: get_now_cookie(userid+cookie_url+'/assets')=='false'?true:false, title: '设备名称', width: 180, align: 'center'}
                , {field: 'department_name',hide: get_now_cookie(userid+cookie_url+'/department_name')=='false'?true:false, title: '使用科室', width: 160, align: 'center'}
                , {field: 'guarantee_date',hide: get_now_cookie(userid+cookie_url+'/guarantee_date')=='false'?true:false, title: '原厂保修截止', width: 140, align: 'center'}
                , {field: 'is_canbao',hide: get_now_cookie(userid+cookie_url+'/nature')=='false'?true:false, title: '是否参保', width: 100, align: 'center'}
                , {field: 'nature',hide: get_now_cookie(userid+cookie_url+'/nature')=='false'?true:false, title: '维保性质', width: 100, align: 'center'}
                , {field: 'insuranceDate',hide: get_now_cookie(userid+cookie_url+'/insuranceDate')=='false'?true:false, title: '维保日期', width: 190, align: 'center'}
                , {field: 'company',hide: get_now_cookie(userid+cookie_url+'/company')=='false'?true:false, title: '维保公司', width: 230, align: 'center'}
                , {field: 'content',hide: get_now_cookie(userid+cookie_url+'/content')=='false'?true:false, title: '维保内容', width: 300, align: 'center'}
                , {field: 'insuredsum',hide: get_now_cookie(userid+cookie_url+'/insuredsum')=='false'?true:false, title: '参保次数', width: 90, align: 'center'}
                , {field: 'contacts',hide: get_now_cookie(userid+cookie_url+'/contacts')=='false'?true:false, title: '联系人', width: 100, align: 'center'}
                , {field: 'telephone',hide: get_now_cookie(userid+cookie_url+'/telephone')=='false'?true:false, align: 'center', width: 120, title: '联系电话'}
                , {
                    field: 'operation',
                    title: '操作',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    minWidth: 130,
                    align: 'left'
                }
            ]]
            , done: function (res, curr) {
                var pages = this.page.pages;
                var thisId = '#' + this.id;
                if ($(thisId).next().find('.layui-table-main').height() > $(thisId).next().find('.layui-table-main .layui-table').height() && curr == pages) {
                    $(thisId).next().css('height', 'auto');
                    $(thisId).next().find('.layui-table-main').css('height', 'auto');
                } else if (typeof res.total === 'undefined' || typeof res.limit === 'undefined') {
                    $(thisId).next().css('height', 'auto');
                    $(thisId).next().find('.layui-table-main').css('height', 'auto');
                } else {
                    table.resize(this.id); //重置表格尺寸
                }
            }
        });
        //监听工具条
        table.on('tool(getInsuranceListData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).data('url'),flag = 1;;
            switch (layEvent) {
                case 'doRenewal':
                    top.layer.open({
                        id: 'doRenewals',
                        type: 2,
                        title: '【' + rows.assets + '】续保表单',
                        area: ['950px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        isOutAnim: true,
                        closeBtn: 1,
                        content: [url + '?assid=' + rows.assid],
                        end: function () {
                            if(flag) {
                                table.reload('getInsuranceList', {
                                    url: getInsuranceList
                                    , where: gloabOptions
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            }
                        },
                        cancel: function () {
                            flag = 0;
                        }
                    });
                    break;
                case 'showAssets'://显示主设备详情
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.assets+'】设备详情信息',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['1050px', '100%'],
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?assid='+rows.assid]
                    });
                    break;
            }

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

        //监听排序
        table.on('sort(getInsuranceListData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            table.reload('getInsuranceList', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });
        //监听表格复选框选择
        // table.on('checkbox(userData)', function(obj){
        //     console.log(obj)
        // });

        var $ = layui.$, active = {
            getCheckData: function () { //获取选中数据
                var checkStatus = table.checkStatus('userLists')
                    , data = checkStatus.data;
                layer.alert(JSON.stringify(data));
            }
            , getCheckLength: function () { //获取选中数目
                var checkStatus = table.checkStatus('userLists')
                    , data = checkStatus.data;
                layer.msg('选中了：' + data.length + ' 个');
            }
            , isAll: function () { //验证是否全选
                var checkStatus = table.checkStatus('userLists');
                layer.msg(checkStatus.isAll ? '全选' : '未全选')
            }
        };
        $('.demoTable .layui-btn').on('click', function () {
            var type = $(this).data('type');
            active[type] ? active[type].call(this) : '';
        });

        //选择设备
        $("#getInsuranceListAssName").bsSuggest(
            returnAssets()
        );

        //选择设备编号
        $("#getInsuranceListAssNum").bsSuggest(
            returnAssnum()
        );


        //监听提交
        form.on('submit(getInsuranceListSearch)', function (data) {
            var table = layui.table;
            gloabOptions = data.field;
            if (gloabOptions.startDate && gloabOptions.endDate) {
                if (gloabOptions.endDate < gloabOptions.startDate) {
                    layer.msg('维保日期设置不合理', {icon: 2});
                    return false;
                }

                if (gloabOptions.buyEndDate < gloabOptions.buyStartDate) {
                    layer.msg('设备购入日期设置不合理', {icon: 2});
                    return false;
                }
            }
            var guarantee = '';
            $("input[name='guarantee']:checked").each(function () {
                guarantee = $(this).val();
            });
            gloabOptions.guarantee=guarantee;
            //刷新表格时，默认回到第一页
            //table.set(gloabOptions);
            table.reload('getInsuranceList', {
                url: getInsuranceList
                , where: gloabOptions
                , height: 'full-100' //高度最大化减去差值
                , page: {
                    curr: 1 //重新从第 1 页开始
                }, done: function (res, curr) {
                    var pages = this.page.pages;
                    var thisId = '#' + this.id;
                    if ($(thisId).next().find('.layui-table-main').height() > $(thisId).next().find('.layui-table-main .layui-table').height() && curr == pages) {
                        $(thisId).next().css('height', 'auto');
                        $(thisId).next().find('.layui-table-main').css('height', 'auto');
                    } else if (typeof res.total === 'undefined' || typeof res.limit === 'undefined') {
                        $(thisId).next().css('height', 'auto');
                        $(thisId).next().find('.layui-table-main').css('height', 'auto');
                    } else {
                        table.resize(this.id); //重置表格尺寸
                    }
                }
            });
            return false;
        });
    });

    exports('assets/lookup/getInsuranceList', {});
});



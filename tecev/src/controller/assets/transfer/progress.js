layui.define(function(exports){
    //判断搜索建议的位置
    position = '';
    var TransferProgressObj = $("#LAY-Assets-Transfer-progress");
    if (Math.floor(TransferProgressObj.find(".layui-form-item").width()/TransferProgressObj.find(".layui-inline").width()) == 3){
        position = '';
    }else {
        position = 1;
    }
    layui.use(['layer', 'form', 'element', 'laydate', 'table', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, element = layui.element, laydate = layui.laydate, table = layui.table, suggest = layui.suggest, tablePlug = layui.tablePlug;

        //初始化搜索建议插件
        suggest.search();

        //先更新页面部分需要提前渲染的控件
        form.render();

        //转科时间元素渲染
        laydate.render(dateConfig('#progressTransferdate'));

        //申请时间元素渲染
        laydate.render(dateConfig('#progressApplicantdate'));

        //定义一个全局空对象
        var gloabOptions = {};
        //数据表格
        table.render({
            elem: '#progress'
            //,height: '600'
            , limits: [20, 50, 100]
            ,loading:true
            , limit: 20
            ,title: '转科进程列表'
            , height: 'full-100' //高度最大化减去差值
            ,url: progress //数据接口
            ,where: {
                sort: 'atid'
                ,order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            ,initSort: {
                field: 'atid' //排序字段，对应 cols 设定的各字段名
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
            ,page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                ,prev:'上一页'
                ,next:'下一页'
            },
            toolbar: 'true',
            defaultToolbar: ['filter','exports']
            ,cols: [[ //表头
                {
                    field: 'atid',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type:  'space',
                    templet: function(d){
                        return d.LAY_INDEX;
                    }
                }, {
                    field: 'assnum',
                    hide: get_now_cookie(userid + cookie_url + '/assnum') == 'false' ? true : false,
                    title: '设备编号',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    width: 150,
                    align: 'center'
                }
                , {
                    field: 'assets',
                    hide: get_now_cookie(userid + cookie_url + '/assets') == 'false' ? true : false,
                    title: '设备名称',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    width: 150,
                    align: 'center'
                }
                , {field: 'transfernum',hide: get_now_cookie(userid+cookie_url+'/transfernum')=='false'?true:false, title: '转科单号', width: 160, align: 'center'}
                , {field: 'category',hide: get_now_cookie(userid+cookie_url+'/category')=='false'?true:false, title: '设备分类', width: 150, align: 'center'}
                , {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格型号', width: 120, align: 'center'}
                , {field: 'tranout_depart_name',hide: get_now_cookie(userid+cookie_url+'/tranout_depart_name')=='false'?true:false, title: '转出科室', width: 150, align: 'center'}
                , {field: 'tranin_depart_name',hide: get_now_cookie(userid+cookie_url+'/tranin_depart_name')=='false'?true:false, title: '转入科室', width: 150, align: 'center'}
                , {field: 'applicant_user',hide: get_now_cookie(userid+cookie_url+'/applicant_user')=='false'?true:false, title: '申请人', width: 110, align: 'center'}
                , {field: 'applicant_time',hide: get_now_cookie(userid+cookie_url+'/applicant_time')=='false'?true:false, title: '申请时间', width: 160, align: 'center'}
                , {field: 'transfer_date',hide: get_now_cookie(userid+cookie_url+'/transfer_date')=='false'?true:false, title: '转科日期', width: 110, align: 'center'}
                , {field: 'check_time',hide: get_now_cookie(userid+cookie_url+'/check_time')=='false'?true:false, title: '验收时间', width: 160, align: 'center'}
                , {field: 'tran_reason',hide: get_now_cookie(userid+cookie_url+'/tran_reason')=='false'?true:false, title: '转科原因', width: 300, align: 'center'}
                , {field: 'tran_docnum',hide: get_now_cookie(userid+cookie_url+'/tran_docnum')=='false'?true:false, title: '转科文号', width: 200, align: 'center'}
                , {
                    field: 'approve_status_name',
                    hide: get_now_cookie(userid + cookie_url + '/approve_status_name') == 'false' ? true : false,
                    title: '审核状态',
                    width: 100,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    align: 'center'
                }
                , {
                    field: 'is_check_name',
                    hide: get_now_cookie(userid + cookie_url + '/is_check_name') == 'false' ? true : false,
                    title: '验收状态',
                    width: 100,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    align: 'center'
                }
            ]], done: function (res, curr) {
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
        form.on('checkbox', function(data){
            var type=$(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
            var key=data.elem.name;
            var status=data.elem.checked;
            document.cookie=userid+cookie_url+'/'+key+'='+status+"; expires=Fri, 31 Dec 9999 23:59:59 GMT";
        }
           // 
        });

        //列排序
        table.on('sort(progress)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('progress', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });

        //搜索按钮
        form.on('submit(progressSearch)', function(data){
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('progress', {
                url: progress
                ,where: gloabOptions
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

        //设备名称搜索建议
        $("#progressAssets").bsSuggest(
            returnAssets('transfer','assets')
        );

        //转入科室
        $("#progressAssetsDepartmentIn").bsSuggest(
            returnDepartment()
        );
        //转出科室
        $("#progressAssetsDepartmentOut").bsSuggest(
            returnDepartment()
        );


        //设备编号搜索建议
        $("#progressAssetsNum").bsSuggest(
            returnAssets('transfer','assnum')
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="progressAssetsOrnum"]').val(data.assorignum);
            $('input[name="progressAssets"]').val(data.assets);
        });

        //设备原编号搜索建议
        $("#progressAssetsOrnum").bsSuggest(
            returnAssets('transfer','assorignum')
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="progressAssetsNum"]').val(data.assnum);
            $('input[name="progressAssets"]').val(data.assets);
        });

        //分类搜索建议

        $("#progressCategory").bsSuggest(
            returnCategory('',position)
        );
    });
    exports('assets/transfer/progress', {});
});







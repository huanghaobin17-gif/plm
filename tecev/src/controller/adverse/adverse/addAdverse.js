layui.define(function(exports){
    layui.use([ 'form', 'upload','laydate','tipsType','suggest'], function(){
        var form = layui.form,upload = layui.upload,laydate = layui.laydate,suggest = layui.suggest,tipsType = layui.tipsType;

        //初始化搜索建议插件
        suggest.search();
        //初始化tips的选择功能
        tipsType.choose();
        //暂存
        form.on('submit(save)', function (data) {
            form.verify({
                tel: function(value) {
                    if (value){
                        if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                            return '报修电话首尾不能出现下划线\'_\'';
                        }
                        if(!checkTel(value)){
                            return '请正确填写电话号码，例如:13800138000或020-12345678，若电话为400，800开头则不需要填写分隔符';
                        }
                    }
                }
            });
            params = data.field;
            params.status = 0;
            if (params.place == 0){
                params.place = $("input[name='placeRemark']").val();
            }
            if (params.consequence == 0){
                params.consequence = $("input[name='consequence_date']").val();
            }
            if (params.consequence == 1){
                params.consequence = $("textarea[name='consequenceRemark']").val();
            }
            if (params.operator == 1){
                params.operator = $("input[name='operatorRemark']").val();
            }
            params.report_status = '';
            $("input[name='report_status']:checked").each(function () {
                params.report_status += ',' + $(this).val();
            });
            submit($, params,'addAdverse');
            return false;
        });

        //保存并结束
        form.on('submit(endSave)', function (data) {
            form.verify({
                tel: function(value) {
                    if (value){
                        if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                            return '联系电话首尾不能出现下划线\'_\'';
                        }
                        if(!checkTel(value)){
                            return '请正确填写电话号码，例如:13800138000或020-12345678，若电话为400，800开头则不需要填写分隔符';
                        }
                    }
                }
            });
            params = data.field;
            params.status = 1;
            if (params.place == 0){
                params.place = $("input[name='placeRemark']").val();
            }
            if (params.consequence == 0){
                params.consequence = $("input[name='consequence_date']").val();
            }
            if (params.consequence == 1){
                params.consequence = $("textarea[name='consequenceRemark']").val();
            }
            if (params.operator == 1){
                params.operator = $("input[name='operatorRemark']").val();
            }
            params.report_status = '';
            $("input[name='report_status']:checked").each(function () {
                params.report_status += ',' + $(this).val();
            });
            submit($, params,'addAdverse');
            return false;
        });

        //时间元素渲染
        laydate.render({
            elem: '#report_date' //指定元素
            ,max:now_date

        });
        laydate.render({
            elem: '#express_date' //指定元素
            ,max:now_date

        });
        laydate.render({
            elem: '#consequence_date' //指定元素
            ,max:now_date

        });
        laydate.render(dateConfig('#consequence_date'));//事件后果时间
        laydate.render(dateConfig('#validity_date'));//有效期至
        laydate.render(dateConfig('#manufacture_date'));//生产日期
        laydate.render(dateConfig('#discontinuation_date'));//停用日期
        laydate.render(dateConfig('#implantation_date'));//植入日期

        //先更新页面部分需要提前渲染的控件
        form.render();

        //8.医疗器械使用场所的radio
        form.on('radio(place)', function(data){
            var placeReamrk = $(".placeReamrk");
            if (data.value == 0){
                placeReamrk.removeAttr('disabled');
                placeReamrk.css('cursor','text');
                placeReamrk.attr('lay-verify','required');
            }else {
                placeReamrk.val('');
                placeReamrk.attr('disabled','disabled');
                placeReamrk.removeAttr('lay-verify');
                placeReamrk.css('cursor','not-allowed');
            }
        });
        var consequence_date = $("#consequence_date");
        //9.事件后果的radio
        form.on('radio(consequence)', function(data){
            var consequence_date = $("#consequence_date"),state = $(".state");
            if (data.value == 0){
                $(".state").val('');
                consequence_date.removeAttr('disabled');
                consequence_date.css('cursor','pointer');
                consequence_date.attr('lay-verify','required');
            }else if (data.value == 1){
                $("#consequence_date").val('');
                state.removeAttr('disabled');
                state.css('cursor','text');
                state.css('background-color','#fff');
                state.attr('lay-verify','required');
                consequence_date.attr('disabled','disabled');
                consequence_date.css('cursor','not-allowed');
                consequence_date.val('');
                consequence_date.removeAttr('lay-verify')
            }else {
                consequence_date.val('');
                state.val('');
                state.attr('disabled','disabled');
                state.css('cursor','not-allowed');
                state.css('background-color','#f3f3f3');
                state.removeAttr('lay-verify');
                consequence_date.attr('disabled','disabled');
                consequence_date.css('cursor','not-allowed');
                consequence_date.val('');
                consequence_date.removeAttr('lay-verify')
            }
        });

        //16.操作人的radio
        form.on('radio(operator)', function(data){
            var operatorRemark = $(".operatorRemark");
            if (data.value == 1){
                operatorRemark.removeAttr('disabled');
                operatorRemark.css('cursor','text');
                operatorRemark.attr('lay-verify','required');
            }else {
                operatorRemark.val('');
                operatorRemark.attr('disabled','disabled');
                operatorRemark.css('cursor','not-allowed');
                operatorRemark.removeAttr('lay-verify');
            }
        });

        upload.render({
            elem: '#choose' //绑定元素
            ,url: 'addAdverse?type=uploadFile' //上传接口
            ,exts: 'jpg|png|gif|bmp|jpeg|pdf|doc|docx'
            ,done: function(res){
                //上传完毕回调
                if (res.status == 1){
                    var html = '<div class="fileNameInput"><span class="fileName">'+res.name+'</span><i class="layui-icon closeFile" onclick="closeFile()">&#x1007;</i></div>';
                    $("input[name='file_url']").val(res.path);
                    $("input[name='Filename']").val(res.name);
                    $(".inputUpload").html(html);
                }
            }
            ,error: function(){
                //请求异常回调
            }
        });

        /*
         /选择生产商
         */
        $("#dic_factory").bsSuggest(
            getAllSupplierFactoryOrRepair('factory')
        ).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='company_address']").val(data.address);
            $("input[name='company_contract']").val(data.salesman_phone);
        });

        //设备名称搜索建议
        $("#productName").bsSuggest(
            {
                url: admin_name+'/Adverse/addAdverse?type=getAllAssetsSearch',
                effectiveFields: ["assnum","assets"],
                searchFields: [ "assets"],
                effectiveFieldsAlias: {assnum: "设备编号",assets: "设备名称"},
                ignorecase: false,
                showHeader: true,
                listStyle: {
                    "max-height": "375px", "max-width": "500px",
                    "overflow": "auto", "width": "400px", "text-align": "center"
                },
                showBtn: false,     //不显示下拉按钮
                delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
                idField: 'assid',
                listAlign: 'right',
                keyField: 'assets',
                clearable: false
            }
        ).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='assid']").val(data.assid);
            $("input[name='register_num']").val(data.serialnum);
            $("input[name='company']").val(data.factory);
            $("input[name='model']").val(data.model);
            $("input[name='assnum']").val(data.assnum);
            $("input[name='company_address']").val(data.address);
            $("input[name='company_contract']").val(data.factory_tel);
        }).on('onUnsetSelectValue', function () {
            //不正确
            $("input[name='assid']").val('');
            $("input[name='register_num']").val('');
            $("input[name='company']").val('');
            $("input[name='model']").val('');
            $("input[name='assnum']").val('');
        });
    });

//删除文件按钮
    function closeFile(a){
        $(".inputUpload").html('');
        $("input[name='file_url']").val('');
    }

    exports('controller/adverse/adverse/addAdverse', {});
});


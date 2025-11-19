layui.define(function(exports){
    layui.use(['form','laydate','formSelects','suggest','upload'], function () {
        var form = layui.form,laydate = layui.laydate,suggest = layui.suggest,formSelects = layui.formSelects,upload = layui.upload;
        suggest.search();
        formSelects.render('brand', selectParams(1));
        formSelects.btns('brand',selectParams(2));

        var hospital_id = $('input[name="hospital_id"]').val();
        var apply_type = $('select[name="apply_type"]').val();
        //监听提交
        form.on('submit(addDepartApply)', function(data){
            var params = data.field;
            if(params.apply_type == 2){
                //计划外 设备
                params.assets_name = '';
                params.unit = '';
                params.market_price = '';
                params.nums = '';
                params.is_import = '';
                params.buy_dtype = '';
                params.brand = '';
                var assets_name = $('.assets-list').find('.assets_name');
                var unit = $('.assets-list').find('.unit');
                var market_price = $('.assets-list').find('.market_price');
                var nums = $('.assets-list').find('.nums');
                var is_import = $('.assets-list').find('.is_import');
                var buy_type = $('.assets-list').find('.buy_type');
                var brand = $('.assets-list').find('.brand');
                $.each(assets_name,function (index,item) {
                    params.assets_name += $(item).html()+',';
                });
                $.each(unit,function (index,item) {
                    params.unit += $(item).html()+',';
                });
                $.each(market_price,function (index,item) {
                    params.market_price += $(item).html()+',';
                });
                $.each(nums,function (index,item) {
                    params.nums += $(item).html()+',';
                });
                $.each(is_import,function (index,item) {
                    params.is_import += $(item).html()+',';
                });
                $.each(buy_type,function (index,item) {
                    params.buy_type += $(item).html()+',';
                });
                $.each(brand,function (index,item) {
                    params.brand += $(item).html()+'|';
                });
                //附件
                params.file_name = '';
                params.save_name = '';
                params.file_type = '';
                params.file_size = '';
                params.file_url = '';
                var file_name = $('.file_list').find('.file_name');
                var file_size = $('.file_list').find('.file_size');
                $.each(file_name,function (index,item) {
                    params.file_name += $(item).html()+',';
                });
                $.each(file_size,function (index,item) {
                    params.file_size += $(item).html()+',';
                });


                var save_name = $('.file_list').find('.save_name');
                var file_type = $('.file_list').find('.file_type');
                var file_url = $('.file_list').find('.file_url');
                $.each(save_name,function (index,item) {
                    params.save_name += $(item).val()+',';
                });
                $.each(file_type,function (index,item) {
                    params.file_type += $(item).val()+',';
                });
                $.each(file_url,function (index,item) {
                    params.file_url += $(item).val()+',';
                });
            }
            submit($,params,'addPurchaseApply');
            return false;
        });

        form.on('submit(addOneAssets)',function (data) {
            var params = data.field;
            if(!params.assets_name){
                layer.msg("请选择设备名称！",{icon : 2,time:1000});
                return false;
            }
            var target = $('.assets-list').find('.assets_name');
            var flag = true;
            $.each(target,function (index,item) {
                if($(item).html() == params.assets_name){
                    flag = false;
                }
            });
            if(!flag){
                layer.msg("请勿重复添加设备！",{icon : 2,time:1000});
                return false;
            }
            if(params.market_price == '' || params.market_price <= 0){
                layer.msg("请正确填写单价！",{icon : 2,time:1000});
                return false;
            }else{
                var reg = /^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/;
                if (!reg.test(params.market_price)) {
                    layer.msg("请正确填写单价！",{icon : 2,time:1000});
                    return false;
                }
            }
            if(params.nums == '' || params.nums <= 0){
                layer.msg("请正确填写设备数量！",{icon : 2,time:1000});
                return false;
            }else{
                var reg = /^\+?[1-9][0-9]*$/;
                if (!reg.test(params.nums)) {
                    layer.msg("请正确填写设备数量！",{icon : 2,time:1000});
                    return false;
                }
            }
            if(params.is_import == ''){
                layer.msg("请选择是否进口！",{icon : 2,time:1000});
                return false;
            }
            if(params.buy_type == ''){
                layer.msg("请选择上报类型！",{icon : 2,time:1000});
                return false;
            }
            if(params.brand == ''){
                layer.msg("请选择参考品牌！",{icon : 2,time:1000});
                return false;
            }
            var is_import = params.is_import == 0 ? '否' : '是';
            var buy_type = params.buy_type == 1 ? '报废更新' : (params.buy_type == 2 ? '添置' : '新增');
            var xuhao = $('.assets-list').find('.old_list').length;
            if($('.assets-list').find('.no-data').length == 1){
                $('.assets-list').find('.no-data').remove();
            }
            var tr = '';
            tr += '<tr class="old_list">\n' +
                '<td class="xuhao">'+(xuhao+1)+'</td>\n' +
                '<td class="assets_name">'+params.assets_name+'</td>\n' +
                '<td class="unit">'+params.unit+'</td>\n' +
                '<td class="market_price">'+params.market_price+'</td>\n' +
                '<td class="nums">'+params.nums+'</td>\n' +
                '<td class="is_import">'+is_import+'</td>\n' +
                '<td class="buy_type">'+buy_type+'</td>\n' +
                '<td class="brand">'+params.brand+'</td>\n' +
                '<td><button type="button" class="layui-btn layui-btn-xs  layui-btn-danger delAssets" lay-submit lay-filter="delAssets">\n' +
                '                                    移除\n' +
                '                                </button></td>\n' +
                '</tr>';
            $('.assets-list').find('.addAssets').before(tr);
            $('input[name="assets_name"]').val('');
            $('input[name="unit"]').val('');
            $('input[name="nums"]').val('');
            $('input[name="market_price"]').val('');
            $('select[name="is_import"]').val('');
            $('select[name="buy_type"]').val('');
            //管理科室 多选框初始配置
            formSelects.render('brand', selectParams(1));
            formSelects.btns('brand',selectParams(2));
            form.render();
            return false;
        });
        $("body").on('click','.delAssets',function () {
            $(this).parent().parent().remove();
            var target = $('.assets-list').find('.xuhao');
            $.each(target,function (index,item) {
                $(item).html(index+1);
            });
        });

        //切换医院
        form.on('select(hospital_id)',function (data) {
            $('input[name="project_name"]').val('');
            $("#apply_department").bsSuggest("destroy");
            var main_assets_div=$('#apply_department_sel');
            var html = '<div class="input-group">';
            html += '<input type="text" class="form-control bsSuggest" id="apply_department" lay-verify="departid|required" placeholder="请选择申请科室" />\n' +
                '                        <div class="input-group-btn">\n' +
                '                            <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">\n' +
                '                            </ul>\n' +
                '                        </div>';
            html += '</div>';
            main_assets_div.html('');
            main_assets_div.html(html);
            apply_type = $('select[name="apply_type"]').val();
            if(apply_type == 2){
                $('.addAssets').show();
                $('#addAssetsFile').show();
                $('#dwFile').show();
                $('input[name="project_name"]').removeAttr('readonly');
            }else{
                $('.addAssets').hide();
                $('#addAssetsFile').hide();
                $('#dwFile').hide();
                $('input[name="project_name"]').attr('readonly','readonly');
            }
            var tr1 = '<tr class="no-data">\n' +
                '<td colspan="10" style="text-align: center !important;">暂无数据</td>\n' +
                '</tr>';
            var tr2 = '<tr class="no-data">\n' +
                '<td colspan="6" style="text-align: center !important;">暂无数据</td>\n' +
                '</tr>';
            $('.assets-list').find('.old_list').remove();
            if($('.assets-list').find('.no-data').length == 0){
                $('.addAssets').before(tr1);
            }
            $('.file_list').find('tr').remove();
            $('.file_list').append(tr2);
            init_apply_department(data.value,apply_type);
        });

        //切换申请方式
        form.on('select(apply_type)',function (data) {
            $('input[name="project_name"]').val('');
            $("#apply_department").bsSuggest("destroy");
            var main_assets_div=$('#apply_department_sel');
            var html = '<div class="input-group">';
            html += '<input type="text" class="form-control bsSuggest" id="apply_department" lay-verify="departid|required" placeholder="请选择申请科室" />\n' +
                '                        <div class="input-group-btn">\n' +
                '                            <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">\n' +
                '                            </ul>\n' +
                '                        </div>';
            html += '</div>';
            main_assets_div.html('');
            main_assets_div.html(html);
            if(data.value == 2){
                $('.addAssets').show();
                $('#addAssetsFile').show();
                $('#dwFile').show();
                $('input[name="project_name"]').val('');
                $('input[name="project_name"]').removeAttr('readonly');
                var tr1 = '<tr class="no-data">\n' +
                    '<td colspan="10" style="text-align: center !important;">暂无数据</td>\n' +
                    '</tr>';
                var tr2 = '<tr class="no-data">\n' +
                    '<td colspan="6" style="text-align: center !important;">暂无数据</td>\n' +
                    '</tr>';
                $('.assets-list').find('.old_list').remove();
                if($('.assets-list').find('.no-data').length == 0){
                    $('.addAssets').before(tr1);
                }
                $('.file_list').find('.old_list').remove();
                if($('.file_list').find('.no-data').length == 0){
                    $('.file_list').append(tr2);
                }
                init_apply_department(hospital_id,data.value);
            }else{
                $('.addAssets').hide();
                $('#addAssetsFile').hide();
                $('#dwFile').hide();
                $('input[name="project_name"]').attr('readonly','readonly');
                $('.file_list').find('.old_list').remove();
                if($('.file_list').find('.no-data').length == 0){
                    var tr2 = '<tr class="no-data">\n' +
                        '<td colspan="6" style="text-align: center !important;">暂无数据</td>\n' +
                        '</tr>';
                    $('.file_list').append(tr2);
                }
                init_apply_department(hospital_id,data.value);
            }
        });
        init_apply_department(hospital_id,apply_type);
        /*
         /选择申请科室
         */
        function init_apply_department(hospital_id,apply_type) {
            $("#apply_department").bsSuggest(
                returnApplyDepartment(hospital_id,apply_type)
            ).on('onSetSelectValue', function (e, keyword, data) {
                $('input[name="project_name"]').val(data.project_name);
                $('input[name="departid"]').val(data.departid);
                if(apply_type == 1){
                    $('input[name="plans_id"]').val(data.plans_id);
                }else{
                    $('input[name="plans_id"]').val(0);
                }
                showAssetsAndFileInfo(data.plans_id);
            }).on('onUnsetSelectValue', function () {
                //不正确
                $('input[name="departid"]').val('0');
            });
        }

        function showAssetsAndFileInfo(plans_id) {
            var params = {};
            params.action = 'addPlnasAssets';
            params.plans_id = plans_id;
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: 'addPurchaseApply',
                data: params,
                dataType: "json",
                async: true,
                success: function (data) {
                    if (!$.isEmptyObject(data.assets)) {
                        var tr = '';
                        $.each(data.assets,function (index,item) {
                            tr += '<tr class="old_list">\n' +
                                '<td>'+(index+1)+'</td>\n' +
                                '<td>'+item.assets_name+'</td>\n' +
                                '<td>'+item.unit+'</td>\n' +
                                '<td>'+item.market_price+'</td>\n' +
                                '<td>'+item.nums+'</td>\n' +
                                '<td>'+item.buy_type_name+'</td>\n' +
                                '<td>'+item.is_import_name+'</td>\n' +
                                '<td>'+item.brand+'</td>\n' +
                                '<td></td>\n' +
                                '</tr>';
                        });
                        if($('.assets-list').find('.no-data').length == 1){
                            $('.assets-list').find('.no-data').remove();
                        }
                        $('.assets-list').find('.addAssets').hide();
                        $('.assets-list').find('.old_list').remove();
                        $('.assets-list').append(tr);
                    }else{
                        var tr = '<tr class="no-data">\n' +
                            '<td colspan="10" style="text-align: center !important;">暂无数据</td>\n' +
                            '</tr>';
                        $('.assets-list').find('.old_list').remove();
                        if($('.assets-list').find('.no-data').length == 0){
                            $('.addAssets').before(tr1);
                        }
                    }
                    if(!$.isEmptyObject(data.files)){
                        var tr = '';
                        $.each(data.files,function (index,item) {
                            tr += '<tr class="old_list">\n' +
                                '<td>'+(index+1)+'</td>\n' +
                                '<td>'+item.file_name+'</td>\n' +
                                '<td>'+item.file_size+'</td>\n' +
                                '<td>'+item.add_user+'</td>\n' +
                                '<td>'+item.add_time+'</td>\n' +
                                '<td></td>\n' +
                                '</tr>';
                        });
                        $('.file_list').find('tr').remove();
                        $('.file_list').append(tr);
                    }else{
                        var tr = '<tr class="no-data">\n' +
                            '<td colspan="6" style="text-align: center !important;">暂无数据</td>\n' +
                            '</tr>';
                        $('.file_list').find('tr').remove();
                        $('.file_list').append(tr);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                }
            });
            return false;
        }

        //上传附件
        upload.render({
            elem: '#addAssetsFile'  //绑定元素
            , url: admin_name+'/PurchaseApply/addPurchaseApply' //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf|zip' //格式 用|分隔
            , method: 'POST'
            , data: {action: 'uploadFile'}
            , choose: function (obj) {

            }
            ,before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。

            }
            , done: function (res) {
                if (res.status == 1) {
                    if($('.file_list').find('.no-data').length == 1){
                        $('.file_list').find('.no-data').remove();
                    }
                    var xuhao = $('.file_list').find('.old_list').length;
                    var tr = '';
                    tr += '<tr class="old_list">\n' +
                        '<td class="xuhao">'+(xuhao+1)+'</td>\n' +
                        '<td class="file_name">'+res.file_name+'</td>\n' +
                        '<td class="file_size">'+res.file_size+'</td>\n' +
                        '<td class="add_user">'+res.add_user+'</td>\n' +
                        '<td class="add_time">'+res.add_time+'</td>\n' +
                        '<td>' +
                        '<input type="hidden" class="file_type" name="file_type" value="'+res.file_type+'"/>' +
                        '<input type="hidden" class="file_url" name="file_url" value="'+res.file_url+'"/>' +
                        '<input type="hidden" class="save_name" name="save_name" value="'+res.save_name+'"/>' +
                        '<button type="button" class="layui-btn layui-btn-xs  layui-btn-danger delFile" lay-submit lay-filter="delFile">移除</button>' +
                        '</td>\n' +
                        '</tr>';
                    $('.file_list').append(tr);
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function (index, upload) {
                //失败
            }
        });

        //下载模板文件
        $("#dwFile").on('click',function () {
            var params = {};
            params.path = '/Public/downloads/12.zip';
            params.filename = '科室申请附件模板.zip';
            postDownLoadFile({
                url:admin_name+'/Tool/downFile',
                data: params,
                method: 'POST'
            });
        });
        $("body").on('click','.delFile',function () {
            $(this).parent().parent().remove();
            var target = $('.file_list').find('.xuhao');
            $.each(target,function (index,item) {
                $(item).html(index+1);
            });
        });
        /*
         /选择设备字典名称
         */
        $("#dist_assets_name").bsSuggest(
            returnDicAssets(hospital_id)
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="unit"]').val(data.unit);
            $('input[name="asname"]').val('1');
        }).on('onUnsetSelectValue', function () {
            //不正确
            $('input[name="asname"]').val('0');
        });
    });
    exports('controller/purchases/purchaseApply/addPurchaseApply', {});
});
layui.define(function(exports){
    layui.use(['form','laydate','formSelects','suggest','upload'], function () {
        var form = layui.form,laydate = layui.laydate,suggest = layui.suggest,formSelects = layui.formSelects,upload = layui.upload;
        suggest.search();
        formSelects.render('brand', selectParams(1));
        formSelects.btns('brand',selectParams(2));

        form.verify({
            market_price: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (!/^(([1-9]\d*)|\d)(\.\d{1,3})?$/.test(value)) {
                    return '请输入正确的金额';
                }
            },
            company_price: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (!/^(([1-9]\d*)|\d)(\.\d{1,3})?$/.test(value)) {
                    return '请输入正确的金额';
                }
            }
        });

        //监听提交
        form.on('submit(addFac)', function(data){
            var params = data.field;
            params.action = 'addFac';
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
            submit($,params,'handleTender');
            return false;
        });
        var hospital_id = $('input[name="hospital_id"]').val();

        //上传附件
        upload.render({
            elem: '#addFacFile'  //绑定元素
            , url: admin_name+'/TenderRecord/handleTender' //接口
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

        $("body").on('click','.delFile',function () {
            $(this).parent().parent().remove();
            var target = $('.file_list').find('.xuhao');
            if(target.length > 0){
                $.each(target,function (index,item) {
                    $(item).html(index+1);
                });
            }else{
                var tr = '<tr class="no-data">\n' +
                    '                        <td colspan="6" style="text-align: center !important;">暂无相关记录</td>\n' +
                    '                    </tr>';
                $('.file_list').append(tr);
            }
        });
        $('#addBrandDic').on('click',function () {
            var url = $(this).attr('data-url');
            var flag = 1;
            top.layer.open({
                id: 'addDictorys',
                type: 2,
                title: '添加品牌字典',
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar: false,
                area: ['680px', '100%'],
                closeBtn: 1,
                content: url,
                end: function () {
                    if (flag) {
                        location.reload();
                    }
                },
                cancel: function () {
                    //如果是直接关闭窗口的，则不刷新表格
                    flag = 0;
                }
            });
            return false;
        });

        /*
        /选择供应商
        */
        $("#dic_supplier").bsSuggest(
            getAllSupplierFactoryOrRepair('supplier')
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="supplier_id"]').val(data.olsid);
        }).on('onUnsetSelectValue', function () {
            //不正确
            $('input[name="asname"]').val('0');
        });
        /*
        /选择生产商
        */
        $("#dic_factory").bsSuggest(
            getAllSupplierFactoryOrRepair('factory')
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="factory_id"]').val(data.olsid);
        }).on('onUnsetSelectValue', function () {
            //不正确
            $('input[name="asname"]').val('0');
        });

        /*
        /选择品牌名称
        */
        $("#dic_brand").bsSuggest(
            returnDicBrand()
        );
    });
    exports('controller/purchases/tenderRecord/addFacDetail', {});
});

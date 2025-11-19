layui.define(function(exports){
    var assid = $('input[name="assid"]').val();
//参保列表
    layui.use(['layer', 'form', 'laydate', 'formSelects', 'table', 'upload', 'tipsType', 'tablePlug'], function () {
        var laydate = layui.laydate, table = layui.table, upload = layui.upload, form = layui.form, $ = layui.jquery,
            layer = layui.layer, tipsType = layui.tipsType, formSelects = layui.formSelects, tablePlug = layui.tablePlug;

        formSelects.render('suppliers_type', selectParams(2));
        formSelects.btns('suppliers_type', selectParams(2));
        form.render();
        tipsType.choose();

        laydate.render(dateConfig('#doRenewalBuydate'));
        laydate.render({
            elem: '#doRenewalStartdate',
            calendar: true
            , min: 0
        });
        laydate.render({
            elem: '#doRenewalOverdate',
            calendar: true
            , min: 1
        });
        layer.config(layerParmas());

        //验证
        form.verify({
            cost: function (value) { //value：表单的值、item：表单的DOM对象
                if ($.trim(value) != '') {
                    if (!check_price(value)) {
                        return '请输入正确格式的维保金额';
                    }
                }
            }
        });


        //初始化维保明细列表
        table.render({
            elem: '#doRenewal'
            , limits: [10, 20, 50, 100]
            , loading: true
            , url: admin_name+'/Lookup/doRenewal.html' //数据接口
            , where: {
                type: 'getList'
                , assid: assid
                , sort: 'insurid'
                , order: 'asc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'insurid' //排序字段，对应 cols 设定的各字段名
                , type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            }
            , method: 'POST' //如果无需自定义HTTP类型，可不加该参数 
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                dataName: 'rows' //数据字段
            }
            , cols: [[ //表头
                {
                    field: 'insurid',
                    title: '序号',
                    width: 60,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'company',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    title: '维保公司名称',
                    width: 240,
                    align: 'center'
                }
                , {field: 'nature', title: '维保性质', width: 100, align: 'center'}
                , {field: 'cost', title: '参保费用', width: 100, align: 'center'}
                , {field: 'buydate', title: '购入日期', width: 110, align: 'center'}
                , {field: 'term', title: '维保期限', width: 200, align: 'center'}
                , {field: 'contacts', title: '联系人', width: 100, align: 'center'}
                , {field: 'telephone', title: '联系电话', width: 120, align: 'center'}
                , {field: 'content', title: '维保内容', width: 300, align: 'center'}
                , {field: 'file_data', title: '相关文件', width: 150, align: 'center'}
                , {field: 'remark', title: '备注', width: 180, align: 'center'}
                , {
                    field: 'operation',
                    title: '操作',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    width: 120,
                    align: 'center'
                }]]
        });
        //监听工具条-修改操作
        table.on('tool(doRenewalData)', function (obj) {
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event;
            var action = $(this).attr('data-url');
            switch (layEvent) {
                case 'save_insurance':
                    var html = '<form class="layui-form layui-form-pane" action="">';
                    html += '<div class="layui-form-item">';
                    html += '<div class="layui-inline">';
                    html += '<label class="layui-form-label" style="width: 125px;">维保公司：</label>';
                    html += '<div class="layui-input-inline">';
                    html += '<input type="text" name="company_save" class="layui-input" readonly value="' + rows.company + '">';
                    html += '</div>';
                    html += '</div>';
                    html += '<div class="layui-inline">';
                    html += '<label class="layui-form-label" style="width: 125px;"><span class="rquireCoin"> * </span>联系人：</label>';
                    html += '<div class="layui-input-inline">';
                    html += '<input type="text" name="contacts_save" class="layui-input" autocomplete="off" value="' + rows.contacts + '">';
                    html += '</div>';
                    html += '</div>';
                    html += '<div class="layui-inline">';
                    html += '<label class="layui-form-label" style="width: 125px;"><span class="rquireCoin"> * </span>联系电话：</label>';
                    html += '<div class="layui-input-inline">';
                    html += '<input type="text" autocomplete="off" class="layui-input" name="telephone_save" lay-verify="number" value="' + rows.telephone + '">';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                    html += '</form>';
                    layer.open({
                        title: '修改【' + rows.company + ' 】联系人',
                        area: ['450px', '290px'],
                        content: html
                        , btn: ['确认', '取消']
                        , yes: function (index, layero) {
                            //按钮【按钮一】的回调
                            contacts = $('input[name="contacts_save"]').val();
                            telephone = $('input[name="telephone_save"]').val();
                            if ($.trim(contacts) == '') {
                                layer.msg("联系人不能为空！", {icon: 2}, 1000);
                                return false;
                            }
                            if (!checkTel(telephone)) {
                                layer.msg("请输入正确的号码！", {icon: 2}, 1000);
                                return false;
                            }
                            var params = {};
                            params.type='saveInsurance';
                            params.insurid = rows.insurid;
                            params.contacts = contacts;
                            params.telephone = telephone;
                            $.ajax({
                                timeout: 5000,
                                type: "POST",
                                url: 'doRenewal',
                                data: params,
                                dataType: "json",
                                success: function (data) {
                                    if (data) {
                                        if (data.status == 1) {
                                            table.reload('doRenewal', {
                                                url: admin_name+'/Lookup/doRenewal.html'
                                                , where: {assid: assid, type: 'getList'}
                                            });
                                            layer.msg(data.msg, {icon: 1}, 1000);
                                        } else {
                                            layer.msg(data.msg, {icon: 2}, 1000);
                                        }
                                    } else {
                                        layer.msg("数据异常！", {icon: 2}, 1000);
                                    }
                                },
                                error: function () {
                                    layer.msg("网络访问失败", {icon: 2}, 1000);
                                }
                            });
                        }
                    });
                    break;
            }
        });


        //修改维保性质
        var old_nature = -1;
        var salesman_name_all=[];
        var salesman_phone_all=[];
        form.on('select(nature)', function (data) {
            var nature = parseInt(data.value);
            var company_id = $('select[name="company_id"]');
            var contacts = $('input[name="contacts"]');
            var telephone = $('input[name="telephone"]');
            var html='';
            if (data.value) {
                if (nature !== old_nature) {
                    if (nature === parseInt(INSURANCE_IS_GUARANTEE)) {
                        html='<option value="'+usecompany.ols_facid+'">'+usecompany.factory+'</option>';
                        company_id.html(html);
                        company_id.val(usecompany.ols_facid);
                        contacts.val(usecompany.factory_user);
                        telephone.val(usecompany.factory_tel);
                        salesman_name_all[usecompany.ols_facid]=usecompany.factory_user;
                        salesman_phone_all[usecompany.ols_facid]=usecompany.factory_tel;
                        old_nature=nature;
                    } else {
                        //选择第三方
                        var params={};
                        params.type = 'getRepairOffList';
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            url: 'doRenewal',
                            data: params,
                            dataType: "json",
                            success: function (data) {
                                if (data.status === 1) {
                                    var html = '<option value="">请选择第三方公司</option>';

                                    $.each(data.result, function (key, value) {
                                        salesman_name_all[value.olsid]=value.salesman_name;
                                        salesman_phone_all[value.olsid]=value.salesman_phone;
                                        html += '<option value="' + value.olsid + '">' + value.sup_name + '</option>';
                                    });
                                    company_id.html(html);
                                    contacts.val('');
                                    telephone.val('');
                                    old_nature=nature;
                                    form.render();
                                } else {
                                    layer.msg(data.msg, {icon: 2, time: 3000});
                                }
                            },
                            error: function () {
                                layer.msg('网络访问失败', {icon: 2, time: 3000});
                            }
                        });
                    }
                }
            }else{
                //选择空名称 复位填充数据
                html=' <option value="-1">请先选择维保性质</option>';
                company_id.html(html);
                contacts.val('');
                telephone.val('');
                old_nature=nature;
            }
            form.render();
        });




        //选择维保公司
        form.on('select(company_id)', function (data) {
            var contacts = $('input[name="contacts"]');
            var telephone = $('input[name="telephone"]');
            if(data.value){

                contacts.val(salesman_name_all[data.value]);
                telephone.val(salesman_phone_all[data.value]);
                console.log(data.value);

                console.log(salesman_name_all[data.value]);
                form.render();

            }else{
                contacts.val('');
                telephone.val('');
            }
        });

        //点击补充厂家
        $(document).on('click', '#addSupplier', function () {
            layer.open({
                id: 'addOLSContractAddSupplier',
                type: 1,
                title: '添加厂商',
                area: ['750px', '450px'],
                offset: 'auto',
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#addSuppliersDiv'),
                success: function (layero, index) {
                    //复位操作
                    resetAddSuppliers();
                    form.on('submit(addSuppliers)', function (data) {
                        var params = data.field;
                        params.type = 'addSuppliers';
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            url: 'doRenewal',
                            data: params,
                            dataType: "json",
                            success: function (data) {
                                if (data.status === 1) {

                                    $('input[name="sup_num"]').val(data.result.sup_num);

                                    if (parseInt(old_nature) === parseInt(INSURANCE_THIRD_PARTY)) {
                                        var arr = params.suppliers_type.split(',');
                                        var html = '';
                                        var set = false;
                                        if ($.inArray('4', arr) >= 0) {
                                            html = '<option value="' + data.result.olsid + '">' + data.result.sup_name + '</option>';
                                            $('select[name="company_id"]').append(html);
                                            form.render();
                                        }
                                    }
                                    layer.close(layer.index);
                                    layer.msg('补充成功', {icon: 1, time: 3000});
                                } else {
                                    layer.msg(data.msg, {icon: 2, time: 3000});
                                }
                            },
                            error: function () {
                                layer.msg('网络访问失败', {icon: 2, time: 3000});
                            }
                        });
                        return false;
                    });
                    form.render('select');
                }
            });
        });

        //已选省份、城市
        var old_provinces = 0;
        var old_city = 0;
        //选择省份
        form.on('select(provinces)', function (data) {
            var provinces = parseInt(data.value);
            if (data.value) {
                if (provinces !== old_provinces) {
                    var params = {};
                    params.type = 'getCity';
                    params.provinceid = provinces;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: 'doRenewal',
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                if (data.result.length > 0) {
                                    var html = '<option value="">请选择城市</option>';
                                    $('select[name="areas"]').html(html);
                                    $.each(data.result, function (key, value) {
                                        html += '<option value="' + value.cityid + '">' + value.city + '</option>';
                                    });
                                    $('select[name="city"]').html(html);
                                } else {
                                    $('select[name="city"]').html('<option>/</option>');
                                    $('select[name="areas"]').html('<option>/</option>');
                                }
                                form.render();
                                old_provinces = provinces;

                            } else {
                                layer.msg(data.msg, {icon: 2, time: 3000});
                            }
                        },
                        error: function () {
                            layer.msg('网络访问失败', {icon: 2, time: 3000});
                        }
                    });
                }
            } else {
                var html = '<option value="">请选择省份</option>';
                //选择空名称 复位填充数据
                $('select[name="city"]').html(html);
                $('select[name="areas"]').html(html);
                form.render();
                old_provinces = 0;
            }
        });
        //选择城市
        form.on('select(city)', function (data) {
            var city = parseInt(data.value);
            if (data.value) {
                if (city !== old_city) {
                    var params = {};
                    params.type = 'getAreas';
                    params.cityid = city;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: 'doRenewal',
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                if (data.result.length > 0) {
                                    var html = '<option value="">请选择区/城镇</option>';
                                    $.each(data.result, function (key, value) {
                                        html += '<option value="' + value.areaid + '">' + value.area + '</option>';
                                    });
                                    $('select[name="areas"]').html(html);
                                } else {
                                    $('select[name="areas"]').html('<option value="">/</option>');
                                }
                                form.render();
                                old_city = city;
                            } else {
                                layer.msg(data.msg, {icon: 2, time: 3000});
                            }
                        },
                        error: function () {
                            layer.msg('网络访问失败', {icon: 2, time: 3000});
                        }
                    });
                }
            } else {
                var html = '<option value="">请选择城市</option>';
                //选择空名称 复位填充数据
                $('select[name="areas"]').html(html);
                form.render();
                old_city = 0;
            }
        });



        //监听提交
        form.on('submit(addDoRenewal)', function (data) {
            var table = layui.table;
            var params = data.field;
            var formerly = '', fileName = '';
            $('.file_data').each(function () {
                formerly += $(this).find('span').html() + '|';
                fileName += $(this).find('input[name="path"]').val() + '|';
            });
            params.company = $('select[name="company_id"]').find('option[value="' + params.company_id + '"]').html();



            params.formerly = formerly;
            params.type = 'addInsurance';
            params.fileName = fileName;

            console.log(params);
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: 'doRenewal',
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status == 1) {
                        table.reload('doRenewal', {
                            url: admin_name+'/Lookup/doRenewal.html'
                            , where: {assid: assid, type: 'getList'}
                        });
                        $('.file_url').empty();
                        $('#addInsurance')[0].reset();
                        form.render();
                        layer.msg(data.msg, {icon: 1}, 1000);
                    } else {
                        layer.msg(data.msg, {icon: 2}, 1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2}, 1000);
                }
            });
            return false;
        });
        //上传文件
        uploadFile = upload.render({
            elem: '#file_url'  //绑定元素
            , url: admin_name+'/Lookup/doRenewal' //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
            , method: 'POST'
            , data: {type: 'upload'}
            , choose: function (obj) {
                //选择文件后
            }
            , done: function (res) {
                layer.closeAll('loading');
                if (res.status == 1) {
                    $(".file_url").append('<span class="file_data" data-name="' + res.name + '"><span>' + res.formerly + '</span><input type="hidden" name="path" class="path" value="' + res.path + '"><a style="color: red;text-decoration: none;cursor: pointer;"  onclick="delDoRenewaFile(this)">  删除  </a></span>');
                    layer.msg(res.msg, {icon: 1}, 1000);
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function (index, upload) {
                //失败
            }
        });


        //复位添加厂商Div from
        function resetAddSuppliers() {
            $('input[name="sup_name"]').val('');
            formSelects.value('suppliers_type', []);
            $('input[name="salesman_name"]').val('');
            $('input[name="salesman_phone"]').val('');
            $('input[name="address"]').val('');
            $('select[name="provinces"]').val('');
            $('select[name="city"]').html('<option value="">请选择省份</option>');
            $('select[name="areas"]').html('<option value="">请选择城市</option>');
            form.render();
        }

    });
//提示是否下载/预览
    $(document).on('click', '.operationFile', function () {
        var path = $(this).data('path');
        var name = $(this).data('name');
        var  url = admin_name+'/Tool/showFile';
                top.layer.open({
                    id: 'showFiles',
                    type: 2,
                    title: name + ' 查看',
                    scrollbar: false,
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    area: ['75%', '100%'],
                    closeBtn: 1,
                    content: [url +'?path=' + path + '&filename=' + name]
                });
                return false;
    });
//删除上传文件
    function delDoRenewaFile(a) {
        $(a).parent().remove();
        layer.msg('删除成功', {icon: 1}, 1000);
    }
    exports('controller/assets/lookup/doRenewal', {});
});
layui.define(function (exports) {
    layui.use(['layer', 'form', 'upload', 'formSelects', 'tipsType', 'laydate'], function () {
        var form = layui.form, upload = layui.upload, layer = layui.layer, laydate = layui.laydate,
            formSelects = layui.formSelects,
            tipsType = layui.tipsType,$ = layui.jquery;
        //初始化tips
        tipsType.choose();
        laydate.render({
            elem: '#validity' //指定元素
            , min: today
        });
        //管理科室 多选框初始配置
        formSelects.render('department', selectParams(1));
        formSelects.btns('department', selectParams(2));

        //用户角色 多选框初始配置
        formSelects.render('character', selectParams(1));
        formSelects.btns('character', selectParams(2));
        var thisbody = $('#LAY-BaseSetting-User-addUser');
        var is_supplier = 0;
        form.verify({
            username: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '用户名首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d+\d$/.test(value)) {
                    return '用户名不能全为数字';
                }
            },
            password: [/^(?![a-z]+$)(?![A-Z]+$)(?![0-9]+$)(?![\W_]+$)[a-zA-Z0-9\W_]{8,30}$/, '密码必须8到18位，且大写字母 小写字母 数字 特殊字符，四种包括两种,且不能出现空格'],
            passwordconfirm: function (value) {
                if (value !== $("input[name='password']").val()) {
                    $("input[name='passwordconfirm']").val("");
                    return '确认密码与密码不一致';
                }
            },
            department: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (!value && is_supplier === 0) {
                    return '请选择管理科室'
                }
            },
            character: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (!value && is_supplier === 0) {
                    return '请选择用户角色'
                }
            }
        });


        //显示/隐藏密码
        var showPassword = 0;
        thisbody.find(".layui-icon-zzeye-slash").click(function () {
            if (showPassword === 0) {
                showPassword = 1;
                thisbody.find('input[name="password"]').attr('type', 'text');
                thisbody.find('input[name="passwordconfirm"]').attr('type', 'text');
                $(this).removeClass('layui-icon-zzeye-slash');
                $(this).addClass('layui-icon-zzeye');
            } else {
                showPassword = 0;
                thisbody.find('input[name="password"]').attr('type', 'password');
                thisbody.find('input[name="passwordconfirm"]').attr('type', 'password');
                $(this).removeClass('layui-icon-zzeye');
                $(this).addClass('layui-icon-zzeye-slash');
            }
        });

        thisbody.find(".makeP").click(function () {
            var passw = _getRandomString(8);
            thisbody.find('input[name="password"]').val(passw);
            thisbody.find('input[name="passwordconfirm"]').val(passw);
            showPassword = 1;
            thisbody.find('input[name="password"]').attr('type', 'text');
            thisbody.find('input[name="passwordconfirm"]').attr('type', 'text');
            return false;
        });


        form.on('radio(is_supplier)', function (data) {
            if (parseInt(data.value) === 1) {
                thisbody.find('.yes_supplier').show();
                thisbody.find('.not_supplier').hide();
                is_supplier = 1;
            } else {
                thisbody.find('.yes_supplier').hide();
                thisbody.find('.not_supplier').show();
                is_supplier = 0;
            }

        });


        //监听提交
        form.on('submit(save)', function (data) {
            params = data.field;
            if (is_supplier === 0) {
                if (!params.belongDepartment) {
                    layer.msg("请选择工作科室", {icon: 2}, 1000);
                    return false;
                }
                params.department = formSelects.value('department', 'valStr');
                params.character = formSelects.value('character', 'valStr');
            }

            var myreg = /^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[0-9])\d{8}$/;
            var phone = $.trim($("input[name='telephone']").val());
            if (!myreg.test(phone)) {
                layer.msg("请输入有效的手机号码", {icon: 2});
                return false;
            }
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: 'addUser',
                data: params,
                dataType: "json",
                async: true,
                beforeSend: beforeSend,
                success: function (data) {
                    if (data.status == 1) {
                        layer.msg(data.msg, {icon: 1},function () {
                            parent.layer.closeAll()
                        });
                    } else if (data.status == -2) {
                        layer.confirm(data.msg, {
                            btn: ['恢复', '取消'] //可以无限个按钮
                            , yes: function (index, layero) {
                                var params = {};
                                params = data.data;
                                params.userid = data.userid;
                                params.action = 'restore';
                                submit($, params, 'addUser');
                            }, btn2: function (index) {
                                layer.msg('请在用户名后加上数字或者字母再次添加', {icon: 2}, 1000);
                            }
                        });
                    } else {
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
        //点击打开裁剪页面
        $('#addautograph').on('click', function () {
            var storage = window.localStorage;
            storage.setItem('picbase', "");
            layer.open({
                id: 'cropper',
                type: 2,
                title: '上传并裁剪图片',
                offset: '20px',
                area: ['750px', '100%'],
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: "uploadautograph",
                end: function () {
                    var storage = window.localStorage;
                    if (storage['picbase'] != "") {
                        compress(storage['picbase'], 200, 0.5).then(function (val) {
                            $('#autographpic').attr('src', val);
                            $('#autograph').attr('value', val);
                        });
                        $('#autographpic').css("display", "inline");
                        $('#addautograph').text('修改');
                    }
                }
            })
        });
        form.on('select(manageDepartment)', function (data) {
            if (data.value === '') {
                $(".boldManger").hide();
            } else {
                //获取工作科室负责人
                $.getJSON(admin_name+'/User/addUser?type=getManager&departid=' + data.value, function (v) {
                    $(".boldManger").show();
                    if (!v) {
                        $(".manager").html('无')
                    } else {
                        $(".manager").html(v)
                    }
                });
            }
        });

        form.on('select(getHospitalDepartment)', function (data) {
            //分院功能获取对应科室
            formSelects.data('department', 'server', {
                url: "addUser?type=getHospitalDepartment&hospital_id=" + data.value
            });
            $.getJSON(admin_name+'/User/addUser?type=getHospitalDepartment&hospital_id=' + data.value, function (v) {
                html = '<option value="">请先选择所属医院</option>';
                $.each(v.data, function (key, value) {
                    html += '<option value="' + value.value + '">' + value.name + '</option>';
                });
                $("select[name='belongDepartment']").html(html);
                form.render('select');
            });
        });

        function compress(base64String, w, quality) {
            var getMimeType = function (urlData) {
                var arr = urlData.split(',');
                var mime = arr[0].match(/:(.*?);/)[1];
                // return mime.replace("image/", "");
                return mime;
            };
            var newImage = new Image();
            var imgWidth, imgHeight;

            var promise = new Promise(resolve => newImage.onload = resolve);
            newImage.src = base64String;
            return promise.then(() => {
                imgWidth = newImage.width;
            imgHeight = newImage.height;
            var canvas = document.createElement("canvas");
            var ctx = canvas.getContext("2d");
            if (Math.max(imgWidth, imgHeight) > w) {
                if (imgWidth > imgHeight) {
                    canvas.width = w;
                    canvas.height = w * imgHeight / imgWidth;
                } else {
                    canvas.height = w;
                    canvas.width = w * imgWidth / imgHeight;
                }
            } else {
                canvas.width = imgWidth;
                canvas.height = imgHeight;
            }
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(newImage, 0, 0, canvas.width, canvas.height);
            var base64 = canvas.toDataURL(getMimeType(base64String), quality);
            return base64;
        });
        }

        function hoverOpenImg() {
            var img_show = null; // tips提示
            $('#autographpic').hover(function () {
                //alert($(this).attr('src'));
                var img = "<img class='img_msg' src='" + $(this).attr('src') + "' style='width:130px;' />";
                img_show = layer.tips(img, this, {
                    tips: [2, 'rgba(41,41,41,.5)']
                    , area: ['160px']
                });
            }, function () {
                layer.close(img_show);
            });
            $('#autographpic').attr('style', 'max-width:140px');
        }
    });
    exports('controller/basesetting/user/addUser', {});
});


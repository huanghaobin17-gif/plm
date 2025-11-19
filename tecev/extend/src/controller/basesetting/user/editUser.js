layui.define(function(exports){
    var table = {};
    layui.use('table', function () {
        table = layui.table;
    });
    layui.use(['layer', 'form', 'upload', 'formSelects', 'tipsType','laydate'], function () {
        var form = layui.form, upload = layui.upload, $ = layui.jquery, layer = layui.layer,laydate = layui.laydate, formSelects = layui.formSelects,
            tipsType = layui.tipsType;
        //初始化tips
        tipsType.choose();
        laydate.render({
            elem: '#validity' //指定元素
            ,min: today
        });
        //管理科室 多选框初始配置
        formSelects.render('department', selectParams(1));
        formSelects.btns('department', selectParams(2));

        //用户角色 多选框初始配置
        formSelects.render('character', selectParams(1));
        formSelects.btns('character', selectParams(2));
        hoverOpenImg();

        var thisbody = $('#LAY-BaseSetting-User-addUser');
        var is_supplier = parseInt(thisbody.find('input[name="is_supplier"]').val());
        form.verify({
            password: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (value) {
                    if (!/^(?![a-z]+$)(?![A-Z]+$)(?![0-9]+$)(?![\W_]+$)[a-zA-Z0-9\W_]{8,30}$/.test(value)) {
                        return '密码必须8到18位，且大写字母 小写字母 数字 特殊字符，四种包括两种,且不能出现空格';
                    }
                }
            },
            passwordconfirm: function (value) {
                if (value != $("input[name='password']").val()) {
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
            if(phone){
                if (!myreg.test(phone)) {
                    layer.msg("请输入有效的手机号码", {icon: 2}, 1000);
                    return false;
                }
            }
            submit($, params, 'editUser');
            return false;
        });
        if (!$("#addautograph")[0]) {
            $(".emptySign").css("cssText", "position: relative;top: 10px;")
        }

        //点击打开裁剪页面
        $('#addautograph').on('click',function () {
            var storage = window.localStorage;
            storage.setItem('picbase',"");
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
                end: function(){
                var storage = window.localStorage;
                if (storage['picbase']!="") {
                    compress(storage['picbase'], 200, 0.5).then(function (val) {
                 $('#autographpic').attr('src', val);
                 $('#autograph').attr('value', val);
                 $('#autographpic').attr('width', "140px");
                 $('#autographpic').attr('height', "40px");
                });
                    $(".emptySign").hide();
                $('#autographpic').css("display","inline");
                $('#addautograph').text('修改');
                }
          }
        })});

        //修改用户名
        $("#edit_name").on('click', function () {
            var old_name = $.trim($(this).attr('data-name'));
            layer.prompt({
                formType: 2,
                value: '',
                title: '请输入新的用户名',
                area: ['250px', '20px'] //自定义文本域宽高
            }, function (value, index, elem) {
                value = $.trim(value);
                if(value == old_name || !value){
                    layer.msg("请输入新的用户名", {icon: 2});
                    return false;
                }
                var params = {};
                params.old_name = old_name;
                params.new_name = value;
                $.ajax({
                    timeout: 0,
                    type: "POST",
                    url: 'chuna',
                    data: params,
                    dataType: "json",
                    async: true,
                    beforeSend:function () {
                        layer.load(2);
                    },
                    success: function (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg,{icon : 1},function () {
                                layer.close(index);
                                $("#uname").val(value);
                            });
                        }else{
                            layer.msg(data.msg,{icon : 2});
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败",{icon : 2});
                    },
                    complete:function () {
                        layer.closeAll('loading');
                    }
                });
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
        function hoverOpenImg(){
        var img_show = null; // tips提示
        $('#autographpic').hover(function(){
            //alert($(this).attr('src'));
            var img = "<img class='img_msg' src='"+$(this).attr('src')+"' style='width:130px;' />";
            img_show = layer.tips(img, this,{
                tips:[2, 'rgba(41,41,41,.5)']
                ,area: ['160px']
            });
        },function(){
            layer.close(img_show);
        });
            $("#autographpic").addClass('maxWidth')
        }
        form.on('select(manageDepartment)', function (data) {
            if (data.value == '') {
                $(".boldManger").hide();
            } else {
                $.getJSON(admin_name+'/User/editUser?type=getManager&departid=' + data.value, function (v) {
                    $(".boldManger").show();
                    if (!v) {
                        $(".manager").html('无')
                    } else {
                        $(".manager").html(v)
                    }
                });
            }
        });
    });

    exports('controller/basesetting/user/editUser', {});
});


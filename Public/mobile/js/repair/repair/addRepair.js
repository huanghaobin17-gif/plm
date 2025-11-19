layui.use(['form', 'element', 'upload'], function () {
    var form = layui.form;

    //初始化下方导航栏菜单
    menuListSpread();

    $(".revoice").click(function () {
        $(".reVoiceButton").hide();
        var targetdiv = $('.voiceButton').find('.record');
        $(targetdiv).attr('ontouchstart',"gtouchstart(event)");
        $(targetdiv).attr('ontouchmove',"gtouchmove(event)");
        $(targetdiv).attr('ontouchend',"gtouchend(event)");
        $('.record_title').css({"color":'#000000'});
    });

    //监听提交
    form.on('submit(save)', function (data) {
        var params = data.field;
        params.from=0;
        if(!params.breakdown){
            $.toptip('请填写故障描述', 'error');
            return false;
        }
        params.wxTapeAmr = $('#audio').attr('src');

        //获取上传文件信息
        var fileDataTr = $('.fileDataTr');
        if (fileDataTr.length > 0) {
            params.filename = '';
            $.each(fileDataTr, function (key, value) {
                params.filename += $(value).find('img').data('time')+'/'+$(value).find('img').data('save')+ ',';
            });
        }
        submit(params, addRepairUrl,mobile_name+'/Index/testindex.html');
        return false;
    });



    var filename = '';
    var add_picObj = $('.add_pic'), add_pic = add_picObj[0];
    var fileElemObj = $('#fileElem'), fileElem = fileElemObj[0];
    add_pic.addEventListener("click", function (e) {
        if (fileElem) {
            fileElem.click();
        }
        e.preventDefault(); // prevent navigation to "#"
    }, false);

    fileElemObj.change(function () {
        var file = this.files[0];
        filename = file['name'];
        var reader = new FileReader();
        reader.onload = function () {
            // 通过 reader.result 来访问生成的 DataURL
            var url = reader.result;
            setImageURL(url);
            layer.load(2);
            setTimeout(function () {
                uploadImage();
            }, 500);
        };
        reader.readAsDataURL(file);
    });

    var image = new Image();
    var targetWidth = targetHeight = 1000;
    image.onload = function () {
        // 图片原始尺寸
        var originWidth = this.width;
        var originHeight = this.height;
        // 最大尺寸限制，可通过国设置宽高来实现图片压缩程度
        var maxWidth = 1600,
            maxHeight = 1600;
        // 目标尺寸
        targetWidth = originWidth;
        targetHeight = originHeight;
        // 图片尺寸超过400x400的限制
        if (originWidth > maxWidth || originHeight > maxHeight) {
            if (originWidth / originHeight > maxWidth / maxHeight) {
                // 更宽，按照宽度限定尺寸
                targetWidth = maxWidth;
                targetHeight = Math.round(maxWidth * (originHeight / originWidth));
            } else {
                targetHeight = maxHeight;
                targetWidth = Math.round(maxHeight * (originWidth / originHeight));
            }
        }
    };
    function setImageURL(url) {
        var notFileDataTr = $('.notFileDataTr');
        if (notFileDataTr.length > 0) {
            notFileDataTr.remove();
        }
        var addFileTbody = $('.addFileTbody');
        image.src = url;
        var html = '<tr class="fileDataTr">';
        html += '<td class="fileName"><img src="' + image.src + '" style="display: none;"/></td>';
        html += '<td><div class="layui-btn layui-btn-xs layui-btn-danger del_file">移除</div></td>';
        addFileTbody.append(html);
    }

    function uploadImage() {
        var canvas = document.getElementById("myCanvas");
        var ctx = canvas.getContext("2d");
        ctx.imageSmoothingEnabled = true;
        img = $('.fileDataTr:last').find('img')[0];
        $("#myCanvas").attr('width', targetWidth);
        $("#myCanvas").attr('height', targetHeight);
        // 设置画布的实际渲染大小，只是简单的对画布进行缩放
        canvas.style.width = canvas.width;
        canvas.style.height = canvas.height;

        // 以实际渲染倍率来放大画布
        //canvas.width = canvas.width * ratio;
        //canvas.height = canvas.height * ratio;
        // 清除画布
        ctx.clearRect(0, 0, targetWidth, targetHeight);
        // 图片压缩
        ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
        /*第一个参数是创建的img对象；第二个参数是左上角坐标，后面两个是画布区域宽高*/
        //压缩后的图片base64 url
        /*canvas.toDataURL(mimeType, qualityArgument),mimeType 默认值是'image/jpeg';
         * qualityArgument表示导出的图片质量，只要导出为jpg和webp格式的时候此参数才有效果，默认值是0.92*/
        var data = canvas.toDataURL('image/jpeg', 0.99);
        data = data.split(',')[1];
        data = window.atob(data);
        var ia = new Uint8Array(data.length);
        for (var i = 0; i < data.length; i++) {
            ia[i] = data.charCodeAt(i);
        }

        // canvas.toDataURL 返回的默认格式就是 image/png
        var blob = new Blob([ia], {type: "image/png"});
        var fd = new FormData();

        fd.append('file', blob);
        fd.append('action', 'upload');
        fd.append('zm', 'canvas');
        fd.append('filename', filename);
        fd.append('i', i);
        $.ajax({
            url: addRepairUrl,
            type: "POST",
            data: fd,
            //beforeSend:beforeSend,
            processData: false,  //tell jQuery not to process the data
            contentType: false,  //tell jQuery not to set contentType
            success: function (res) {
                if (res.status === 1) {
                    $.toptip(res.msg, 'success');
                    setTimeout(function(){//两秒后跳转
                        $('.fileDataTr:last').remove();
                        var addFileTbody = $('.addFileTbody');
                        var html = '<tr class="fileDataTr">';
                        html += '<td class="fileName"><img src="'+res.file_url+'" data-save="'+res.save_name+'" data-name="'+res.file_name+'" data-type="'+res.file_type+'" data-time="'+res.thisDateTime+'"  data-size="'+res.file_size+'" style="display: "/>'+res.file_name+'</td>';
                        html += '<td><div class="layui-btn layui-btn-xs layui-btn-danger del_file">移除</div></td>';
                        addFileTbody.append(html);
                    },1000);
                } else {
                    $.toptip(res.msg, 'error');
                }
            },
            complete: complete
        });
    }

    //移除文件
    $(document).on('click', '.del_file', function () {
        var thisTr = $(this).parents('tr');
        var addFileTbody = $('.addFileTbody');
        thisTr.remove();
        if (addFileTbody.find('tr').length === 0) {
            addFileTbody.html('<tr class="notFileDataTr"><td colspan="4" style="text-align: center!important;">暂无数据</td></tr>');
        }
        $.toptip('移除成功', 'success');
        return false;
    });


});
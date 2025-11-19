layui.define(function(exports){
    layui.use(['element','form'], function(){
        var $ = layui.jquery,
            form = layui.form,element = layui.element; //Tab的切换功能，切换事件监听等，需要依赖element模块


        form.val("user_1_form", user_1_data);
        form.val("user_2_form", user_2_data);
        form.val("user_3_form", user_3_data);
        form.val("user_4_form", user_4_data);
        var show = [];
        show['assets'] = '设备名称：内热式软管自动罐装封口机';
        show['assnum'] = '设备编号：68010149901001';
        show['assorignum'] = '设备编号：545050292';
        show['category'] = '设备分类：医用X射线设备';
        show['department'] = '使用科室：中西医结合皮肤科';
        show['factorynum'] = '出厂编号：SCG11179416WA';
        show['model'] = '规格型号：PM-9000EXPRESS';
        show['opendate'] = '启用日期：2018-12-01';
        show['serialnum'] = '序 列 号：SN:BI3A1209322';
        show['storage_date'] = '入库日期：2019-01-01';
        show['remark'] = '设备备注：这是设备的备注信息';
        show['assetsrespon'] = '负 责 人：张三';
        show['zidingyi'] = '固定资产管理卡';
        show['hos_name'] = hospital_name;
        var forms = $('.layui-form');
        $.each(forms,function (index,item) {
            var classname = $(this).attr('lay-filter');
            var tar = $('.'+classname).find('.cus_sel').find('select');
            $.each(tar,function (index1,item1) {
                var filter = $(this).attr('lay-filter');
                var i = filter.substring(7,8);
                form.on("select("+filter+")", function(data){
                    if($(this).parents('.layui-form').find('.czsy_table').length >= 1){
                        var tr = $(this).parents('.layui-form').find('.czsy_table').find('tr')[i-1];
                        if(show[data.value].indexOf('：') >= 0){
                            var strs = new Array(); //定义一数组
                            strs = show[data.value].split("：");
                            $(tr).find('td:first').html(strs[0]);
                            $(tr).find('td:nth-child(2)').find('div').html(strs[1]);
                        }else{
                            if(data.value == 'zidingyi'){
                                layer.prompt({
                                    formType: 2,
                                    value: '',
                                    title: '请输入自定义值',
                                    area: ['200px', '40px'] //自定义文本域宽高
                                }, function(value, index, elem){
                                    $(tr).find('td:first').html(value);
                                    $('input[name="zidingyi_value"]').val(value);
                                    layer.close(index);
                                });
                            }else{
                                $(tr).find('td:first').html(hospital_name);
                            }
                        }
                    }else{
                        var tr = $(this).parents('.layui-form').find('.show_table').find('tr')[i-1];
                        $(tr).find('td:first').html(show[data.value]);
                    }
                });
            });
        });
        form.on("select(pic_width)", function(data){
            var classname = $(this).parents('.layui-form').find('div:first').attr('class');
            $('.'+classname).find('img').css('width',data.value);
        });
        form.on("select(font_size)", function(data){
            var classname = $(this).parents('.layui-form').find('div:first').attr('class');
            $('.'+classname).find('td').css('font-size',data.value+'px');
            $('.'+classname).find('table').find('div').css('font-size',data.value+'px');
        });

        //监听提交
        form.on('submit(user_1_save)', function (data) {
            var params = data.field;
            params.temp_name = 'user_1';
            var imgwidth = params.pic_width;
            var imgtop = 0;
            var imghtml = '<img src="/Public/images/show_qrcode_1.png" style="width: '+imgwidth+'px;margin-top: '+imgtop+'px;"/>';
            var html = '<div class="user_1">\n' +
                '    <div class="hospital_name"></div>\n' +
                '    <div class="hospital_info">\n' +
                '        <table class="show_table">\n' +
                '            <tbody>\n' +
                '            <tr>\n' +
                '                <td colspan="2" class="line_height"></td>\n' +
                '            </tr>\n' +
                '             <tr>\n' +
                '                <td colspan="2" class="line_height"></td>\n' +
                '            </tr>\n' +
                '            <tr>\n' +
                '                <td colspan="2" class="line_height"></td>\n' +
                '            </tr>\n' +
                '            <tr>\n' +
                '                <td class="row_td line_height"></td>\n' +
                '                <td class="td_img img_line_height" rowspan="3">\n' +imghtml+
                '                </td>\n' +
                '            </tr>\n' +
                '            <tr>\n' +
                '                <td class="row_td line_height"></td>\n' +
                '            </tr>\n' +
                '            <tr>\n' +
                '                <td class="row_td line_height"></td>\n' +
                '            </tr>\n' +
                '            </tbody>\n' +
                '        </table>\n' +
                '    </div>\n' +
                '</div>\n' +
                '<div class="layui-btn-group" style="width:100%;text-align: center;margin-top:10px;">\n' +
                '      <button type="button" class="layui-btn layui-btn-sm default" style="margin: 0 0 0 60px;">\n' +
                '         <i class="layui-icon layui-icon-zsave"></i>设为默认\n' +
                '      </button>\n' +
                '      <button type="button" class="layui-btn layui-btn-sm layui-btn-normal print_test" style="margin: 0 0 0 60px;">\n' +
                '         <i class="layui-icon layui-icon-zprinter-l" style="font-size: 16px;"></i>打印测试\n' +
                '       </button>\n' +
                '       <button type="button" class="layui-btn layui-btn-sm layui-btn-danger delete" style="margin: 0 0 0 60px;">\n' +
                '           <i class="layui-icon">&#xe640;</i>删除标签\n' +
                '       </button>\n'+
                '</div>';
            params.action = 'design_label';
            params.temp_content = html;
            submit($,params,'design');
            return false;
        });

        //监听提交
        form.on('submit(user_2_save)', function (data) {
            var params = data.field;
            params.temp_name = 'user_2';
            var imgwidth = params.pic_width;
            var imgtop = 0;
            var imghtml = '<img src="/Public/images/show_qrcode_1.png" style="width: '+imgwidth+'px;margin-top: '+imgtop+'px;"/>';
            var html = '<div class="user_2">\n' +
                '    <div class="hospital_name"></div>\n' +
                '    <div class="hospital_info">\n' +
                '        <table class="show_table">\n' +
                '            <tbody>\n' +
                '            <tr>\n' +
                '                <td colspan="2" class="line_height"></td>\n' +
                '            </tr>\n' +
                '            <tr>\n' +
                '                <td colspan="2" class="line_height"></td>\n' +
                '            </tr>\n' +
                '            <tr>\n' +
                '                <td class="row_td line_height"></td>\n' +
                '                <td class="td_img img_line_height" rowspan="4">\n' +imghtml+
                '                </td>\n' +
                '            </tr>\n' +
                '            <tr>\n' +
                '                <td class="row_td line_height"></td>\n' +
                '            </tr>\n' +
                '            <tr>\n' +
                '                <td class="row_td line_height"></td>\n' +
                '            </tr>\n' +
                '            <tr>\n' +
                '                <td class="row_td line_height"></td>\n' +
                '            </tr>\n' +
                '            </tbody>\n' +
                '        </table>\n' +
                '    </div>\n' +
                '</div>' +
                '<div class="layui-btn-group" style="width:100%;text-align: center;margin-top:10px;">\n' +
                '      <button type="button" class="layui-btn layui-btn-sm default" style="margin: 0 0 0 60px;">\n' +
                '         <i class="layui-icon layui-icon-zsave"></i>设为默认\n' +
                '      </button>\n' +
                '      <button type="button" class="layui-btn layui-btn-sm layui-btn-normal print_test" style="margin: 0 0 0 60px;">\n' +
                '         <i class="layui-icon layui-icon-zprinter-l" style="font-size: 16px;"></i>打印测试\n' +
                '       </button>\n' +
                '       <button type="button" class="layui-btn layui-btn-sm layui-btn-danger delete" style="margin: 0 0 0 60px;">\n' +
                '           <i class="layui-icon">&#xe640;</i>删除标签\n' +
                '       </button>\n'+
                '</div>';
            params.action = 'design_label';
            params.temp_content = html;
            submit($,params,'design');
            return false;
        });

        //监听提交
        form.on('submit(user_3_save)', function (data) {
            var params = data.field;
            params.temp_name = 'user_3';
            var imgwidth = params.pic_width;
            var imgtop = 0;
            var imghtml = '<img src="/Public/images/show_qrcode_1.png" style="width: '+imgwidth+'px;margin-top: '+imgtop+'px;"/>';
            var html = '<div class="user_3">\n' +
                '    <div class="hospital_name" style="font-size:16px;height:18px;line-height:18px;padding-right: 10px;font-family: 宋体;">郴州市第三人民医院</div>\n' +
                '    <div class="hospital_info">\n' +
                '        <table class="czsy_table">\n' +
                '            <tbody>\n' +
                '            <tr>\n' +
                '                <td class="czsy_title"></td>\n' +
                '                <td><div class="czsy_con"></div></td>\n' +
                '            </tr>\n' +
                '            <tr>\n' +
                '                <td class="czsy_title"></td>\n' +
                '                <td><div class="czsy_con"></div></td>\n' +
                '            </tr>\n' +
                '            <tr>\n' +
                '                <td class="czsy_title"></td>\n' +
                '                <td><div class="czsy_con"></div></td>\n' +
                '            </tr>\n' +
                '            <tr>\n' +
                '                <td class="czsy_title"></td>\n' +
                '                <td><div class="czsy_con"></div></td>\n' +
                '            </tr>\n' +
                '            <tr>\n' +
                '                <td class="czsy_title"></td>\n' +
                '                <td><div class="czsy_con"></div></td>\n' +
                '            </tr>\n' +
                '            </tbody>\n' +
                '        </table>\n' +
                '    </div>\n' +
                '    <div class="czsy_barcode">\n' +imghtml+
                '        <div style="color: #000000;font-size: 12px;width: 105px;overflow: hidden;">6858011371866</div>\n' +
                '    </div>\n' +
                '</div>\n' +
                '<div class="layui-btn-group" style="width:100%;text-align: center;margin-top:10px;">\n' +
                '    <button type="button" class="layui-btn layui-btn-sm default" style="margin: 0 0 0 60px;">\n' +
                '        <i class="layui-icon layui-icon-zsave"></i>设为默认\n' +
                '    </button>\n' +
                '    <button type="button" class="layui-btn layui-btn-sm layui-btn-normal print_test" style="margin: 0 0 0 60px;">\n' +
                '        <i class="layui-icon layui-icon-zprinter-l" style="font-size: 16px;"></i>打印测试\n' +
                '    </button>\n' +
                '    <button type="button" class="layui-btn layui-btn-sm layui-btn-danger delete" style="margin: 0 0 0 60px;">\n' +
                '       <i class="layui-icon">&#xe640;</i>删除标签\n' +
                '</button>\n' +
                '</div>\n';
            params.action = 'design_label';
            params.temp_content = html;
            submit($,params,'design');
            return false;
        });

        //监听提交
        form.on('submit(user_4_save)', function (data) {
            var params = data.field;
            params.temp_name = 'user_4';
            var imgwidth = params.pic_width;
            var imgtop = 0;
            var imghtml = '<img src="/Public/images/show_qrcode_1.png" style="width: '+imgwidth+'px;margin-top: '+imgtop+'px;"/>';
            var html = '<div class="user_4">\n' +
                '            <table class="czsy_table">\n' +
                '               <tbody>\n' +
                '                  <tr>\n' +
                '                      <td colspan="2"></td>\n' +
                '                      <td rowspan="6">' +imghtml+'</td>\n' +
                '                  </tr>\n' +
                '                  <tr>\n' +
                '                      <td colspan="2"></td>\n' +
                '                  </tr>\n' +
                '                  <tr>\n' +
                '                      <td class="czsy_title"></td>\n' +
                '                      <td><div class="czsy_con"></div></td>\n' +
                '                  </tr>\n' +
                '                  <tr>\n' +
                '                      <td class="czsy_title"></td>\n' +
                '                      <td><div class="czsy_con"></div></td>\n' +
                '                  </tr>\n' +
                '                  <tr>\n' +
                '                      <td class="czsy_title"></td>\n' +
                '                      <td><div class="czsy_con"></div></td>\n' +
                '                  </tr>\n' +
                '                  <tr>\n' +
                '                      <td class="czsy_title"></td>\n' +
                '                      <td><div class="czsy_con"></div></td>\n' +
                '                  </tr>\n' +
                '                  <tr>\n' +
                '                      <td class="czsy_title"></td>\n' +
                '                      <td><div class="czsy_con"></div></td>\n' +
                '                      <td><div class="bot_assnum">68560211933</div></td>\n' +
                '                 </tr>\n' +
                '              </tbody>\n' +
                '          </table>\n' +
                '</div>\n'+
                '<div class="layui-btn-group" style="width:100%;text-align: center;margin-top:10px;">\n' +
                '    <button type="button" class="layui-btn layui-btn-sm default" style="margin: 0 0 0 60px;">\n' +
                '        <i class="layui-icon layui-icon-zsave"></i>设为默认\n' +
                '    </button>\n' +
                '    <button type="button" class="layui-btn layui-btn-sm layui-btn-normal print_test" style="margin: 0 0 0 60px;">\n' +
                '        <i class="layui-icon layui-icon-zprinter-l" style="font-size: 16px;"></i>打印测试\n' +
                '    </button>\n' +
                '    <button type="button" class="layui-btn layui-btn-sm layui-btn-danger delete" style="margin: 0 0 0 60px;">\n' +
                '       <i class="layui-icon">&#xe640;</i>删除标签\n' +
                '</button>\n' +
                '</div>\n';
            params.action = 'design_label';
            params.temp_content = html;
            submit($,params,'design');
            return false;
        });
    });
    exports('controller/assets/print/design_label', {});
});

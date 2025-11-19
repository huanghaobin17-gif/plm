layui.define(function(exports){

    layui.use(['layer', 'form'], function() {
        var form = layui.form, $ = layui.jquery, layer = layui.layer;
        layer.config(layerParmas());
        form.on('checkbox(allChoose)', function(data){
            if (data.elem.checked){
                $(this).parent().parent().find("input[name='level2']").prop('checked',true);
                $(this).parent().parent().find("input[name='level2']").prop('disabled',true);
            }else {
                $(this).parent().parent().find("input[name='level2']").prop('checked',false);
                $(this).parent().parent().find("input[name='level2']").prop('disabled',false);
            }
            form.render('checkbox');
        });
        form.verify({
            name: function (value) {
                if (value){
                    if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                        return '模板名称首尾不能出现下划线\'_\'';
                    }
                    if (/^\d+\d+\d$/.test(value)) {
                        return '模板名称不能全为数字';
                    }
                }else {
                    return '模板名称不能为空';
                }
            }
        });
        //监听提交
        form.on('submit(show)', function (data) {
            var level2 = "";
            $("input[name='level2']:checked").each(function(){
                if(level2==''){
                    level2 = $(this).val();
                }else{
                    level2 += ","+$(this).val();
                }
            });
            var level1 = "";
            $("input[name='level1']:checked").each(function(){
                if(level1==''){
                    level1 = $(this).val();
                }else{
                    level1 += ","+$(this).val();
                }
            });
            var level3 = "";
            $("input[name='level3order']").each(function(){
                if(level3==''){
                    level3 = $(this).val();
                }else{
                    level3 += ","+$(this).val();
                }
            });
            params = data.field;
            params.level3 = level3;
            params.level1 = level1;
            params.level2 = level2;
            //拼接弹出ID
            data.field['layerId']+=','+parent.layer.getFrameIndex(window.name);
            var url = '';
            if (params.update == 1){
                url = admin_name+'/PatrolSetting/editTemplate?name='+data.field['name']+'&level1='+data.field['level1']+'&level2='+data.field['level2']+'&level3='+data.field['level3']+'&update='+data.field['update']+'&tpid='+data.field['tpid']+'&layerId='+data.field['layerId']+'&type=confirmAdd';
            }else {
                url = admin_name+'/PatrolSetting/addTemplate?name='+data.field['name']+'&level1='+data.field['level1']+'&level2='+data.field['level2']+'&level3='+data.field['level3']+'&update='+data.field['update']+'&tpid='+data.field['tpid']+'&layerId='+data.field['layerId']+'&type=confirmAdd';
            }
            //下一步
            top.layer.open({
                id: 'shows',
                type: 2,
                title: $(this).html(),
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar: false,
                area: ['75%', '100%'],
                closeBtn: 1,
                content: [url]
            });
            return false;
        })
    });

//返回上一步
    $("#prev").click(function () {
        var index = parent.layer.getFrameIndex(window.name);
        parent.layer.close(index);
    });

//移除一行
    function delRow(k){
        $(k).parent().parent().remove();
    }

    function ontop(a) {
        //置顶
        var tr = $(a).parents("tr");
        tr.fadeOut().fadeIn();
        $(a).parents("table").prepend(tr);
    }
    exports('controller/patrol/patrolsetting/next', {});
});






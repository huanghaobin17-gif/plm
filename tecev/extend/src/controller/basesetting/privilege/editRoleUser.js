layui.define(function(exports){
    layui.use(['layer', 'form'], function() {
        var form = layui.form, $ = layui.jquery;
        //监听提交
        form.on('submit(save)', function (data) {
            var roleid_edit=get_checkbox_notchecked('roleid_edit'),roleid_add=get_checkbox('roleid_add'),roleid=$("input[name='roleid']").val();
            var data = {roleid_edit:roleid_edit,roleid_add:roleid_add,roleid:roleid};
            submit($,data,'editRoleUser');
            return false;
        })
    });

    function get_checkbox(text){
        var chk_value =[];
        $('input[name="'+text+'"]:checked').each(function(){
            chk_value.push($(this).val());
        });
        chk_value = chk_value.join(",");
        return chk_value;
    }

    function get_checkbox_notchecked(text){
        var chk_value =[];
        $('input[name="'+text+'"]:not(:checked)').each(function(){
            chk_value.push($(this).val());
        });
        chk_value = chk_value.join(",");
        return chk_value;
    }
    exports('controller/basesetting/privilege/editRoleUser', {});
});

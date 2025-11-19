/**
 * Created by jinlong on 2017/3/8.
 */
/*function checkData(){
    var rolename  = $("input[name='role']").val();
    rolename      = trim(rolename,'g');
    var modulearr = [];
    for(var i = 1;i < 10;i++){
        var mName = 'moduleid['+i+'][0]';
        $('input[name="'+mName+'"]:checked').each(function(){
            console.log(66);
            modulearr.push($(this).val());//向数组中添加元素
        });
    }
    if(rolename != ''){
        if(modulearr.length == 0){
            $.dialog({
                icon: 'error_24.gif',
                title:false,
                heght: '5em',
                time: 1,
                content: '请选择角色权限'
            });
            return false;
        }
    }else{
        if(modulearr.length > 0){
            $.dialog({
                icon: 'error_24.gif',
                title:false,
                heght: '5em',
                time: 1,
                content: '请输入角色名称'
            });
            return false;
        }
    }
}
*/

/**
 * Created by chouxiaoya on 2017/3/18.
 */
$(function(){
   $("#getMore").click(function(){
       if($(".assHid").css("display")=="none"){
           $(".assHid").css("display","block");
           $("#getMore").html('收起');
           return false;
       }else{
           $(".assHid").css("display","none");
           $("#getMore").html('展开');
           return false;
       }
   });
    $(".ui-label").click(function(){
        var content = $(this).html();
        $("textarea[name='breakdown']").val('');
        $("textarea[name='breakdown']").val(content);
    });
});
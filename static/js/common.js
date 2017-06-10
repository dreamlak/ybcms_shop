/**
 * 获取多级联动的商品分类
 */
function get_category(id,next,select_id){
	require(['think'],function(){
	    var url = Think.U('Common/Api/get_category','parent_id='+ id);
	    $.ajax({
	        type : "GET",
	        url  : url,
	        error: function(request) {
	            alert("服务器繁忙, 请联系管理员!");
	            return;
	        },
	        success: function(v) {
				v = "<option value='0'>请选择商品分类</option>" + v;
	            $('#'+next).empty().html(v);
				(select_id > 0) && $('#'+next).val(select_id);//默认选中
	        }
	    });
    });
}
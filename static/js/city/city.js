//获取省份
function getProvince(){
    var url = '/index.php/Common/Area/getAreas/pid/0';
    $.ajax({
        type : "GET",
        url  : url,
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return;
        },
        success: function(v) {
            v = '<option value="0">选择省份</option>'+ v;
            $('#province').empty().html(v);
        }
    });
}

/**
 * 获取城市
 * @param t  省份select对象
 */
function getCity(t){
    var pid = $(t).val();
    if(!pid > 0){
        return;
    }
    $('#district').empty().css('display','none');
    $('#twon').empty().css('display','none');
    var url = '/index.php/Common/Area/getAreas/pid/'+ pid;
    $.ajax({
        type : "GET",
        url  : url,
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return;
        },
        success: function(v) {
            v = '<option value="0">选择城市</option>'+ v;
            $('#city').empty().html(v);
        }
    });
}

/**
 * 获取地区
 * @param t  城市select对象
 */
function getArea(t){
    var pid = $(t).val();
    if(!pid > 0){
        return;
    }
    $('#district').empty().css('display','inline');
    $('#twon').empty().css('display','none');
    var url = '/index.php/Common/Area/getAreas/pid/'+ pid;
    $.ajax({
        type : "GET",
        url  : url,
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return;
        },
        success: function(v) {
            v = '<option>选择区域</option>'+ v;
            $('#district').empty().html(v);
        }
    });
}

//获取最后一级乡镇
function getTwon(obj){
    var pid = $(obj).val();
    var url = '/index.php/Common/Area/getAreas/pid/'+ pid;
    $.ajax({
        type : "GET",
        url  : url,
        success: function(res) {
            if(parseInt(res) == 0){
                $('#twon').empty().css('display','none');
            }else{
                $('#twon').css('display','inline');
                $('#twon').empty().html(res);
            }
        }
    });
}
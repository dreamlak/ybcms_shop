function scu(key,isexpires) 
{
    try
    {
        var doCan = false;
        if (document.cookie.length > 0) {
            c_start = document.cookie.indexOf(key+"=")
            if (c_start != -1) {
                doCan = true;
            }
        }
        if(!doCan)
        {
            var exdate = new Date();
            exdate.setDate(exdate.getDate() + 1024);
			var ep ='';
			if(isexpires)
			{
				ep = "expires=" + exdate.toGMTString() + ";";
			}
            document.cookie = key + "=" + escape((Math.random() + "").substr(2)) + ";domain=360che.com;"+ep+"path=/;";
			//
        }
    }
    catch(e){}
}
scu("udstatistics",true);
scu("epnonestats",false);

var browser = navigator.appName
var b_version = navigator.appVersion
var ie_version = false;
if (b_version.indexOf("MSIE 6.0") > 0 || b_version.indexOf("MSIE 7.0") > 0 || b_version.indexOf("MSIE 8.0") > 0) {
    ie_version = true;
}
var src = "http:\/\/stats.360che.com\/tj.gif?sw=" + screen.width + "&sc=" + screen.height + "&referer=" + escape(document.referrer) + "&page=" + escape(document.URL)+"&site="+thpi.site+"&dl="+thpi.product.c+"&ct="+thpi.product.sc+"&sr="+thpi.product.sr+"&br="+thpi.product.br+"&p="+thpi.product.p+"&ts="+new Date().getTime() + "";

if (browser == "Microsoft Internet Explorer" && ie_version) {
    var iframe = document.createElement("<img width=0 height=0 id=360chedf src=" + decodeURI(src) + " style=display:none; />");
    document.body.appendChild(iframe);
} else {
    var iframe = document.createElement("img");
    iframe.setAttribute("id", "360chedf");
    iframe.setAttribute("src", decodeURI(src));
    iframe.setAttribute("width", "0");
    iframe.setAttribute("height", "0");
    iframe.setAttribute("style", "display:none;");
    document.body.appendChild(iframe);
}

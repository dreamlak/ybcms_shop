(function(window) {
	// 当domReady的时候开始初始化
	var uploadfile={},callback=null;
	uploadfile.files = function(parame,callback){
		if(!parame) parame = {};
		//定义变量
	    var $wrap = $('#uploader'),
	        // 容器
	        $queue = $('<ul class="filelist"></ul>').appendTo($wrap.find('.queueList')),
	        // 状态栏，包括进度和控制按钮
	        $statusBar = $wrap.find('.statusBar'),
	        // 文件总体选择信息。
	        $info = $statusBar.find('.info'),
	        // 上传按钮
	        $upload = $wrap.find('.uploadBtn'),
	        // 没选择文件之前的内容。
	        $placeHolder = $wrap.find('.placeholder'),
	        $progress = $statusBar.find('.progress').hide(),
	        // 显示添加的文件数量
	        fileCount = 0,
	        // 显示添加的文件总大小
	        fileSize = 0,
	        // 优化retina, 在retina下这个值是2
	        ratio = window.devicePixelRatio || 1,
	        // 缩略图大小
	        thumbnailWidth = 110 * ratio,
	        thumbnailHeight = 110 * ratio,
	        // 可能有pedding, ready, uploading, confirm, done.
	        state = 'pedding',
	        // 所有文件的进度信息，key为file id
	        percentages = {},
	        // 判断浏览器是否支持图片的base64
	        isSupportBase64 = (function(){
	            var data = new Image();
	            var support = true;
	            data.onload = data.onerror = function(){
	                if( this.width != 1 || this.height != 1 ){
	                    support = false;
	                }
	            }
	            data.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";
	            return support;
	        })(),
	
	        // 检测是否已经安装flash，检测flash的版本
	        flashVersion = ( function() {
	            var version;
	            try {
	                version = navigator.plugins[ 'Shockwave Flash' ];
	                version = version.description;
	            } catch ( ex ) {
	                try {
	                    version = new ActiveXObject('ShockwaveFlash.ShockwaveFlash')
	                            .GetVariable('$version');
	                } catch ( ex2 ) {
	                    version = '0.0';
	                }
	            }
	            version = version.match( /\d+/g );
	            return parseFloat( version[ 0 ] + '.' + version[ 1 ], 10 );
	        })(),
	
	        // 定义WebUploader实例
	        uploader;
	    
		require(['webuploader'],function(WebUploader){
		    if (!WebUploader.Uploader.support('flash') && WebUploader.browser.ie ){
		        // flash 安装了但是版本过低。
		        if (flashVersion) {
		            (function(container){
		                window['expressinstallcallback'] = function( state ){
		                    switch(state) {
		                        case 'Download.Cancelled':
		                            alert('您取消了更新！')
		                            break;
		                        case 'Download.Failed':
		                            alert('安装失败')
		                            break;
		                        default:
		                            alert('安装已成功，请刷新！');
		                            break;
		                    }
		                    delete window['expressinstallcallback'];
		                };
		                var swf = '/static/js/webuploader/js/expressInstall.swf';
		                // insert flash object
		                var html = '<object type="application/x-shockwave-flash" data="' +  swf + '" ';
		                if (WebUploader.browser.ie) {
		                    html += 'classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" ';
		                }
		                html += 'width="100%" height="100%" style="outline:0">'  +
		                    '<param name="movie" value="' + swf + '" />' +
		                    '<param name="wmode" value="transparent" />' +
		                    '<param name="allowscriptaccess" value="always" />' +
		                '</object>';
		                container.html(html);
		            })($wrap);
		        // 压根就没有安转。
		        } else {
		            $wrap.html('<a href="http://www.adobe.com/go/getflashplayer" target="_blank" border="0"><img alt="get flash player" src="http://www.adobe.com/macromedia/style_guide/images/160x41_Get_Flash_Player.jpg" /></a>');
		        }
		        return;
		    } else if (!WebUploader.Uploader.support()) {
		        alert( '您的浏览器不支持上传文件！请升级或换谷歌浏览器');
		        return;
		    }
		
		    // 实例化
		    uploader = WebUploader.create({
				dnd: '#dndArea',//拖拽的容器的ID
				// 禁掉全局的拖拽功能。这样不会出现图片拖进页面的时候，把图片打开。
		        disableGlobalDnd: true,
		        paste: '#uploader',
		    	//指定选择文件的按钮容器,不指定则不创建按钮。
				pick: {
					id: '#filePicker',
					label: '点击选择文件',
					multiple:true //是否开起同时选择多个文件能力。
				},
		        swf: '/static/js/webuploader/js/Uploader.swf',
		        server: Think.U('common/Fileupload/fileupload'),
		        formData: {
					uptypes:parame.filetype,
					ismark:parame.ismark,
		            adminid:parame.adminid,
		            userid:parame.userid,
		            dir:parame.dir,
		        },
		        accept:{title:parame.filetype,extensions:parame.ext},//文件类型
				fileVal:'file',//设置文件上传域的name
				//chunked: true,//是否要分片处理大文件上传。
				//chunkSize: 1024 * 1024 * 5,//分片 默认大小为5M
				//chunkRetry:3,//如果某个分片由于网络问题出错，允许自动重传多少次？
				threads:5,//上传并发数。允许同时最大上传进程数
		        fileNumLimit: parame.filenum,//验证文件总数量, 超出则不允许加入队列。
				fileSizeLimit: 500 * 1024 * 1024,// 500M  验证文件总大小是否超出限制, 超出则不允许加入队列
				fileSingleSizeLimit: parame.upfilesize,// 50M  验证单个文件大小是否超出限制, 超出则不允许加入队列。
				duplicate:true,//重复
				auto:false	//是否自动上传
		    });
		
		    // 拖拽时不接受 js, txt 文件。
		    uploader.on('dndAccept', function( items ) {
		        var denied = false,
		            len = items.length,
		            i = 0,
		            // 修改js类型
		            unAllowed = 'text/plain;application/javascript ';
		        for ( ; i < len; i++ ) {
		            // 如果在列表里面
		            if ( ~unAllowed.indexOf( items[ i ].type ) ) {
		                denied = true;
		                break;
		            }
		        }
		        return !denied;
		    });
		    // 添加“添加文件”的按钮，
			uploader.addButton({
				id: '#filePicker2',
				label: '继续添加'
			});
		    uploader.on('ready', function() {
		        window.uploader = uploader;
		    });
		
		    // 当有文件添加进来时执行，负责view的创建
		    function addFile(file) {
		        var $li = $(
		        		'<li id="' + file.id + '">' +
		                '	<p class="title">' + file.name + '</p>' +
		                '	<p class="imgWrap"></p>'+
		                '	<p class="progress"><span></span></p>' +
		                '</li>'
		                ),
		            $btns = $(
		            	'<div class="file-panel">' +
		                '	<span class="cancel">删除</span>' +
		                '</div>'
		                ).appendTo($li),
		            $prgress = $li.find('p.progress span'),
		            $wrap = $li.find( 'p.imgWrap' ),
		            $info = $('<p class="error"></p>'),
		
		            showError = function( code ) {
		                switch( code ) {
		                    case 'exceed_size':
		                        text = '文件大小超出';
		                        break;
		                    case 'interrupt':
		                        text = '上传暂停';
		                        break;
		                    case 'invalid':
		                        text = '文件格式不合格';
		                        break;
		                    default:
		                        text = '上传失败，请重试';
		                        break;
		                }
		                $info.text(text).appendTo($li);
		            };
				
		        if (file.getStatus() === 'invalid') {
		            showError(file.statusText);
		        }else{
		            $wrap.text('预览中...');
		            uploader.makeThumb(file,function(error,src) {
		                var img;
		                if(error){
		                	if('jpg,jpeg,gif,png,bmp,pdf'.indexOf(file.ext)>0){
                            	img = $('<img src="'+response.result+'">');
                            	$wrap.empty().append(img);
                           	}else if('rar,zip,doc,docx,xls,xlsx,ppt,pptx'.indexOf(file.ext)>0){
                           		img = $('<i class="fa fa-file-text-o"></i>');
                            	$wrap.empty().append(img);
                           	}else if('mp4,vai,rmvb,wma'.indexOf(file.ext)>0){
                           		img = $('<i class="fa fa-video-camera"></i>');
                            	$wrap.empty().append(img);
                           	}else if('mp3,wmv'.indexOf(file.ext)>0){
                           		img = $('<i class="fa fa-music"></i>');
                            	$wrap.empty().append(img);
                           	}else{
                           		$wrap.text("不能预览");
                           	}
		                    return;
		                }
		                //针对于图片类型文件预览(支持Base64数据直接预览)
		                if(isSupportBase64 ){
		                    img = $('<img src="'+src+'">');
		                    $wrap.empty().append(img);
		                }else{//不支持从服务端获取
		                    $.ajax(Think.U('common/Fileupload/filepreview'),{
		                        method: 'POST',
		                        data: src,
		                        dataType:'json'
		                    }).done(function(response) {
		                        if(response.result){
		                        	img = $('<img src="'+response.result+'">');
	                            	$wrap.empty().append(img);
		                        }else{
		                            $wrap.text("不能预览");
		                        }
		                    });
		                }
		            }, thumbnailWidth, thumbnailHeight );
		            percentages[ file.id ] = [ file.size, 0 ];
		            file.rotation = 0;
		        }
				//文件信息(状态，说明)
		        file.on('statuschange', function(cur,prev){
		            if (prev === 'progress'){//上传中,进度条准备
		                $prgress.hide().width(0);
		            }else if(prev === 'queued'){//上传中
		                $li.off('mouseenter mouseleave');//文件项鼠标事件失效，同时删除按钮失效
						$btns.remove();//删除移出
		            }
		
		            // 上传出错、文件不合格
		            if (cur === 'error' || cur === 'invalid') {
		                console.log(file.statusText);
		                showError('未知错误');
		                percentages[file.id][ 1 ] = 1;
		            } else if (cur === 'interrupt'){//上传中断，可续传。
		                showError( 'interrupt' );
		            } else if (cur === 'queued'){//已经进入队列, 等待上传
		                percentages[file.id][ 1 ] = 0;
		            } else if (cur === 'progress') {//上传中
		                $info.remove();
		                $prgress.css('display','block');//进度条显示
		            } else if (cur === 'complete') {//上传完成
		                $li.append('<span class="success"></span>');//显示右下角打钩
		            }
		            $li.removeClass('state-' + prev).addClass('state-' + cur);
		        });
				//当鼠标指针穿过元素时
		        $li.on('mouseenter', function() {
		            $btns.stop().animate({height: 30});
		        });
				//当鼠标指针离开时
		        $li.on('mouseleave', function() {
		            $btns.stop().animate({height: 0});
		        });
				//操作删除时
		        $btns.on('click','span', function(){
		            uploader.removeFile(file);
		        });
		        $li.appendTo($queue);
		    }
		
		    // 负责view的销毁
		    function removeFile( file ) {
		        var $li = $('#'+file.id);
		        delete percentages[file.id];
		        updateTotalProgress();
		        $li.off().find('.file-panel').off().end().remove();
		    }
			//进度
		    function updateTotalProgress() {
		        var loaded = 0,
		            total = 0,
		            spans = $progress.children(),
		            percent;
		
		        $.each( percentages, function( k, v ) {
		            total += v[ 0 ];
		            loaded += v[ 0 ] * v[ 1 ];
		        } );
		        percent = total ? loaded / total : 0;
		        spans.eq( 0 ).text( Math.round( percent * 100 ) + '%' );
		        spans.eq( 1 ).css( 'width', Math.round( percent * 100 ) + '%' );
		        updateStatus();
		    }
			
		    function updateStatus() {
		        var text = '', stats;
		        if(state === 'ready'){//准备状态时
		            text = '添加' + fileCount + '个文件，共' +
		                    WebUploader.formatSize( fileSize ) + '。';
		        }else if(state === 'confirm') {//确认状态时
		            stats = uploader.getStats();//各种状态的个数
		            if ( stats.uploadFailNum ) {
		                text = '已成功上传' + stats.successNum+ '个文件，'+
		                    stats.uploadFailNum + '个文件上传失败，<a class="retry" href="javascript:">重新上传</a> 失败文件或<a class="ignore" href="javascript:">忽略</a>'
		            }
		        }else{
		            stats = uploader.getStats();
		            text = '共' + fileCount + '个（' +
		                    WebUploader.formatSize( fileSize )  +
		                    '），已上传' + parseInt(stats.successNum) + '个';
		            if ( stats.uploadFailNum ) {
		                text += '，失败' + stats.uploadFailNum + '个';
		            }
		        }
		        $info.html(text);
		    }
			//铵钮状态
		    function setState( val ) {
		        var file, stats;
		        if ( val === state ) {
		            return;
		        }
		        $upload.removeClass( 'state-' + state );
		        $upload.addClass( 'state-' + val );
		        state = val;
		        switch (state) {
		            case 'pedding'://初始状态
		                $placeHolder.removeClass('element-invisible');
		                $queue.hide();
		                $statusBar.addClass('element-invisible');
		                uploader.refresh();
		                break;
		            case 'ready'://准备状态
		                $placeHolder.addClass( 'element-invisible' );
		                $('#filePicker2').removeClass( 'element-invisible');
		                $queue.show();
		                $statusBar.removeClass('element-invisible');
		                uploader.refresh();
		                break;
		            case 'uploading'://上传中状态
		                $('#filePicker2').addClass('element-invisible');
		                $progress.show();
		                $upload.text('暂停上传');
		                break;
		            case 'paused':
		                $progress.show();
		                $upload.text('继续上传');
		                break;
		            case 'confirm':
		                $progress.hide();
		                $('#filePicker2').removeClass( 'element-invisible' );
		                $upload.text('开始上传');
		                stats = uploader.getStats();
		                if ( stats.successNum && !stats.uploadFailNum ) {
		                    setState('finish');
		                    return;
		                }
		                break;
		            case 'finish':
		                stats = uploader.getStats();
		                if (stats.successNum ) {
		                	//alert( '上传成功');
		                	return;
		                }else{
		                    // 没有成功的，重设
		                    state = 'done';
		                    location.reload();
		                }
		                break;
		        }
		        updateStatus();
		    }
			//进度条
		    uploader.onUploadProgress = function( file, percentage ) {
		        var $li = $('#'+file.id),
		            $percent = $li.find('.progress span');
		        $percent.css('width', percentage * 100 + '%' );
		        percentages[ file.id ][ 1 ] = percentage;
		        updateTotalProgress();
		    };
			//当文件被加入队列以后触发。
		    uploader.onFileQueued = function( file ) {
		    	$info.show();
		        fileCount++;
		        fileSize += file.size;
		        if (fileCount === 1) {
		            $placeHolder.addClass('element-invisible' );
		            $statusBar.show();
		        }
		        addFile( file );
		        setState('ready');
		        updateTotalProgress();
		    };
			//当文件被移除队列后触发。
		    uploader.onFileDequeued = function( file ) {
		        fileCount--;
		        fileSize -= file.size;
		        if (!fileCount){
		        	$('#filePicker2').addClass('element-invisible');
		        	$statusBar.hide();
		            setState('pedding');
		        }
		        removeFile( file );
		        updateTotalProgress();
		    };
			//所有事件处理
		    uploader.on('all',function(type) {
		        var stats;
		        switch( type ) {
		            case 'uploadFinished':
		                setState( 'confirm' );
		                break;
		            case 'startUpload':
		                setState( 'uploading' );
		                break;
		            case 'stopUpload':
		                setState( 'paused' );
		                break;
		        }
		    });
		
		    uploader.onError = function( code ) {
		    	if(code=='Q_EXCEED_NUM_LIMIT'){
		    		alert('添加的文件数量超出');return false;
		    	}else if(code=='Q_EXCEED_SIZE_LIMIT'){
		    		alert('添加的文件总大小超出');return false;
		    	}else if(code=='Q_TYPE_DENIED'){
		    		alert('上传文件类型不正确');return false;
		    	}
		    };
		
		    $upload.on('click', function() {
		        if ( $(this).hasClass( 'disabled' ) ) {
		            return false;
		        }
		        if ( state === 'ready' ) {
		            uploader.upload();
		        } else if ( state === 'paused' ) {
		            uploader.upload();
		        } else if ( state === 'uploading' ) {
		            uploader.stop();
		        }
		    });
		
		    $info.on( 'click', '.retry', function() {
		        uploader.retry();
		    } );
		
		    $info.on( 'click', '.ignore', function() {
		    	uploader.reset();
		    	//location.reload();
		        //alert( 'todo' );
		    } );
		
		    //$upload.addClass( 'state-' + state );
		    
		    updateTotalProgress();
		    //上传完成时触发
		    uploader.on('uploadSuccess', function(file,response){
			    $('#'+file.id).attr('url',response.url);
			    $('#'+file.id).addClass('active');//上传完成加选中状态
			    $('#'+file.id).find('p.progress').hide();
			   
			   //多选
			    $(".filelist > li").on("click",function(){
					if($(this).hasClass('active')){
						$(this).removeClass('active');
					}else{
						$(this).addClass("active");
					}
				});
				
				//上传完成时插入文件
				$('#intoUpdateFile').click(function(){
					var images=[];
					var selectNum = $(".filelist > li.active").length;
					if(selectNum==0){
						//alert('请选择图片...！');
						$('#histips').text('请选择图片！').css('color','red');
					}else if(selectNum==1){
						images[0]=$(".filelist > li.active").attr('url');
					}else if(selectNum>parame.filenum){
						//alert('您只能选择 '+parame.filenum+' 项！');
						$('#histips').text('您只能选择 '+parame.filenum+' 项！').css('color','red');
					}else{
						//多文件时，插入容器
						$(".filelist > li.active").each(function(i,e){
							images[i]=$(this).attr('url');
						});
					}
					callback(images);
				});
			});
		});
		
		uploadfile.historyinto(parame,function(img){
			if(img){
				if($.isFunction(callback)){
					callback(img);
				}
			}
		});
	}

	//历史上传插入
	uploadfile.historyinto=function(parame,callback){
		$('#intoHistoryFile').click(function(){
			var images=[];
			var selectNum = $(".history_list > li.active").length;
			if(selectNum==0){
				$('#histip').text('您还没有选择项！');
			}else if(selectNum==1){
				images[0]=$(".history_list > li.active").attr('url');
			}else if(selectNum>parame.filenum){
				$('#histip').text('您只能选择 '+parame.filenum+' 项！');
			}else{
				//多文件时
				$(".history_list > li.active").each(function(i,e){
					images[i]=$(this).attr('url');
				});
			}
			callback(images);
		});
	}
	
	if (typeof define === "function" && define.amd) {
		define(['webuploader'], function(){
			return uploadfile;
		});
	} else {
		window.uploadfile = uploadfile;
	}
})(window);
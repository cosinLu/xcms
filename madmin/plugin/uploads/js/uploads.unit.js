/**
 * Created by Admin on 2017/9/5.
 */
define(['jquery'], function ($) {
    var itemTemplate = '<div class="uploadifive-queue-item uploads-queue-item uploads-queue-item-hook">\
                            <span class="uploads-move uploads-move-hook"></span>\
                            <div class="uploads-progress progress">\
                                <div class="progress-bar progress-bar-success"></div>\
                            </div>\
                            <div class="uploads-control ploads-control-hook">\
                                <a href="javascript:;" class="uploads-preview uploads-preview-hook"><i class="fa fa-search-plus"></i></a>\
                                <a href="javascript:;" class="uploads-destory uploads-destory-hook"><i class="fa fa-trash"></i></a>\
                            </div>\
                            <img class="uploads-thumb uploads-thumb-hook" src="" alt="">\
                            <input type="hidden" class="uploads-input-hook">\
                        </div>';
    return {
        //缩略图
        thumbnail: function (ext) {
            if (!ext.length) return '';
            var src;
            var thumbnail = {
                'zip': 'plugin/uploads/images/zip.png',
                'ai': 'plugin/uploads/images/ai.png',
                'apk': 'plugin/uploads/images/apk.png',
                'excel': 'plugin/uploads/images/excel.png',
                'flash': 'plugin/uploads/images/flash.png',
                'image': 'plugin/uploads/images/image.png',
                'music': 'plugin/uploads/images/music.png',
                'pdf': 'plugin/uploads/images/pdf.png',
                'ppt': 'plugin/uploads/images/ppt.png',
                'psd': 'plugin/uploads/images/psd.png',
                'video': 'plugin/uploads/images/video.png',
                'word': 'plugin/uploads/images/word.png',
                'unknown': 'plugin/uploads/images/unknown.png'
            };
            ext           = ext.replace('\.', '').toLowerCase();
            if ($.inArray(ext, ['7z', 'rar', 'zip']) > -1) {
                src = thumbnail.zip;
            } else if ($.inArray(ext, ['ai']) > -1) {
                src = thumbnail.ai;
            } else if ($.inArray(ext, ['apk']) > -1) {
                src = thumbnail.apk;
            } else if ($.inArray(ext, ['xls', 'xlsx']) > -1) {
                src = thumbnail.excel;
            } else if ($.inArray(ext, ['flv', 'flash']) > -1) {
                src = thumbnail.flash;
            } else if ($.inArray(ext, ['jpg', 'png', 'gif', 'bmp']) > -1) {
                src = thumbnail.image;
            } else if ($.inArray(ext, ['mp3', 'ogg', 'wav']) > -1) {
                src = thumbnail.music;
            } else if ($.inArray(ext, ['pdf']) > -1) {
                src = thumbnail.pdf;
            } else if ($.inArray(ext, ['ppt', 'pptx']) > -1) {
                src = thumbnail.ppt;
            } else if ($.inArray(ext, ['psd']) > -1) {
                src = thumbnail.psd;
            } else if ($.inArray(ext, ['mp4', 'rm', '3gp', 'avi', 'wma', 'rmvb']) > -1) {
                src = thumbnail.video;
            } else if ($.inArray(ext, ['doc', 'docx']) > -1) {
                src = thumbnail.word;
            } else {
                src = thumbnail.unknown;
            }
            return src;
        },

        //初始化数据
        initData: function () {
            require(['jqthumb'], function (jqthumb) {
                $('.uploads-queue-hook').each(function () {
                    var _this     = $(this);
                    var list      = _this.data('list');
                    var inputName = _this.attr('id');
                    if (list != undefined) {
                        $.each(list, function (i, data) {
                            var item = $(itemTemplate).clone();
                            var src  = parseInt(data.upl_image) ? data.upl_path : this.thumbnail(data.upl_ext);
                            //状态
                            item.addClass('complete');
                            //预览图片信息
                            item.find('.uploads-preview-hook')
                                .attr('data-content', '{"src":"' + src + '","name":"' + data.upl_name + '","size":"' + data.upl_size + '","width":"' + data.upl_width + '","height":"' + data.upl_height + '","image":"' + data.upl_image + '"}');
                            //input
                            item.find('.uploads-input-hook')
                                .attr('name', inputName + '[]').val(data.upl_id);
                            //缩略图
                            item.find('.uploads-thumb-hook').attr('src', src)
                                .attr('alt', data.upl_name)
                                .jqthumb({width: '69', height: '69'});
                            //图片标题
                            item.attr('title', data.upl_name);
                            //添加到容器
                            _this.append(item);
                        })
                    }
                })
            })
        },

        //拖拽
        dragSort: function () {
            require(['sortable'], function (Sortable) {
                $('.uploads-queue-hook').each(function () {
                    var id       = $(this).attr('id');
                    var el       = document.getElementById(id);
                    var sortable = new Sortable(el, {
                        group: id,
                        handle: '.uploads-move-hook',
                        ghostClass: 'uploads-ghost',
                        chosenClass: "uploads-chosen",
                        draggable: '.uploads-queue-item-hook',
                    });
                });
            })
        },

        //上传预览
        uploadsPreview: function () {
            require(['layer'], function () {
                $(document).on('click', '.uploads-preview-hook', function () {
                    var content = $(this).data('content');
                    var str     = '';
                    str += '<div class="uploads-preview">';
                    str += '<div class="uploads-preview-img">';
                    str += '<img src="' + content.src + '">';
                    str += '</div>';
                    str += '<div class="uploads-preview-content">';
                    str += '<div>名称：' + content.name + '</div>';
                    if (content.image) {
                        str += '<div>尺寸：' + content.width + ' * ' + content.height + '</div>';
                    }
                    str += '<div>大小：' + content.size + ' KB</div>';
                    str += '</div>';
                    str += '</div>';
                    parent.layer.open({
                        title: false,
                        area: '500px',
                        btn: false,
                        offset: '100px',
                        content: str
                    });
                });
            })
        },

        //删除上传
        uploadsDel: function () {
            $(document).on('click', '.uploads-destory-hook', function () {
                $(this).closest('.uploads-queue-item-hook').remove();
            });
        },

        //实例化上传组件
        initUploads: function () {
            require(['uploadifive'], function (uploadifive) {
                $('.uploads-btn-hook').uploadifive({
                    'auto': true,
                    'buttonClass': 'uploads-btn',
                    'buttonText': '+',
                    'formData': {
                        'timestamp': new Date().getTime(),
                        'token': Math.random()
                    },
                    'itemTemplate': itemTemplate,
                    'fileSizeLimit': '20MB',
                    'fileTypeSuffix': 'jpg,png,gif',
                    'uploadScript': 'index.php/uploads/do_upload',
                    'onUploadComplete': function (file, data) {
                        var data      = $.parseJSON(data);
                        var item      = file.queueItem;
                        var src       = data.upl_image ? data.upl_path : this.thumbnail(data.upl_ext);
                        var inputName = data.upl_input;

                        //预览图片信息
                        item.find('.uploads-preview-hook')
                            .attr('data-content', '{"src":"' + src + '","name":"' + data.upl_name + '","size":"' + data.upl_size + '","width":"' + data.upl_width + '","height":"' + data.upl_height + '","image":"' + data.upl_image + '"}');
                        //input
                        item.find('.uploads-input-hook')
                            .attr('name', inputName + '[]').val(data.upl_id);
                        //缩略图
                        item.find('.uploads-thumb-hook').attr('src', src)
                            .attr('alt', data.upl_name)
                            .jqthumb({width: '69', height: '69'});
                        //图片标题
                        item.attr('title', data.upl_name);
                    },
                    'onQueueComplete': function (uploads) {
                        //this.dragSort();
                    },
                    'onError': function (errorType) {
                    }
                });
            })
        },

        //云端
        initCloudUplods: function () {
            require(['layer'], function (layer) {
                $('.uploads-online-hook').on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    //容器
                    var queueID = $(this).data('id');
                    //是否多文件上传
                    var multi   = $(this).data('multi');
                    var url     = $(this).data('url') + '?&queueid=' + queueID + '&multi=' + multi;
                    layer.open({
                        type: 2,
                        title: '从云端选择',
                        area: ['800px', '560px'],
                        content: url
                    })
                });
            })
        },
    }
});

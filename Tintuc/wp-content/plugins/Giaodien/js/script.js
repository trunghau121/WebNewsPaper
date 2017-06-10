jQuery(document).ready(function ($) {
    $(window).load(function () {
        if ($('#select')) {
            var id = $('#select').val();
            $.ajax({
                type: 'POST',
                url: laytinAjax.ajaxurl,
                data: {action: "laytin_get_link_content", id: id},
                success: function (data) {
                    $('#infor').html(data);
                }
            });
        }
    });
    $('#btplugin').click(function () {
        var enable = $('#settting-enable').is(':checked');
        var time = $('#time').val();
    
        $.ajax({
            type: 'POST',
            url: laytinAjax.ajaxurl,
            data: {action: "laytin_update_plugin_setting", time: time, enable: enable},
            success: function (data) {
                if (data == 1) {
                    alert('Update SettingPlugin thành công');
                } else {
                    alert('SettingPlugin không thay đổi');
                }
            }
        });
    });
    $('#select').change(function () {
        var id = $('#select').val();
        $.ajax({
            type: 'POST',
            url: laytinAjax.ajaxurl,
            data: {action: "laytin_get_link_content", id: id},
            success: function (data) {
                $('#infor').html(data);
            }
        });
    });
    $('#btdulieu').click(function () {
        var id = $('#select').val();
        var host = $('#host').val();
        var bieuthuc = $('#bieuthuc').val();
        var link = $('#link').val();
        var title = $('#title').val();
        var img = $('#img').val();
        var content = $('#content').val();
        var loaibo = $('#loaibo').val();
        $.ajax({
            type: 'POST',
            url: laytinAjax.ajaxurl,
            data: {action: "laytin_data_view", id: id, host: host, bieuthuc: bieuthuc, link: link, title: title, img: img, content: content, loaibo: loaibo},
            success: function (data) {
                $('#view').html(data);
            }
        });
    });
    $('#btcrawler').click(function () {

        var id = $('#select').val();
        var iddanhmuc = $('#danhmuc').val();
        var host = $('#host').val();
        var bieuthuc = $('#bieuthuc').val();
        var link = $('#link').val();
        var title = $('#title').val();
        var img = $('#img').val();
        var content = $('#content').val();
        var loaibo = $('#loaibo').val();
        $.ajax({
            type: 'POST',
            url: laytinAjax.ajaxurl,
            data: {action: "save_crawler", id: id, iddanhmuc: iddanhmuc, host: host, bieuthuc: bieuthuc, link: link, title: title, img: img, content: content, loaibo: loaibo},
            success: function (data) {
                    if (data != 00) {
                    alert('Update Crawler thành công ');
                } else {
                    alert('Update Crawler không thành công ');
                }
            }
        });
    });
});

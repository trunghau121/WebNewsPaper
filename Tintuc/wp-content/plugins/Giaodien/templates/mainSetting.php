<html>
    <head>
        <meta charset="UTF-8">
    </head>
    <body>
        <div class="wrap">
            <h2>Cài đặt cho plugin</h2>
            <form method="post" action="">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Thời gian lấy tin:</th>
                        <td>
                            <select name="time" id="time">
                                <?php
                                global $wpdb;
                                $time = get_option('laytin_time');
                                $enable = get_option('laytin_enable');
                                echo '  <option value="pre_minute" ' . (($time == "pre_minute") ? "selected" : "") . '>Một phút</option>
                                        <option value="thirty_minute" ' . (($time == "thirty_minute") ? "selected" : "") . '>30 phút</option>
                                        <option value="hourly" ' . (($time == "hourly") ? "selected" : "") . '>Một giờ</option>
                                        <option value="twicedaily" ' . (($time == "twicedaily") ? "selected" : "") . '>Hai lần/Ngày</option>
                                        <option value="daily" ' . (($time == "daily") ? "selected" : "") . '>Hằng Ngày</option>';
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Hoạt động:</th>
                        <td>
                            <?php
                            echo '<input name="settting-enable" id="settting-enable" type="radio" value="1" ' . (($enable == "true") ? "checked" : "") . '/>Bật<br />
                                        <input name="settting-enable" type="radio" value="0" ' . (($enable == "false") ? "checked" : "") . '/>Tắt<br />';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input name="btplugin" id="btplugin" type="button" value="Lưu cài đặt"/>
                        </td>
                    </tr>
                </table><!--kết thúc table plugin-->
                <h2>Cài đặt cho Crawler</h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Link lấy tin:</th>
                        <td>
                            <select name="link" id="select">
                                <option value="1">Link 1</option>
                                <option value="2" >Link 2</option>
                                <option value="3">Link 3</option>
                                <option value="4">Link 4</option>

                            </select></td>
                    </tr>
                    <tr>
                    </tr>
                </table><!--kết thúc table crawler-->
                <div id="infor"></div>
                <div style="text-align: center;">
                    <td><input name="btcrawler" id="btcrawler" value="Lưu cài đặt" type="button"/></td>
                    <td></td>
                    <td><input name="btview" id="btdulieu" value="Xem dữ liệu" type="button"/></td>
                </div>
                <div id="view"></div>
            </form>
        </div><!--kết thúc div main -->
    </body>
</html>
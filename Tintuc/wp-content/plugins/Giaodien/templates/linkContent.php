<meta content="text/html" charset="UTF-8" http-equiv="content-type"/>
<?php
$category = $wpdb->get_results("SELECT * FROM wp_terms WHERE term_id > '1' and term_id < '6' ");
$iddanhmuc = $link->iddanhmuc;


echo ' <table class="form-table">
        <tr>
         <th scope="row"> Danh Mục :</th>
        <td> <select name="danhmuc" id="danhmuc">';

foreach ($category as $val) {
    echo '<option value="' . ($val->term_id) . ' " ' . ((($val->term_id) == $iddanhmuc) ? "selected" : "") . '>' . ($val->name) . '</option>';
}



echo '</select></td>
        <tr>
         <th scope="row"> Web URL:</th>
        <td>
            <textarea rows="4" cols="50" name="host" id="host">' . ($link->host) . '</textarea>
        </td>
        </tr>
        <tr>
         <th scope="row"> Biểu thức:</th>
        <td>
           <textarea rows="4" cols="50" name="bieuthuc" id="bieuthuc">' . htmlentities($link->bieuthuc) . '</textarea>
        </td>
        </tr>
         <tr>
         <th scope="row"> Vị trí URL:</th>
        <td>
            <input name="link" type="text" id="link" value="' . htmlentities($link->url) . '"/>
        </td>
        </tr>
        <tr>
        <th scope="row">Vị trí tiêu đề:</th>
        <td>
            <input name="title" type="text" id="title" value="' . htmlentities($link->tieude) . '"/>
        </td>
        </tr>
        <tr>
         <th scope="row">Vị trí hình ảnh:</th>
        <td>
            <input name="img" id="img" type="text" value="' . htmlentities($link->hinhanh) . '"/>
        </td>
        </tr>
        <tr>
         <th scope="row"> Biểu thức nội dung:</th>
        <td>
           <textarea rows="4" cols="50" name="content" id="content">' . htmlentities($link->noidung) . '</textarea>
        </td>
        </tr>
        <tr>
        <th scope="row"> Loại bỏ thẻ:</th>
        <td>
           <textarea rows="4" cols="50" name="remove" id="loaibo">' . htmlentities($link->loaibo) . '</textarea>
        </td>
        </tr>
        </table>';
?>
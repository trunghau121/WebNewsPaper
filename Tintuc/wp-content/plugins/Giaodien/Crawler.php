<?php

class crawling {

    public function get_data($arr) {
        $subject = crawling::getURL($arr['host']);
        $pattern = $arr['bieuthuc'];
        preg_match_all($pattern, $subject, $matches);
        $new = array();
        foreach ($matches[1] as $key => $info) {
            $tmp['link'] = $matches[(int) $arr['link']][$key];
            $tmp['title'] = $matches[(int) $arr['title']][$key];
            $tmp['img'] = $matches[(int) $arr['img']][$key];
            $new[$key] = $tmp;
        }

        return $new;
    }

    function getURL($URL) {
        $useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function get_content($url, $pre, $remove) {

        $subject = crawling::getURL($url);
        $pattern = $pre;
        preg_match_all($pattern, $subject, $matches);
        if (!empty($remove)) {
            $matches[0][0]=  crawling::RemoveTag($remove,$matches[0][0]);
        }
        return $matches[0][0];
    }
    public function RemoveTag($preg_remove, $data) {
        $subject = $preg_remove;
        $preg_remov = preg_replace('#@.*@#imsU', "@(.*)@", $subject);
        $pattern = '#' . $preg_remov . '#imsU';
        preg_match_all($pattern, $subject, $matches);
        $i = 0;
        foreach ($matches as $s) {
            if ($i != 0) {
                $data=preg_replace('#'.$s[0].'#imsU',"",$data);
            }
            $i++;
        }
        return $data;
    }

}

?>
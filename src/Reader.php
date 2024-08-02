<?php

namespace CReadLines;

class Reader
{
    public $line_base_dir;
    public $line_code_path;
    public $output;
    public $allow_ext = ['php'];
    public function calc_line($path)
    {
        $line_base_dir = $this->line_base_dir;
        $line_base_dir = realpath($line_base_dir);
        $line_base_dir = str_replace("\\", "/", $line_base_dir);
        $list = $this->get_deep_dir($path);
        $php_files = [];
        foreach($list as $v) {
            if(strpos($v, '.php') !== false) {
                $php_files[] = realpath($v);
            }
        }
        $new_dir = [];
        $all = scandir($path);
        foreach($all as $v) {
            $f = $path.'/'.$v;
            if(!in_array($v, ['.','..','.git']) && is_dir($f)) {
                $new_dir[] = realpath($f);
            }
        }
        $out = [];
        $sum = 0;
        $body = '';
        foreach($php_files as $v) {
            $v = realpath($v);
            $new_v = str_replace("\\", "/", $v);
            $body_header = "\n".substr($new_v, strlen($line_base_dir))."\n";
            $body .= $body_header;
            $body .= file_get_contents($v);
            $c = $this->get_lines($v);
            foreach($new_dir as $vv) {
                if(strpos($v, $vv) !== false && in_array($this->get_ext($v), $this->allow_ext)) {
                    $a = substr($vv, strrpos($vv, DIRECTORY_SEPARATOR) + 1);
                    if(!isset($out[$a])) {
                        $out[$a] = $c;
                    } else {
                        $out[$a] += $c;
                    }
                    $sum += $c;
                }
            }
        }
        return ['sum' => $sum,'out' => $out,'body' => $body];
    }
    public function get_deep_dir($path)
    {
        $arr = array();
        $arr[] = $path;
        if (is_file($path)) {
        } else {
            if (is_dir($path)) {
                $data = scandir($path);
                if (!empty($data)) {
                    foreach ($data as $value) {
                        if ($value != '.' && $value != '..') {
                            $sub_path = $path . "/" . $value;
                            $temp = $this->get_deep_dir($sub_path);
                            $arr  = array_merge($temp, $arr);
                        }
                    }
                }
            }
        }
        return $arr;
    }
    /**
    * 获取文件行数，不包空行
    */
    public function get_lines($file, $length = 40960)
    {
        $i = 1;
        $handle = @fopen($file, "r");
        if ($handle) {
            while (!feof($handle)) {
                $body = fgets($handle, $length);
                if($body && trim($body)) {
                    $i++;
                }
            }
            fclose($handle);
        }
        return $i;
    }
    public function get_ext($name)
    {
        if (strpos($name, '?') !== false) {
            $name = substr($name, 0, strpos($name, '?'));
        }
        $name =  substr($name, strrpos($name, '.'));
        return strtolower(substr($name, 1));
    }
    public function run()
    {
        $sum = 0;
        $out = [];
        $body = '';
        foreach($this->line_code_path as $path) {
            $res = $this->calc_line($path);
            if($res && $res['sum'] > 0) {
                $sum = $sum + ($res['sum'] ?: 0);
                $out = array_merge($out, $res['out'] ?: []);
                $body .= $res['body']." ";
            }
        }
        echo "计算行数：\n";
        foreach($out as $k => $v) {
            echo $k." >> ".$v."\n";
        }
        echo "共：".$sum." 行\n";
        file_put_contents($this->output, trim($body));
    }
}

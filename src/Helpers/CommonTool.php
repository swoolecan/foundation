<?php

declare(strict_types=1);

namespace Swoolecan\Foundation\Helpers;

use Carbon\Carbon;

class CommonTool
{
    use TraitToolSpell;

    public static function generateUniqueString($length = 6)
    {
        // 字符集，可任意添加你需要的字符
        //$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
        $chars = 'abcdefghijmnpqrtxyABCDEFGHJLMNPRTXY23456789';
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            // 这里提供两种字符获取方式
            // 第一种是使用substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组$chars 的任意元素
            // $string .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            $string .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return strtolower(base_convert(time() - 1420070400, 10, 36)) . $string;
    }

    /**
     * 数字转换为中文
     * @param string|integer|float $num 目标数字
     * @return string
     */
    public static function numberToChinese($number)
    {
        if (!is_numeric($number)) {
            return $number; // '含有非数字非小数点字符！';
        }
        $char = ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九'];
        $unit = ['', '十', '百', '千', '', '万', '亿', '兆'];

        $retval = '点';
        // 小数部分
        if (strpos($number, '.')) {
            list($num, $dec) = explode('.', $number);
            $dec = strval(round($dec, 2));
            for ($i = 0, $c = strlen($dec); $i < $c; $i++) {
                $retval .= $char[$dec[$i]];
            }
        }

        // 整数部分
        $str = strrev(intval($number));
        for ($i = 0, $c = strlen($str); $i < $c; $i++) {
            $out[$i] = $char[$str[$i]];
            $out[$i] .= $str[$i] != '0' ? $unit[$i % 4] : '';
            if ($i > 1 and $str[$i] + $str[$i - 1] == 0) {
                $out[$i] = '';
            }
            if ($i % 4 == 0) {
                $out[$i] .= $unit[4 + floor($i / 4)];
            }
        }
        $retval = join('', array_reverse($out)) . $retval;
        return $retval;
    }

    public static function createTree(& $infos, $parent = '', $indexBy = 'key', $parentField = 'parent_code', $keyField = 'code')
    {
        $datas = [];
        foreach ($infos as $key => $info) {
            if ($info[$parentField] != $parent) {
                continue;
            }
            unset($infos[$key]);

            $info['subInfos'] = self::createTree($infos, $info[$keyField], $indexBy, $parentField, $keyField);
            if ($indexBy == 'num') {
                $datas[] = $info;
            } else {
                $datas[$info[$keyField]] = $info;
            }
        }
        return $datas;
    }

    //获取中文
    public static function getChineseString($string)
    {
        $rule = '/[\x{4e00}-\x{9fff}]+/u';
        preg_match_all($rule, $string, $wordName);
        return $wordName[0];
    }

    //拆分中文字符成数组
    public static function splitData($data, $count)
    {
        if (is_array($data)) {
            $data = implode($data);
        }
        $length = mb_strlen($data);
        for ($i = 0; $i < $length; $i += $count) {
            $arr[] = mb_substr($data, $i, $count);
        }

        return $arr;
    }

    public static function getPathFiles($path)
    {
        $files = scandir($path);
        foreach ($files as $key => $file) {
            if (in_array($file, ['.', '..'])) {
                unset($files[$key]);
            }
        }
        return $files;
    }

    public static function getCarbonObj($string = null)
    {
        if (is_null($string)) {
            return Carbon::now();
        }
        return Carbon::parse($string);
    }
}

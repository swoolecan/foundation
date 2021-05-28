<?php
declare(strict_types = 1);

namespace Swoolecan\Foundation\Helpers;

class CommonTool
{
    public static function generateUniqueString($length = 6)
    {
        // 字符集，可任意添加你需要的字符
        //$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
        $chars = 'abcdefghijmnpqrtxyABCDEFGHJLMNPRTXY23456789';
        $string = '';
        for ( $i = 0; $i < $length; $i++) {
            // 这里提供两种字符获取方式
            // 第一种是使用substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组$chars 的任意元素
            // $string .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            $string .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return strtolower(base_convert(time() - 1420070400, 10, 36)) . $string;
    }
}

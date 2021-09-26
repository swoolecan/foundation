<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Helpers;

use Overtrue\Pinyin\Pinyin;

trait TraitToolSpell
{
    public static function getSpellInstance()
    {
        $spell = new Pinyin(); // 小内存型: 将字典分片载入内存
        //$spell = new Pinyin('\\Overtrue\\Pinyin\\MemoryFileDictLoader'); // 内存型: 将所有字典预先载入内存
        //$spell = new Pinyin('\\Overtrue\\Pinyin\\GeneratorFileDictLoader'); // I/O型: 不载入内存，将字典使用文件流打开逐行遍历并运用php5.5生成器(yield)特性分配单行内存
        return $spell;
    }

    public static function getSpell($string, $option = null)
    {
        //PINYIN_TONE UNICODE 式音调：měi hǎo
        //PINYIN_ASCII_TONE   带数字式音调： mei3 hao3
        //PINYIN_NO_TONE  无音调：mei hao
        //PINYIN_KEEP_NUMBER  保留数字
        //PINYIN_KEEP_ENGLISH 保留英文
        //PINYIN_KEEP_PUNCTUATION 保留标点
        //PINYIN_UMLAUT_V 使用 v 代替 yu, 例如：吕 lyu 将会转为 lv
        
        return self::getSpellInstance()->convert($string, $option);
    }

    public static function getSpellStr($string, $linkStr = '-')
    {
        // 生成用于链接的拼音字符串
        return self::getSpellInstance()->permalink($string, $linkStr);
    }

    public static function getFirstSpell($string, $linkStr = '')
    {
        // 获取首字符字符串
        // PINYIN_KEEP_NUMBER); // nh2018
        // PINYIN_KEEP_ENGLISH); // HNY2018
        return self::getSpellInstance()->abbr($string, $linkStr);
    }

    public static function getLongSpell($string, $option)
    {
        // 翻译整段文字为拼音
        // 将会保留中文字符：，。 ！ ？ ： “ ” ‘ ’ 并替换为对应的英文符号。
        
        return self::getSpellInstance()->sentence($string, $option);
    }

    public static function getNameSpell($name, $option)
    {
        //翻译姓名 姓名的姓的读音有些与普通字不一样，比如 ‘单’ 常见的音为 dan，而作为姓的时候读 shan。
        return self::getSpellInstance()->name($name, $option);
    }
}

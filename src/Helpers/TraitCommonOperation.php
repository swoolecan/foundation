<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Helpers;

use Overtrue\Pinyin\Pinyin;

trait TraitCommonOperation
{
    public function 

PINYIN_TONE UNICODE 式音调：měi hǎo
PINYIN_ASCII_TONE   带数字式音调： mei3 hao3
PINYIN_NO_TONE  无音调：mei hao
PINYIN_KEEP_NUMBER  保留数字
PINYIN_KEEP_ENGLISH 保留英文
PINYIN_KEEP_PUNCTUATION 保留标点
PINYIN_UMLAUT_V 使用 v 代替 yu, 例如：吕 lyu 将会转为 lv
拼音数组
use Overtrue\Pinyin\Pinyin;

// 小内存型
$pinyin = new Pinyin(); // 默认
// 内存型
// $pinyin = new Pinyin('\\Overtrue\\Pinyin\\MemoryFileDictLoader');
// I/O型
// $pinyin = new Pinyin('\\Overtrue\\Pinyin\\GeneratorFileDictLoader');

$pinyin->convert('带着希望去旅行，比到达终点更美好');
// ["dai", "zhe", "xi", "wang", "qu", "lyu", "xing", "bi", "dao", "da", "zhong", "dian", "geng", "mei", "hao"]

$pinyin->convert('带着希望去旅行，比到达终点更美好', PINYIN_TONE);
// ["dài","zhe","xī","wàng","qù","lǚ","xíng","bǐ","dào","dá","zhōng","diǎn","gèng","měi","hǎo"]

$pinyin->convert('带着希望去旅行，比到达终点更美好', PINYIN_ASCII_TONE);
//["dai4","zhe","xi1","wang4","qu4","lyu3","xing2","bi3","dao4","da2","zhong1","dian3","geng4","mei3","hao3"]
小内存型: 将字典分片载入内存
内存型: 将所有字典预先载入内存
I/O型: 不载入内存，将字典使用文件流打开逐行遍历并运用php5.5生成器(yield)特性分配单行内存
生成用于链接的拼音字符串
$pinyin->permalink('带着希望去旅行'); // dai-zhe-xi-wang-qu-lyu-xing
$pinyin->permalink('带着希望去旅行', '.'); // dai.zhe.xi.wang.qu.lyu.xing
获取首字符字符串
$pinyin->abbr('带着希望去旅行'); // dzxwqlx
$pinyin->abbr('带着希望去旅行', '-'); // d-z-x-w-q-l-x

$pinyin->abbr('你好2018！', PINYIN_KEEP_NUMBER); // nh2018
$pinyin->abbr('Happy New Year! 2018！', PINYIN_KEEP_ENGLISH); // HNY2018
翻译整段文字为拼音
将会保留中文字符：，。 ！ ？ ： “ ” ‘ ’ 并替换为对应的英文符号。

$pinyin->sentence('带着希望去旅行，比到达终点更美好！');
// dai zhe xi wang qu lyu xing, bi dao da zhong dian geng mei hao!

$pinyin->sentence('带着希望去旅行，比到达终点更美好！', PINYIN_TONE);
// dài zhe xī wàng qù lǚ xíng, bǐ dào dá zhōng diǎn gèng měi hǎo!
翻译姓名
姓名的姓的读音有些与普通字不一样，比如 ‘单’ 常见的音为 dan，而作为姓的时候读 shan。

$pinyin->name('单某某'); // ['shan', 'mou', 'mou']
$pinyin->name('单某某', PINYIN_TONE); // ["shàn","mǒu","mǒu"]
}

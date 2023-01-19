<?php

namespace Swoolecan\Foundation\Services;

trait TraitRedisService
{
    protected $redis;

    protected function pointRepository()
    {
        return false;
    }

    public function set($key, $value)
    {
        $value = is_array($value) ? json_encode($value) : $value;
        $this->redis->set($key, $value);
    }

    public function get($key, $returnArray = false)
    {
        $value = $this->redis->get($key);
        return $returnArray ? json_decode($value, true) : $value;
    }

    public function mset($mkv)
    {
        $mkv = [
            "user:001"=>'First user',
            "user:002"=>"Second user",
            "user:003"=>"Third user"
        ];
        $this->redis->mset($mkv); // 存储多个key对应的value
    }

    public function mget($mkv)
    {
        $retval = $this->redis->mget(array_keys($mkv)); // 获取多个key对应的value
    }

    public function setex($key, $duration, $value)
    {
        $this->redis->setex($key , $duration, $value); // setex 存放带存储时效的记录 存储key为library,值为phpredis的记录，有效时长为10秒
    }

    public function exists($key)
    {
        return $this->redis->exists($key); //exists 检测是否存在  存在返回1 否则返回0
    }

    public function del($key)
    {
        return $this->redis->del($key);
    }

    public function lpush($key, $datas)
    {
        if (is_array($datas)) {
            return $this->redis->lpush($key, ...$datas);
        }
        return $this->redis->lpush($key, $datas);
    }

    public function llen($key)
    {
        return $this->redis->llen($key); //返回 3
    }

    // lrange 返回队列中一个区间的元素
    public function lrange($key, $start, $end, $isArray = false)
    {
        $datas = $this->redis->lrange($key, $start, $end); //返回数组包含第0个至第1个，共2个元素
        if (empty($isArray)){
            return $datas;
        }
        foreach ($datas as $key => & $data) {
            $data = unserialize($data);
        }
        return $datas;
        //$redis->lrange('foolist' , 0 , -1);//返回第0个至倒数第一个，相当于返回所有元素
    }

    public function hget($key, $filed) {
        return $this->redis->hget($key, $filed);
    }

    public function hset($key, $filed, $value) {
        $this->redis->hset($key, $filed, $value);
    }

    public function hDel($key, $filed) {
        $this->redis->hDel($key, $filed);
    }

    public function hIncrBy($key, $filed, $num = 1) {
        $this->redis->hIncrBy($key, $filed, $num);
    }

    public function hExists($key, $filed) {
        return $this->redis->hExists($key, $filed);
    }

    public function expireat($key, $time = null) {
        if(is_null($time)) {
            $time = strtotime(date("Y-m-d 23:59:59"),time());
        }
        $this->redis->expireat($key, $time);
    }
}

/**
add操作，不会覆盖已有值
$redis->setnx("foo" , 12); //返回true, 添加成功  存在不做任何操作  否则创建
$redis->setnx('foo' , 34); //返回false ，添加失败，因为存在键名foo的记录

$redis->getset('foo' , 56);// getset 是 set的变种，结果返回替换前的值//返回12；如果之前不存在记录，则返回null

//incrby/incr/decrby/decr对值得递增和递减
$redis->incr('foo'); //返回57 ，递增 阶梯为1
$redis->incrby('foo' , 2); //返回59 递增 阶梯为2

$redis->exists("foo"); //exists 检测是否存在  存在返回1 否则返回0
$redis->type('foo');//type 类型检测，字符串返回 string ,列表返回 list , set表返回 set/zset ，hash表返回 hash

$redis->get('str');//返回test
$redis->append('str' , "_123"); // append 连接到已存在字符串

// setrange 部分替换操作，并返回字符串长度
$redis->setrange('str' , 0 , 'abc'); //返回3，第2个参数为0等同于set操作
$redis->setrange('str' , 2 , 'cd'); //返回4，表示从第2个字符后替换，这时‘str’ 为 ‘abcd’

//substr 部分获取操作
$redis->substr('str' , 0 , 2);//返回abc 表示从第0个起，取到第2个字符串
$redis->strlen('str'); // 返回4 此时‘str’ 为‘abcd’

$redis->setbit('library' , 31 ,1); // 表示在第31位存入1 //setbit 位存储
$redis->getbit('library' , 31); //返回1 //getbit 位获取

//keys 模糊查找功能，支持 * 号 以及 ？号 （匹配一个字符）
$redis->set('foo1',123);
$redis->set('foo2' , 456);
$redis->keys('foo*'); //返回foo1和foo2的array
$redis->keys('f?0?'); // 同上

// randomkey  随机返回一个key
$redis->randomkey(); //可能是返回‘foo1’ 或者是foo2 及其它任何已存在的key

//rename/renamenx 方式对key进行改名，所不同的是renamenx不允许改成已存在的key
$redis->rename('str','str2'); // 把原先命名为 str 的key改成了 str2

//expire 设置key-value的时效性
$redis->expire('foo' , 10);//设置有效期为10秒
$redis->ttl('foo'); // 返回剩余有效期值10秒 ttl  获取剩余有效期
$redispersist("fool");//取消有效期，变为永久存储 persist  重新设置为永久存储

//dbsize 返回redis当前数据库的记录总数
$redis->dbsize();

// 队列操作
// rpush/rpushx有序列表操作，从队列后插入元素；
// lpush/lpushx和rpush/rpushx的区别是插入到队列的头部，同上,‘x’含义是只对已存在的key进行操作
$redis->rpush('foolist' , 'bar1'); //返回列表长度1
$redis->rpush('foolist' , 'bar0'); // 返回列表长度2
$redis->rpushx('foolist' , 'bar2'); // 返回3 ， rpushx只对已存在的队列做添加，否则返回0

//lindex 返回指定顺序位置的list元素
$redis->lindex('foolist' , 1); //返回bar1

// lset 修改队列中指定位置的value
$redis->lset('foolist' , 1 ,'123'); // 修改位置1的元素，返回true

// lrem 删除队列中左起指定数量的字符
$redis->lrem("foolist" , 1 , '_'); //删除队列中左起（右起使用-1）1个字符‘_’（若有）

// lpop/rpop 类似栈结构地弹出（并删除）最左或最右的一个元素

$redis->lpop('foolist');//左侧返回
$redis->rpop('foolist'); // 右侧返回

// ltrim 队列修改，保留左边起若干元素，其余删除
$redis->ltrim('foolist' , 0 , 1);   //  保留左边起第0个至第1个元素

//rpoplpush 从一个队列中pop元素并push到另一个队列
$redis->rpush('list1' , 'ab0');
$redis->rpush('list1','ab1');
$redis->rpush('list2' , 'ab2');
$redis->rpush('list2' , "ab3");
$redis->rpoplpush('list1' , "list2");
$redis->rpoplpush('list2' , 'list2');

//linsert在队列的中间指定元素前或后插入元素
$redis->linsert('list2' , 'before' , 'ab1' , '123');//表示在元素 ‘ab1’ 之前插入‘123’
$redis->linser('list2' , 'after' , 'ab1' , "456");//表示在元素 ‘ab1’ 之后插入

//blpop/brpop 阻塞并等待一个队列不为空时，在pop出最左或最右的一个元素（这个功能在php以外可以说非常好用）
$redis->blpop('list3' , 10) ; //如果list3 为空则一直等待，知道不为空时将第一个元素弹出，10秒后超时

//set集合操作 sadd增加set集合元素，返回true，重复返回false
$redis->sadd('set1' , 'ab');
$redis->sadd('set1' , 'cd');
$redis->sadd('set1' , 'ef');
$redis->smembers("set1");  // 查看集合元素

// srem 移除指定元素
$redis->srem('set1' , 'cd');//删除‘cd’ 元素

//spop弹出首元素
$redis->spop("set1");//返回‘ab’

// smove移动当前set集合的指定元素到另一个set集合
$redis->sadd("set2",'123');
$redis->smove('set1','set2','ab');//移动set1中的ab到set2 ,返回true or false;此时 set1 集合不存在 ab 这个值

// scard 返回当前set表元素个数
$redis->scard('set2');//返回2

// sismember判断元素是否属于当前set集合
$redis->sismember('set2','123'); //返回true or false

// smembers返回当前set集合的所有元素
$redis->smember('set2'); //返回array(123,ab)

// sinter/sunion/sdiff 返回两个表中的交集/并集/补集
$redis->sadd('set1' , 'ab');
$redis->sinter('set2' , 'set1');//返回array('ab');
sinterstore/sunionstore/sdiffstore 将两个表交集/并集/补集元素copy到第三个表中
$redis->set('foo' , 0);
$redis->sinterstore('foo' , 'set1');//等同于将set1 的内容copy到foo中，并将foo转为set表
$redis->sinterstore('foo' , array('set1' , 'set2'));//将set1和set2中相同的元素copy到foo表中，覆盖foo原有内容

// srandmember 返回表中一个随即元素
$redis->srandmember('set1');

// 有序set表操作
// zadd增加元素，并设置序号，成功返回true，重复返回false
$redis->zadd("zset1" , 1 , 'ab');
$redis->zadd('zset1' , 2 , 'cd');
$redis->zadd('zset1' , 3 , 'ef');
// zincrBy对指定元素索引值的增减，改变元素排序次序
$redis->zincryBy('zset1' , 10 , 'ab');  //返回11
// zrem 移除指定元素
$redis->zrem('zset1' , 'ef');//返回true  or  false
// zrange按位置次序返回表中指定区间的元素
$redis->zrange("zset1" , 0 , 1);//返回位置0 和 1 之间（两个）的元素
$redis->zrange('zset1' , 1 , -1);//返回位置0和倒数第一个元素之间的元素（相当于所有元素）
// zrevrange同上，返回表中指定区间的元素，按次序倒排
$redis->zrevrange('zset1' , 0 ,-1);//元素顺序和zrange相反
// zrangeByscore/zrevrangeByscore 按顺序/降序返回表中指定索引区间的元素
$redis->zadd('zset1' , 3 , 'ef');
$redis->zadd('zset1' , 5 , 'gh');
$redis->zrangeByscore('zset1' , 2, 9);//返回索引值2-9之间的元素array('ef' , 'gh');
$redis->zrangeByscore('zset1' , 2 ,9 ,array('withscores'=>true , 'limit'=>array(1,2)));//返回索引值2-9之间的元素，withscores=>true表示包含索引值；limit=>array(1,2),表示偏移1，返回2条，结果为array(array('ef',3),array('gh',5))

//zcount统计一个索引区间的元素个数
$redis->zcount('zset1' , 3 , 5);//返回2
$redis->zcount('zset1' , '(3' , 5 ) );//’（3‘ 表示索引的值在3-5之间但不含3，同理也可以使用’（5‘ 表示上限为5但不含5
//zcard 统计元素个数
$redis->zcard('zset1');//返回4

//zremrangeByscore删除一个索引区间的元素
$redis->zremrangeByscore('zset1' , 0 ,  2);//删除索引在0-2之间的元素（ab ,  cd），返回删除元素个数2
//zrank/zrevrank返回元素所在表顺序/降序的位置（不是索引）
$redis->zrank('zset1' , 'ef');//返回0，因为它是一个元素；zrevrank则返回1（最后一个）

//zremrangeByrank删除表中指定位置区间的元素
$redis->zremrangeByrank('zset1' , 0  ,  10);//删除位置为0-10的元素，返回删除的元素个数2

//hash表操作
$redis->hset('hash1' , 'key1' , 'v1');//将key为key1,value为v1的元素存入hash1表
$redis->hset("hash1" , 'key2' , 'v2');
$redis->hget('hash1' , 'key1');//取出表hash1中的key   key  key1的值，返回v1

//hexists返回hash表中的指定key是否存在
$redis->hexists("hash1" , 'key1');//true 或 false

//hdel 删除hash表中指定key的元素
$redis->hdel('hash' , 'key2');//true  or  false

//hlen 返回hash表元素个数
$redis->hlen('hash1'); // 返回1

//hsetnx增加一个元素，但不能重复
$redis->hsetnx('hash1' , 'key1' , 'v2');

$redis->hsetnx('hash1' , 'key2' , 'v2');

//hmset/hmget存取多个元素到hash表
$redis->hmset( 'hash1' , array('key3'=>'v3' , 'key4'=>'v4' ) );
$redis->hmget( 'hash1' , array('key3' , 'key4') );//返回响应的值 array('v3' , 'v4');

//hincryby 对指定key进行累加
$redis->hincryBy('hash1' , 'key5' ,  3); //不存在，则存储并返回3 ；存在，即返回原有值 +3
$redis->hincryBy("hash1" , 'key5' , 10);//返回13

//hkeys返回hash表中的所有key
$redis->hkeys('hash1'); // 返回array('key1' , 'key2' , 'key3' , 'key4' , 'key5');

//hvals 返回hash表中的所有value
$redis->hvals('hash1'); // 返回array('v1' , 'v2' , 'v3' , 'v4' , 13);

//hgetall返回整个hash表元素
$redis->hgetall('hash1');//返回hash1所有表元素

//排序操作 sort排序
$redis->rpush('tab' , 3);
$redis->rpush('tab' , 2);
$redis->rpush('tab' , '17');
$redis->sort('tab');//返回array(2,3,17);
$redis->sort('tab' , array('sort'=>'desc'));//降序排序，返回array(17 , 3, 2)
$redis->sort('tab' , array('limit'=>array(1,2)));//返回顺序位置中1的元素2个（这里的2是指个数，而不是位置），返回array(3,17)
$redis->sort('tab' , array('limit'=>array('alpha'=>true)));//按首字符排序返回array(17 , 2 , 3 )，因为17的首字符是 1 所以排首位置
$redis->sort('tab' , array('limit'=>array('store'=>'ordered')));//表示永久性排序，返回元素个数
$redis->sort('tab' , array("limit"=>array('get'=>'pre_*')));//使用了通配符 * 过滤元素，表示只返回以pre开头的元素

//Redis 管理操作
$redis->info(); //info显示服务当状态信息
$redis->select(4)；//指定数据库的下标 //select指定要操作的数据库
$redis->flushdb(); // flushdb清空当前库

//move移动当库的元素到其它数据库
$redis->set('tomove' , 'bar');
$redis->move('tomove' , 4);

//slaveof 配置从服务器
$redis->slaveof('127.0.0.1' , 80);//配置127.0.0.1端口80的服务器为从服务器
$redis->slaveof();//消除从服务器
//同步保存服务器数据到磁盘
$redis->save();

//异步保存服务器数据到磁盘
$redis->bgsave()

//返回最后更新磁盘的时间
$redis->lastsave();*/

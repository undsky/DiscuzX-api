<?php

/**
 * Redis Sentinel 类
 *
 * @author 耐小心<i@naixiaoxin.com>
 * @copyright 2003-2017 耐小心
 */
class CRedisSentinelCache extends CCache
{
    /**
     * 哨兵地址
     * @var array
     */
    public $sentinelServer = array(
        array(
            'server' => '127.0.0.1',
            'port'   => '26379'
        )
    );
    /**
     * 哨兵名字
     * @var string
     */
    public $sentinelName = 'my-master';
    /**
     * 数据库ID
     * @var int
     */
    public $database = 0;
    /**
     * Redis密码
     * @var string
     */
    public $password = '';

    /**
     * 从库Redis
     * @var Redis
     */
    private $readConnect = null;
    /**
     * 主库Redis
     * @var Redis
     */
    private $writeConnect = null;
    private $init = false;

    //连接哨兵获得Redis
    public function connect()
    {
        $servers = array();
        foreach ($this->sentinelServer as $item)
        {
            $servers[] = new SentinelClient($item['server'], $item['port']);
        }
        $sentinel           = new Sentinel($this->sentinelName, $servers, DISCUZ_ROOT . 'data/log/redis.log');
        $this->readConnect  = $sentinel->getReadConn();
        $this->writeConnect = $sentinel->getWriteConn();
        if (empty($this->writeConnect) || empty($this->readConnect))
        {
            throw new Exception('redis异常,请联系管理员');
        }
        if (!empty($this->password))
        {
            $this->writeConnect->auth($this->password);
            $this->readConnect->auth($this->password);
        }

        if (!empty($this->database))
        {
            $this->readConnect->select($this->database);
            $this->writeConnect->select($this->database);
        }
        $this->init = true;
    }

    protected function getValue($key)
    {
        if(!$this->init)
            $this->connect();
        return $this->readConnect->get($key);
    }

    protected function getValues($keys)
    {
        if(!$this->init)
            $this->connect();
        $response = $this->readConnect->mget($keys);
        $result   = array();
        $i        = 0;
        foreach ($keys as $key)
            $result[$key] = $response[$i++];

        return $result;
    }

    protected function setValue($key, $value, $expire)
    {
        if(!$this->init)
            $this->connect();
        if ($expire == 0)
            return $this->writeConnect->set($key, $value);

        return $this->writeConnect->setex($key, $expire, $value);
    }


    protected function addValue($key, $value, $expire)
    {
        if(!$this->init)
            $this->connect();
        if ($expire == 0)
            return (bool)$this->writeConnect->SETNX($key, $value);

        if ($this->writeConnect->SETNX($key, $value))
        {
            $this->writeConnect->expire($key, $expire);

            return true;
        }
        else
            return false;
    }

    protected function deleteValue($key)
    {
        if(!$this->init)
            $this->connect();
        return (bool)$this->writeConnect->del($key);
    }

    protected function flushValues()
    {
        if(!$this->init)
            $this->connect();
        return (bool)$this->writeConnect->flushDB();
    }


}
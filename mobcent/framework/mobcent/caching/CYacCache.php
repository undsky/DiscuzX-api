<?php

/**
 *
 *
 * @author 耐小心<i@naixiaoxin.com>
 * @copyright 2003-2017 耐小心
 */
class CYacCache extends CCache
{


    protected $yac = null;


    protected function getValue($key)
    {
        if ($this->yac == null)
            $this->yac = new Yac();

        return $this->yac->get($key);
    }

    protected function getValues($keys)
    {
        if ($this->yac == null)
            $this->yac = new Yac();

        return $this->yac->get($keys);
    }

    protected function setValue($key, $value, $expire)
    {
        if ($this->yac == null)
            $this->yac = new Yac();

        return $this->yac->set($key, $value, $expire);
    }

    protected function addValue($key, $value, $expire)
    {

        return $this->setValue($key, $value, $expire);
    }

    protected function deleteValue($key)
    {
        if ($this->yac == null)
            $this->yac = new Yac();

        return $this->yac->delete($key);
    }

    protected function flushValues()
    {
        if ($this->yac == null)
            $this->yac = new Yac();

        return $this->yac->flush();
    }


}
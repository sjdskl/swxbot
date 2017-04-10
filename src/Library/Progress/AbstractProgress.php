<?php

/**
 * Progress created at 2017-4-6 14:58:03
 * The encoding is UTF-8
 * 
 * @author skl@tzg.cn
 */

namespace swxbot\Library\Progress;

abstract class AbstractProgress
{
    protected $_progress = array();
    
    public function getProgresses()
    {
        return $this->_progress;
    }
    
    public abstract function run(callable $callback);
    
    public abstract function runBackground();
    
    public abstract function wait();
    
    
}
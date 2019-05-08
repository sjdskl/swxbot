<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/2/9
 * Time: 下午4:27
 */

class tttt
{

    public function __construct()
    {
        echo "construct\n";
    }

    public function __destruct()
    {
        echo "destruct\n";
    }


}


$a = new tttt();
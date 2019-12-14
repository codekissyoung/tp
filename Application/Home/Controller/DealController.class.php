<?php
namespace Home\Controller;

use Think\Controller;
use Think\Log;

// http://www.pc.com/index.php/Home/Deal/getDeal
class DealController extends Controller
{
    public function _before_getDeal()
    {
        echo "exec before....";
    }

    // http://www.pc.com/Home/Deal/getDeal/name/cky/age/24
    public function getDeal( $name, $age )
    {
        // dump( C() );
        dump( get_defined_constants(true)['user'] );

        Log::record('测试日志信息，这是警告级别','WARN');
        Log::write('测试日志信息，这是警告级别，并且实时写入','WARN');
        trace($name,"错误",'ERR');
        $this -> show("Hi $name , you are $age old!", "utf-8");
        $this -> display();
    }

    public function _after_getDeal()
    {
        echo ".....exec after";
    }

    public function _empty()
    {
        echo "empty page!! 404 not found!";
    }

}
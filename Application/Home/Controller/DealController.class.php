<?php
namespace Home\Controller;

use Think\Controller;

class DealController extends Controller
{
    public function index()
    {
        echo "Deal";
    }

    // http://www.pc.com/Home/Deal/getDeal/name/cky/age/24
    public function getDeal($name, $age)
    {
        // echo "hello Deal";
        dump(C());
        dump(get_defined_constants(true)['user']);

        trace($name, "错误", 'ERR');
        $this->show("Hi $name , you are $age old!", "utf-8");
        $this->display();
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

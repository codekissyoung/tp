<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function index()
    {
        dump("hello");
    }

    public function deal()
    {
        echo "helloDeal";
    }
}

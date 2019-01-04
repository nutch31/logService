<?php
namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Model\Test;

class TestController extends BaseController
{
    public function index()
    {
        return 'Index';
    }
    
    public function get()
    {
        $test = Test::all();

        return $test;
    }

    public function post()
    {
        $user = new Test;
        $user->name = 'John';
        $user->save();

        return $user;
    }
}
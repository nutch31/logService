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

    public function post()
    {
        $test = new Test;
        $test->name = 'Test';
        $test->save();

        return $test;
    }
}
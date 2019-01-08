<?php

namespace App\Model;

//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class LogInLogOut extends Eloquent 
{
    protected $connection = 'mongodb';
    protected $collection = 'loginlogouts';
    protected $primarykey = '_id';
    //
}
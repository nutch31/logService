<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Test extends Eloquent 
{
    protected $connection = 'mongodb';
    protected $collection = 'tests';
    protected $primarykey = 'id';
    //
}
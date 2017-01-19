<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PosGroup extends Model
{
    protected $table = 'pos_group';
    public $timestamps = false;
    const STATUS_ON  = 1;
    const STATUS_OFF = 0;

}
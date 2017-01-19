<?php
/**
 * 流水号表
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    protected $table   = 'sequence';
    public $timestamps = false;
    const CARD_NAME    = 'card_seq';
}
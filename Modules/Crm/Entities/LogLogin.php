<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class LogLogin
 * @package App\Models
 */
class LogLogin extends Model
{
    /**
    * @SWG\Definition()
    */
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'CG_LOG_LOGIN';
    protected $fillable = ['LOG_ID'];
    public $timestamps = false;
    protected $primaryKey = 'LOG_ID';
}

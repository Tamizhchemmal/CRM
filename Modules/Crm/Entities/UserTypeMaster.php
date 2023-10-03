<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class usertypemaster
 * @package App\Models
 */
class UserTypeMaster extends Model
{
    /**
    * @SWG\Definition()
    */
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'CG_USER_TYPE';
    protected $fillable = ['UT_ID'];
    public $timestamps = false;
    protected $primaryKey = 'UT_ID';
}

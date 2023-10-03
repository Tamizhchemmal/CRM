<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class usersInfo
 * @package App\Models
 */
class UserInfo extends Model
{
    /**
    * @SWG\Definition()
    */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'CG_USER_INFO';
    protected $fillable = ['UI_ID','UI_USER_ID'];
    public $timestamps = false;
    protected $primaryKey = 'UI_ID';

    public function createdby()
    {
        return $this->hasOne('Modules\Crm\Entities\User', 'USER_ID', 'UI_CREATED_BY');
    }

    public function updatedby()
    {
        return $this->hasOne('Modules\Crm\Entities\User', 'USER_ID', 'UI_MODIFIED_BY');
    }

    public function user()
    {
        return $this->hasOne('Modules\Crm\Entities\User', 'USER_ID', 'UI_USER_ID');
    }

    public function course()
    {
        return $this->hasOne('Modules\Crm\Entities\Course', 'COURSE_ID', 'UI_COURSE');
    }

}

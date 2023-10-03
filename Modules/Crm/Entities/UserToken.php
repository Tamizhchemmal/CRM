<?php 

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class usertoken
 * @package App\Models
 */
class UserToken extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'CG_USER_TOKEN';
    protected $primaryKey = 'UT_ID';
    public $timestamps = false;
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function user()
    {
        return $this->hasOne('Modules\Crm\Entities\User', 'USER_ID', 'UT_USER_ID');
    }
    
}

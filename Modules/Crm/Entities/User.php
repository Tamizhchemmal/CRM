<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class users
 * @package App\Models
 */
class User extends Model
{
    /**
    * @SWG\Definition()
    */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'CG_USERS';
    protected $fillable = ['USER_ID'];
    public $timestamps = false;
    protected $primaryKey = 'USER_ID';
    protected $hidden = array('USER_PASSWORD');

    public function usertype()
    {
        return $this->hasOne('Modules\Crm\Entities\UserTypeMaster', 'UT_ID', 'USER_TYPE');
    }

    public function userinfo()
    {
        return $this->hasOne('Modules\Crm\Entities\UserInfo', 'UI_USER_ID', 'USER_ID');
    }

    public function usertoken() {
        return $this->hasOne('Modules\Crm\Entities\UserToken', 'UT_USER_ID', 'USER_ID');
    }

    public function userpaymentreceipt() {
        return $this->hasOne('Modules\Crm\Entities\UserPaymentReceipt', 'UPR_USER_ID', 'USER_ID');
    }

    public function trainerStudents() {
        return $this->hasOne('Modules\Crm\Entities\Students', 'STUDENT_TRAINER_ID', 'USER_ID');
    }

    public function referralStudents() {
        return $this->hasOne('Modules\Crm\Entities\Students', 'STUDENT_REFERRAL_ID', 'USER_ID');
    }


   
}

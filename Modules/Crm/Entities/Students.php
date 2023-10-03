<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class students
 * @package App\Models
 */
class Students extends Model
{
    /**
    * @SWG\Definition()
    */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'CG_STUDENTS';
    protected $fillable = ['STUDENT_ID'];
    public $timestamps = false;
    protected $primaryKey = 'STUDENT_ID';

    public function course()
    {
        return $this->hasOne('Modules\Crm\Entities\Course', 'COURSE_ID', 'STUDENT_COURSE_ID')->where('COURSE_STATUS', '=', 1);
    }

    public function referral()
    {
        return $this->hasOne('Modules\Crm\Entities\Users', 'USER_ID', 'STUDENT_REFERRAL_ID');
    }

    public function referralinfo()
    {
        return $this->hasOne('Modules\Crm\Entities\UserInfo', 'UI_USER_ID', 'STUDENT_REFERRAL_ID');
    }

    public function batch()
    {
        return $this->hasOne('Modules\Crm\Entities\Batch', 'BATCH_ID', 'STUDENT_BATCH_ID');
    }

    public function trainers()
    {
        return $this->hasOne('Modules\Crm\Entities\Users', 'USER_ID', 'STUDENT_TRAINER_ID');
    }


   
}

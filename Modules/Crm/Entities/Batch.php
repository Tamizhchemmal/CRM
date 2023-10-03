<?php 

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class batch
 * @package App\Models
 */
class Batch extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'CG_BATCH'; 
    protected $primaryKey = 'BATCH_ID';
    protected $fillable = ['BATCH_ID'];
    public $timestamps = false;
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

    public function trainers()
    {
        return $this->hasOne('Modules\Crm\Entities\Users', 'USER_ID', 'BATCH_TRAINER_ID');
    }

    public function trainerinfo()
    {
        return $this->hasOne('Modules\Crm\Entities\UserInfo', 'UI_USER_ID', 'BATCH_TRAINER_ID');
    }

    public function batchStudents()
    {
        return $this->hasOne('Modules\Crm\Entities\Students', 'STUDENT_BATCH_ID', 'BATCH_ID');
    }
    
}

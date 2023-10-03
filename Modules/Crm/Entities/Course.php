<?php 

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class course
 * @package App\Models
 */
class Course extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'CG_COURSE';
    protected $primaryKey = 'COURSE_ID';
    public $timestamps = false;
    protected $fillable = ['COURSE_ID'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

   
    
}

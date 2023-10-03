<?php 

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class PaymentMethod
 * @package App\Models
 */
class PaymentMethod extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'CG_PAYMENT_METHOD';
    protected $primaryKey = 'PAYM_ID';
    public $timestamps = false;
    protected $fillable = ['PAYM_ID'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

   
    
}

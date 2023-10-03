<?php

namespace Modules\Crm\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Carbon\Carbon;

use Illuminate\Http\Response;
use Modules\Crm\Entities\User;
use Modules\Crm\Entities\UserInfo;
use Modules\Crm\Entities\UserToken;
use Modules\Crm\Entities\UserTypeMaster;
use Modules\Crm\Entities\LogLogin;
use Modules\Crm\Entities\Students;
use Modules\Crm\Entities\Batch;

class BatchController extends Controller
{

    public function store(Request $request)
    {
        try {
            
            $current_date = getCarbonObject();
            $formated_current_date = $current_date->toDateTimeString();

                

                $CreateBatch = Batch::firstOrNew(array('BATCH_ID' => $request->input('batchId')));
                $CreateBatch->BATCH_CODE = $request->input('batchCode');
                $CreateBatch->BATCH_TRAINER_ID = $request->input('trainerId');
                $CreateBatch->BATCH_SELECTED_TIME = $request->input('batchSelectedTime');
                $CreateBatch->BATCH_STARTED_DATE = $request->input('startDate');
                $CreateBatch->BATCH_END_DATE = $request->input('endDate');
                if($CreateBatch->BATCH_CREATED_DATE){
                    $CreateBatch->BATCH_MODIFIED_BY = $request->input('createdby');
                    $CreateBatch->BATCH_MODIFIED_DATE = $current_date;
                }
                else{
                    $CreateBatch->BATCH_CREATED_BY = $request->input('createdby');
                    $CreateBatch->BATCH_CREATED_DATE = $current_date;
                }
                
                $CreateBatch->save();

                if($request->input('studentId')){
                    $updateStudentsBatch = Students::whereIn('STUDENT_ID', $request->input('studentId'))->update(['STUDENT_BATCH_ID' => $CreateBatch->BATCH_ID]);
                }

               
                return $CreateBatch;


           
        } catch (Exception $e) {
            Log::error('Controller - BatchController, function - store, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }


    public function getBatchList(Request $request)
    {
        try {


                $batchDetails = Batch::with(['trainerinfo' => function ($query) {
                    $query->select('UI_ID', 'UI_USER_ID', 'UI_FIRST_NAME', 'UI_LAST_NAME');
                },'batchStudents'])->where('BATCH_STATUS', '=', 1)->select('BATCH_ID', 'BATCH_CODE', 'BATCH_TRAINER_ID', 'BATCH_SELECTED_TIME', 'BATCH_STARTED_DATE', 'BATCH_END_DATE')->get();
               
                return $batchDetails;
           
        } catch (Exception $e) {
            Log::error('Controller - StudentController, function - getBatchList, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }

    public function getBatchById($id)
    {
        try {


                $batchDetails = Batch::with(['trainerinfo' => function ($query) {
                    $query->select('UI_ID', 'UI_USER_ID', 'UI_FIRST_NAME', 'UI_LAST_NAME');
                },'batchStudents'])->where('BATCH_ID', $id)->where('BATCH_STATUS', '=', 1)->select('BATCH_ID', 'BATCH_CODE', 'BATCH_TRAINER_ID', 'BATCH_SELECTED_TIME', 'BATCH_STARTED_DATE', 'BATCH_END_DATE')->get();
               
                return $batchDetails;
           
        } catch (Exception $e) {
            Log::error('Controller - StudentController, function - getBatchById, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }
}

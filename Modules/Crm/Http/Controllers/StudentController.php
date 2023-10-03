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


class StudentController extends Controller
{

    public function store(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'createdby' => 'required|numeric'
            ]);
            $current_date = getCarbonObject();
            $formated_current_date = $current_date->toDateTimeString();
            $createdby = $request->input('createdby');
            $studentId = $request->input('studentid');

                $studentDetails = Students::where('STUDENT_EMAIL', '=', $request->input('email'))->select('STUDENT_ID', 'STUDENT_EMAIL')->get();
                if (sizeof($studentDetails)) {
                    return 'Email Id already exists';
                }

                $CreateStudent = new Students();
                $CreateStudent->STUDENT_EMAIL = $request->input('email');
                $CreateStudent->STUDENT_NAME = $request->input('name');
                $CreateStudent->STUDENT_PHONE = $request->input('primaryphone');
                $CreateStudent->STUDENT_PASSED_YEAR = $request->input('passedoutyear');
                $CreateStudent->STUDENT_STARTED_DATE = $request->input('startDate');
                $CreateStudent->STUDENT_END_DATE = $request->input('endDate');
                $CreateStudent->STUDENT_TOTAL_FEES = $request->input('totalFees');
                $CreateStudent->STUDENT_FEES_PAID = $request->input('paidFees');
                $CreateStudent->STUDENT_PENDING_FEES = $request->input('totalFees') - $request->input('paidFees');
                $CreateStudent->STUDENT_COLLEGE = $request->input('college');
                $CreateStudent->STUDENT_DEGREE = $request->input('degree');
                $CreateStudent->STUDENT_PAYMENT_MODE = $request->input('paymentMode');
                $CreateStudent->STUDENT_PAYMENT_DATE = $request->input('paymentDate');
                $CreateStudent->STUDENT_REFERRAL_ID = $request->input('referralId');
                $CreateStudent->STUDENT_BATCH_ID = $request->input('batchId');
                $CreateStudent->STUDENT_TRAINER_ID = $request->input('trainerId');
                $CreateStudent->STUDENT_COURSE_ID = $request->input('courseId');
                $CreateStudent->STUDENT_CREATED_BY = $request->input('createdby');
                $CreateStudent->STUDENT_CREATED_DATE = $current_date ;
                $CreateStudent->save();
               
                return $CreateStudent;


           
        } catch (Exception $e) {
            Log::error('Controller - StudentController, function - store, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }


    public function update(Request $request)
    {
        try {

            // $request->validate([
            //     'studentid' => 'required|numeric|exists:CG_STUDENTS,STUDENT_ID',
            //     'email' => 'required|email',
            //     'modifiedby' => 'required|numeric'
            // ]);


                $studentId = $request->input('studentid');
                $loginuserid = _getUserDetailsByToken($request);
                if ($loginuserid) {
                    $loginUser = User::where('USER_ID', '=', $loginuserid)->select('USER_ID', 'USER_TYPE', 'USER_UUID')->get();

                    $current_date = getCarbonObject();                   

                    
                    $checkEmailExists = Students::where('STUDENT_EMAIL', '=', $request->input('email'))->where('STUDENT_ID', '!=', $studentId)->where('STUDENT_STATUS', '!=', 2)->get();
                    if (!sizeof($checkEmailExists)) {
                        
                        $CreateStudent = Students::firstOrNew(array('STUDENT_ID' => $studentId));
                        $CreateStudent->STUDENT_EMAIL = $request->input('email');
                        $CreateStudent->STUDENT_NAME = $request->input('name');
                        $CreateStudent->STUDENT_PHONE = $request->input('primaryphone');
                        $CreateStudent->STUDENT_PASSED_YEAR = $request->input('passedoutyear');
                        $CreateStudent->STUDENT_STARTED_DATE = $request->input('startDate');
                        $CreateStudent->STUDENT_END_DATE = $request->input('endDate');
                        $CreateStudent->STUDENT_TOTAL_FEES = $request->input('totalFees');
                        $CreateStudent->STUDENT_FEES_PAID = $request->input('paidFees');
                        $CreateStudent->STUDENT_PENDING_FEES = $request->input('totalFees') - $request->input('paidFees');
                        $CreateStudent->STUDENT_COLLEGE = $request->input('college');
                        $CreateStudent->STUDENT_DEGREE = $request->input('degree');
                        $CreateStudent->STUDENT_PAYMENT_MODE = $request->input('paymentMode');
                        $CreateStudent->STUDENT_PAYMENT_DATE = $request->input('paymentDate');
                        $CreateStudent->STUDENT_REFERRAL_ID = $request->input('referralId');
                        $CreateStudent->STUDENT_BATCH_ID = $request->input('batchId');
                        $CreateStudent->STUDENT_TRAINER_ID = $request->input('trainerId');
                        $CreateStudent->STUDENT_COURSE_ID = $request->input('courseId');
                        $CreateStudent->STUDENT_MODIFIED_BY = $request->input('modifiedby');
                        $CreateStudent->STUDENT_MODIFIED_DATE = $current_date ;
                        $CreateStudent->save();


                     

                        return $CreateStudent;
                    } else {
                        return 'Email Id already exists';
                    }
                } else {
                    return response('Unauthorized token.', 401);
                }
           
        } catch (Exception $e) {
            Log::error('Controller - UserController, function - update, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found',
            ), 400);
        }
    }


    public function getStudentsList(Request $request)
    {
        try {


                $studentDetails = Students::with(['course' => function ($query) {
                    $query->select('COURSE_ID', 'COURSE_NAME', 'COURSE_TYPE', 'COURSE_STATUS');
                },'referralinfo' => function ($query) {
                    $query->select('UI_ID', 'UI_USER_ID', 'UI_FIRST_NAME', 'UI_LAST_NAME');
                }])->where('STUDENT_STATUS', '=', 1)->select('STUDENT_ID', 'STUDENT_EMAIL', 'STUDENT_NAME', 'STUDENT_PHONE', 'STUDENT_PASSED_YEAR', 'STUDENT_STARTED_DATE', 'STUDENT_END_DATE', 'STUDENT_TOTAL_FEES', 'STUDENT_FEES_PAID', 'STUDENT_PENDING_FEES', 'STUDENT_COLLEGE', 'STUDENT_DEGREE', 'STUDENT_PAYMENT_MODE', 'STUDENT_PAYMENT_DATE', 'STUDENT_REFERRAL_ID', 'STUDENT_BATCH_ID', 'STUDENT_TRAINER_ID', 'STUDENT_COURSE_ID', 'STUDENT_STATUS')->get();
               
                return $studentDetails;
           
        } catch (Exception $e) {
            Log::error('Controller - StudentController, function - getStudentsList, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }


    public function getStudentDataById($id)
    {
        try {


                $studentDetails = Students::with(['course' => function ($query) {
                    $query->select('COURSE_ID', 'COURSE_NAME', 'COURSE_TYPE', 'COURSE_STATUS');
                },'referralinfo' => function ($query) {
                    $query->select('UI_ID', 'UI_USER_ID', 'UI_FIRST_NAME', 'UI_LAST_NAME');
                }])->where('STUDENT_ID', $id)->where('STUDENT_STATUS', '=', 1)->select('STUDENT_ID', 'STUDENT_EMAIL', 'STUDENT_NAME', 'STUDENT_PHONE', 'STUDENT_PASSED_YEAR', 'STUDENT_STARTED_DATE', 'STUDENT_END_DATE', 'STUDENT_TOTAL_FEES', 'STUDENT_FEES_PAID', 'STUDENT_PENDING_FEES', 'STUDENT_COLLEGE', 'STUDENT_DEGREE', 'STUDENT_PAYMENT_MODE', 'STUDENT_PAYMENT_DATE', 'STUDENT_REFERRAL_ID', 'STUDENT_BATCH_ID', 'STUDENT_TRAINER_ID', 'STUDENT_COURSE_ID', 'STUDENT_STATUS')->get();
               
                return $studentDetails;
           
        } catch (Exception $e) {
            Log::error('Controller - StudentController, function - getStudentDataById, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }
}

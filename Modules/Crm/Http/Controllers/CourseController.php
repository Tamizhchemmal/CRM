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
use Modules\Crm\Entities\Course;

class CourseController extends Controller
{

    public function store(Request $request)
    {
        try {
            
            $current_date = getCarbonObject();
            $formated_current_date = $current_date->toDateTimeString();

                

                $CreateCourse= Course::firstOrNew(array('COURSE_ID' => $request->input('courseId')));
                $CreateCourse->COURSE_NAME = $request->input('courseName');
                $CreateCourse->COURSE_TYPE = $request->input('courseType');
                $CreateCourse->COURSE_CREATED_BY = $request->input('createdby');
                $CreateCourse->COURSE_CREATED_DATE = $current_date;
                $CreateCourse->save();
               
                return $CreateCourse;


           
        } catch (Exception $e) {
            Log::error('Controller - CourseController, function - store, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }


    public function getCourseList(Request $request)
    {
        try {


                $courseDetails = Course::where('COURSE_STATUS', '=', 1)->get();
               
                return $courseDetails;
           
        } catch (Exception $e) {
            Log::error('Controller - CourseController, function - getCourseList, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }

    public function getCourseById($id)
    {
        try {


                $courseDetails = Course::where('COURSE_ID', $id)->where('COURSE_STATUS', '=', 1)->get();
               
                return $courseDetails;
           
        } catch (Exception $e) {
            Log::error('Controller - CourseController, function - getCourseList, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }
}

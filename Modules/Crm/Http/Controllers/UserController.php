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
use Webpatser\Uuid\Uuid;
use Modules\Crm\Entities\PaymentMethod;


class UserController extends Controller
{

    public function store(Request $request)
    {
        try {
            $request->validate([
                'createdby' => 'required|numeric'
            ]);
            $current_date = getCarbonObject();
            $formated_current_date = $current_date->toDateTimeString();
            $createdby = $request->input('createdby');
            $userId = $request->input('userid');
        

            if (0 == $userId) {
                   
                $result = $this->userStore($request);
                if ('parameter missing' == $result) {
                    return response(array('errmessage' => 'parameters missing'));
                } else  if ($result == 'errmessage') {
                    return response(array('errmessage' => $result));
                } else  if ($result == 'Email Id already exists') {
                    return response(array('errmessage' => 'Email Id already exists'));
                } else {
                    $userId = $result['0']->USER_ID;
                }
            } else {
                $result = $this->userUpdate($request);
                if ('parameter missing' == $result) {
                    return response(array('errmessage' => 'parameters missing'));
                } else  if ($result == 'Email Id already exists') {
                    return response(array('errmessage' => 'Email Id already exists'));
                } else if ($result == 'errmessage') {
                    return response(array('errmessage' => $result));
                }
            }

            $CreateUserInfo = UserInfo::firstOrNew(array('UI_USER_ID' => $userId));
            $CreateUserInfo->UI_USER_ID = $userId;
            $CreateUserInfo->UI_FIRST_NAME = $request->input('firstname');
            $CreateUserInfo->UI_LAST_NAME = $request->input('lastname');
            $CreateUserInfo->UI_ADDRESS = $request->input('address');
            $CreateUserInfo->UI_CITY = $request->input('city');
            $CreateUserInfo->UI_STATE = $request->input('state');
            $CreateUserInfo->UI_ZIPCODE = $request->input('zipcode');
            $CreateUserInfo->UI_COUNTRY = $request->input('country');
            $CreateUserInfo->UI_COMPANY = $request->input('company');
            $CreateUserInfo->UI_PRIMARY_PHONE = $request->input('primaryphone');
            $CreateUserInfo->UI_PAYMENT_MODE= $request->input('paymentmode');
            $CreateUserInfo->UI_PAYMENT_ACC_DETAIL= $request->input('paymentdetails');
            $CreateUserInfo->UI_PAYMENT_IFSC= $request->input('ifsccode');
           
            if($CreateUserInfo->UI_ID && $CreateUserInfo->UI_ID != '') {
                $CreateUserInfo->UI_MODIFIED_BY = $createdby;
                $CreateUserInfo->UI_MODIFIED_DATE = $current_date;
            } else {
                $CreateUserInfo->UI_CREATED_BY = $createdby;
                $CreateUserInfo->UI_CREATED_DATE = $current_date;
               
            }
    
            $CreateUserInfo->UI_COURSE = $request->input('course');
          
            $CreateUserInfo->save();

          
        
          
            $user = UserInfo::with('createdby', 'updatedby', 'user')->where('UI_USER_ID', $userId)->get();

 
                return response($user);
           
        } catch (Exception $e) {
            Log::error('Controller - UserController, function - store, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }



    public function userStore(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'usertype' => 'required|numeric',
                'createdby' => 'required|numeric',
            ]);
        
            $userid = _getUserDetailsByToken($request);
            $origin = $request->header('Referer');
            $origin = substr($origin, 0, strlen($origin) - 1);

            $user = User::where('USER_ID', $userid)->first();

             //   if ($userid == 0 || $userid == '') {
                    $password = $request->input('password');
                // } else {
                //     $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
                //     $pass = array(); //remember to declare $pass as an array
                //     $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
                //     for ($i = 0; $i < 8; $i++) {
                //         $n = rand(0, $alphaLength);
                //         $pass[] = $alphabet[$n];
                //     }
                //     $password = md5(trim(implode($pass)));
                // }

                $current_date = getCarbonObject();
                $userDetails = User::where('USER_EMAIL', '=', $request->input('email'))->select('USER_TENANT_ID', 'USER_EMAIL')->get();
                if (sizeof($userDetails)) {
                    return 'Email Id already exists';
                }

                $CreateUser = new User();
                $CreateUser->USER_EMAIL = $request->input('email');
                $CreateUser->USER_PASSWORD = $password;
                $CreateUser->USER_TYPE = $request->input('usertype');
                $CreateUser->USER_CREATED_BY = $request->input('createdby');
                $CreateUser->USER_CREATED_DATE = $current_date;
                $CreateUser->USER_UUID = Uuid::generate();
                $CreateUser->save();
               


                $reUser = array();
                $reUser[0] = $CreateUser;
                $token = '';
                $token = $this->_getUserToken($CreateUser->USER_ID);
                $user1 = array();
                $reUser['api_token'] = $token;


                return $reUser;
            
        } catch (Exception $e) {
            Log::error('Controller - UserController, function - store, Err:' . $e->getMessage());
            return 'errmessage';
        }
    }


    public function userUpdate(Request $request)
    {
        try {

            $request->validate([
                'userid' => 'required|numeric|exists:CG_USERS,USER_ID',
                'email' => 'required|email',
                'usertype' => 'required|numeric',
            ]);


                $userId = $request->input('userid');
                $loginuserid = _getUserDetailsByToken($request);
                if ($loginuserid) {
                    $loginUser = User::where('USER_ID', '=', $loginuserid)->select('USER_ID', 'USER_TYPE', 'USER_UUID')->get();

                    $current_date = getCarbonObject();

                    if (!empty($request->input('password'))) {
                       // $pass = md5($password);
                        $UpdateUser = User::where('USER_ID', '=', $userId)
                            ->update(['USER_PASSWORD' => $request->input('password')]);
                    }

                    $newUserType = $request->input('usertype');
                   

                    
                    $checkEmailExists = User::where('USER_EMAIL', '=', $request->input('email'))->where('USER_ID', '!=', $userId)->where('USER_STATUS', '!=', 2)->get();
                    if (!sizeof($checkEmailExists)) {
                        

                        $UpdateUser = User::where('USER_ID', '=', $userId)->update(['USER_EMAIL' => $request->input('email'), 'USER_TYPE' => $newUserType, 'USER_MODIFIED_BY' => $request->input('modifiedby'), 'USER_MODIFIED_DATE' => $current_date]);
                        $getuser = User::where('USER_ID', '=', $userId)->get();


                     

                        return $getuser;
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

     /**
     * To generate dynamic token while logining in
     * @param $uid
     * @return generated token
     */
    private function _getUserToken($uid, $isRemember = 0)
    {
        $tok = 0;
        $rnd = rand(1000, 5000);
        // $tok = md5(trim(substr(base64_encode($rnd), 0, 8)));
        $tm1 = Carbon::now('UTC')->format('Y-m-d H:i:s'); //date("Y-m-d H:i:s");
        $randomBytes = random_bytes(16); // Generate 16 random bytes using a cryptographically secure random number generator
        $randomString = bin2hex($randomBytes); // Convert the random bytes to a hexadecimal representation
        $tok = md5(trim(base64_encode($uid . $tm1 . $randomString)));
        $tm2 = Carbon::now('UTC')->addHour(3)->format('Y-m-d H:i:s'); //date("Y-m-d H:i:s", strtotime('+3 hours'));

        $tokObj = new UserToken();
        $tokObj->UT_TOKEN = $tok;
        $tokObj->UT_USER_ID = $uid;
        $tokObj->UT_CREATED_DATE = $tm1;
        $tokObj->UT_DELETED_DATE = null;
        $tokObj->UT_EXPIRE_DATE = $tm2;
        $tokObj->UT_IS_REMEMBER = $isRemember;
        $tokObj->save();

        return $tok;
    }

    public function getActiveUserList(Request $request)
    {
        try {

            $users = $userLists = array();
                $userDetails = User::with(['userinfo' => function ($query) {
                    $query->select('UI_ID', 'UI_USER_ID', 'UI_FIRST_NAME', 'UI_LAST_NAME');
                }])->where('USER_STATUS', '=', 1)->get();
               
                foreach($userDetails as $user){

                    $users['id'] = $user->USER_ID;
                    $users['uuid'] = $user->USER_UUID;
                    $users['name'] = _getFullUserName($user->userinfo);
                    $users['email'] = $user->USER_EMAIL;
                    $users['role'] = _getUserRole($user->USER_TYPE);
                    $users['companyname'] = $user->userinfo ? $user->userinfo->UI_COMPANY : null;
                    $users['mobilenumber'] = $user->userinfo ? $user->userinfo->UI_PRIMARY_PHONE : null;
                    $users['paymentmode'] = $user->userinfo ? _paymentMode($user->userinfo->UI_PAYMENT_MODE) : null;
                    $users['paymentdetails'] = $user->userinfo ? $user->userinfo->UI_PAYMENT_ACC_DETAIL : null;
                    $users['ifsccode'] = $user->userinfo ? $user->userinfo->UI_PAYMENT_IFSC : null;
                    array_push($userLists,$users);
                }
                

                return $userLists;
           
        }catch (Exception $e) {
            Log::error('Controller - UserController, function - getActiveUserList, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }


    public function getUsersById($userId)
    {
        try {

            $users = $userLists = array();
                $userDetails = User::with(['userinfo' => function ($query) {
                    $query->select('UI_ID', 'UI_USER_ID', 'UI_FIRST_NAME', 'UI_LAST_NAME');
                }])->where('USER_ID', $userId)->where('USER_STATUS', '=', 1)->get();
               
                foreach($userDetails as $user){

                    $users['id'] = $user->USER_ID;
                    $users['uuid'] = $user->USER_UUID;
                    $users['name'] = _getFullUserName($user->userinfo);
                    $users['email'] = $user->USER_EMAIL;
                    $users['role'] = _getUserRole($user->USER_TYPE);
                    $users['companyname'] = $user->userinfo ? $user->userinfo->UI_COMPANY : null;
                    $users['mobilenumber'] = $user->userinfo ? $user->userinfo->UI_PRIMARY_PHONE : null;
                    $users['paymentmode'] = $user->userinfo ? _paymentMode($user->userinfo->UI_PAYMENT_MODE) : null;
                    $users['paymentdetails'] = $user->userinfo ? $user->userinfo->UI_PAYMENT_ACC_DETAIL : null;
                    $users['ifsccode'] = $user->userinfo ? $user->userinfo->UI_PAYMENT_IFSC : null;
                    array_push($userLists,$users);
                }
                

                return $userLists;
           
        }catch (Exception $e) {
            Log::error('Controller - UserController, function - getActiveUserList, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }



    public function getTrainersList(Request $request)
    {
        try {

            $trainers = $trainersList = array();

                $trainerDetails = User::with(['userinfo' => function ($query) {
                    $query->select('UI_ID', 'UI_USER_ID', 'UI_FIRST_NAME', 'UI_LAST_NAME');
                },
                'userinfo.course' => function ($query) {
                    $query->select('COURSE_ID', 'COURSE_NAME', 'COURSE_TYPE', 'COURSE_STATUS');
                }, 'userpaymentreceipt', 'trainerStudents'])->where('USER_STATUS', '=', 1)->where('USER_TYPE', '=', 1)->get();
               
                foreach($trainerDetails as $user){

                    $trainers['id'] = $user->USER_ID;
                    $trainers['uuid'] = $user->USER_UUID;
                    $trainers['name'] = _getFullUserName($user->userinfo);
                    $trainers['email'] = $user->USER_EMAIL;
                    $trainers['role'] = _getUserRole($user->USER_TYPE);
                    $trainers['courseId'] = ($user->userinfo && $user->userinfo->course) ? $user->userinfo->course->COURSE_ID : '';
                    $trainers['courseName'] = ($user->userinfo && $user->userinfo->course) ? $user->userinfo->course->COURSE_NAME : null;
                  //  $trainers['paymentDetails'] = $user->userpaymentreceipt ? $user->userpaymentreceipt->UPR_PAYMENT_REF_NUMBER : '';
                  //  $trainers['paymentmode'] = $user->userpaymentreceipt ? _paymentMode($user->userpaymentreceipt->UPR_PAYMENT_MODE) : null;
                    $trainers['companyname'] = $user->userinfo ? $user->userinfo->UI_COMPANY : null;
                    $trainers['mobilenumber'] = $user->userinfo ? $user->userinfo->UI_PRIMARY_PHONE : null;
                    $trainers['paymentmode'] = $user->userinfo ? _paymentMode($user->userinfo->UI_PAYMENT_MODE) : null;
                    $trainers['paymentdetails'] = $user->userinfo ? $user->userinfo->UI_PAYMENT_ACC_DETAIL : null;
                    $trainers['ifsccode'] = $user->userinfo ? $user->userinfo->UI_PAYMENT_IFSC : null;
                    $trainers['trainerStudents'] = $user->trainerStudents;

                    array_push($trainersList,$trainers);

                }
                

                return $trainersList;
           
        } catch (Exception $e) {
            Log::error('Controller - UserController, function - getTrainersList, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }


    public function getMyStudentLists(Request $request)
    {
        try {

            $loginuserid = _getUserDetailsByToken($request);
                $studentDetails = Students::with(['course' => function ($query) {
                    $query->select('COURSE_ID', 'COURSE_NAME', 'COURSE_TYPE', 'COURSE_STATUS');
                },'referralinfo' => function ($query) {
                    $query->select('UI_ID', 'UI_USER_ID', 'UI_FIRST_NAME', 'UI_LAST_NAME');
                }])->where('STUDENT_TRAINER_ID', '=', $loginuserid)->where('STUDENT_STATUS', '=', 1)->select('STUDENT_ID', 'STUDENT_EMAIL', 'STUDENT_NAME', 'STUDENT_PHONE', 'STUDENT_PASSED_YEAR', 'STUDENT_STARTED_DATE', 'STUDENT_END_DATE', 'STUDENT_TOTAL_FEES', 'STUDENT_FEES_PAID', 'STUDENT_PENDING_FEES', 'STUDENT_COLLEGE', 'STUDENT_DEGREE', 'STUDENT_PAYMENT_MODE', 'STUDENT_PAYMENT_DATE', 'STUDENT_REFERRAL_ID', 'STUDENT_BATCH_ID', 'STUDENT_TRAINER_ID', 'STUDENT_COURSE_ID', 'STUDENT_STATUS')->get();
               
                return $studentDetails;
           
        } catch (Exception $e) {
            Log::error('Controller - StudentController, function - getStudentsList, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }


    public function getReferralList(Request $request)
    {
        try {

            $referrals = $referralsList = array();
            $studReferralId = Students::where('STUDENT_STATUS', '=', 1)->pluck('STUDENT_REFERRAL_ID')->toArray();
                $referralDetails = User::with(['userinfo' => function ($query) {
                    $query->select('UI_ID', 'UI_USER_ID', 'UI_FIRST_NAME', 'UI_LAST_NAME');
                }, 'referralStudents'])->whereIn('USER_ID', $studReferralId)->orwhere('USER_TYPE', '=', 2)->where('USER_STATUS', '=', 1)->get();
               
                foreach($referralDetails as $user){

                    $referrals['id'] = $user->USER_ID;
                    $referrals['uuid'] = $user->USER_UUID;
                    $referrals['name'] = _getFullUserName($user->userinfo);
                    $referrals['email'] = $user->USER_EMAIL;
                    $referrals['role'] = _getUserRole($user->USER_TYPE);
                    $referrals['companyname'] = $user->userinfo ? $user->userinfo->UI_COMPANY : null;
                    $referrals['mobilenumber'] = $user->userinfo ? $user->userinfo->UI_PRIMARY_PHONE : null;
                    $referrals['paymentmode'] = $user->userinfo ? _paymentMode($user->userinfo->UI_PAYMENT_MODE) : null;
                    $referrals['paymentdetails'] = $user->userinfo ? $user->userinfo->UI_PAYMENT_ACC_DETAIL : null;
                    $referrals['ifsccode'] = $user->userinfo ? $user->userinfo->UI_PAYMENT_IFSC : null;
                    $referrals['referralStudents'] = $user->referralStudents;

                    array_push($referralsList,$referrals);

                }
                

                return $referralsList;
           
        }catch (Exception $e) {
            Log::error('Controller - UserController, function - getReferralList, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }


    public function getMyReferredStudentsLists(Request $request)
    {
        try {

            $loginuserid = _getUserDetailsByToken($request);
                $studentDetails = Students::with(['course' => function ($query) {
                    $query->select('COURSE_ID', 'COURSE_NAME', 'COURSE_TYPE', 'COURSE_STATUS');
                },'referralinfo' => function ($query) {
                    $query->select('UI_ID', 'UI_USER_ID', 'UI_FIRST_NAME', 'UI_LAST_NAME');
                }])->where('STUDENT_REFERRAL_ID', '=', $loginuserid)->where('STUDENT_STATUS', '=', 1)->select('STUDENT_ID', 'STUDENT_EMAIL', 'STUDENT_NAME', 'STUDENT_PHONE', 'STUDENT_PASSED_YEAR', 'STUDENT_STARTED_DATE', 'STUDENT_END_DATE', 'STUDENT_TOTAL_FEES', 'STUDENT_FEES_PAID', 'STUDENT_PENDING_FEES', 'STUDENT_COLLEGE', 'STUDENT_DEGREE', 'STUDENT_PAYMENT_MODE', 'STUDENT_PAYMENT_DATE', 'STUDENT_REFERRAL_ID', 'STUDENT_BATCH_ID', 'STUDENT_TRAINER_ID', 'STUDENT_COURSE_ID', 'STUDENT_STATUS')->get();
               
                return $studentDetails;
           
        } catch (Exception $e) {
            Log::error('Controller - UserController, function - getStudentsList, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }

    
    public function getPaymentMethodList(Request $request)
    {
        try {


                $paymDetails = PaymentMethod::where('PAYM_STATUS', '=', 1)->get();
               
                return $paymDetails;
           
        } catch (Exception $e) {
            Log::error('Controller - UserController, function - getPaymentMethodList, Err:' . $e->getMessage());
            return response(array(
                'error' => 'model not found'
            ), 400);
        }
    }


   
}

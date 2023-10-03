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


class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('crm::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('crm::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('crm::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('crm::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }


    public function login(Request $request)
    {

        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $current_date = getCarbonObject();
            $formated_current_date = $current_date->toDateString();
            $ipaddress = isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$request->ip();

            $email = $request->input('email');
          //  $originalPassword = getOriginalString($request->input('password'));
            $pass = $request->input('password');
            $rememberMe = ($request->input('rememberMe')) ? ($request->input('rememberMe')) : 0;
            $emaildata = User::where('USER_EMAIL', '=', $email)->first();
            

            if (!empty($emaildata)) {
                if (1 == $emaildata->USER_STATUS) {
                    $userid = $emaildata->USER_ID;
                    $usertype = $emaildata->USER_TYPE;
                    $user = User::with(['userinfo' => function($query) {
                        $query->select('UI_ID', 'UI_USER_ID', 'UI_FIRST_NAME', 'UI_LAST_NAME');
                    },  'usertype'])->where('USER_PASSWORD', '=', $pass)->where('USER_ID', '=', $userid)->select('USER_ID', 'USER_EMAIL', 'USER_LOCATION_ID', 'USER_UUID', 'USER_TYPE')->get();
                    $user = sizeof($user) ? $user[0] : null;
                    

                    if (!empty($user)) {
                        $Loginfo = LogLogin::where('LOG_USER_ID', '=', $userid)
                            ->update(['LOG_STATUS' => 0]);
                       
                        $user['id'] = $user->USER_ID;
                        $user['apitoken'] = $this->_getUserToken($userid, $rememberMe);
                        $user['name'] = _getFullUserName($user);
                        $user['email'] = $user->USER_EMAIL;
                        $user['role'] = _getUserRole($user->USER_TYPE);
                        $currentTime = Carbon::now();
                        $user['currentTime'] = $currentTime;
                      //  $user['usertypes'] = $this->_getUserType();

                        return response($user);
                    } else {
                        $CreateLog = new LogLogin();
                        $CreateLog->LOG_USER_ID = $userid;
                        $CreateLog->LOG_LOGIN_TIME = $formated_current_date;
                        $CreateLog->LOG_IPADDRESS = $ipaddress;
                        $CreateLog->LOG_STATUS = 1;
                        $CreateLog->save();

                        $Loginfo = LogLogin::where('LOG_USER_ID', '=', $userid)
                            ->where('LOG_IPADDRESS', '=', $ipaddress)
                            ->where('LOG_LOGIN_TIME', '=', $formated_current_date)
                            ->where('LOG_STATUS', '=', 1)
                            ->count();
                        if (!(empty($Loginfo))) {
                            if ($Loginfo > 5) {
                                return response()->json(['errmessage' => 'Please click Forgot password and generate'], 400);
                            }
                        }
                        return response()->json(['errmessage' => 'Password is wrong'], 400);
                    }
                } else {
                    return response()->json(['errmessage' => 'User account deactivated/deleted.'], 402);
                }
            } else {
                return response()->json(['errmessage' => 'Email is wrong.'], 400);
            }
        } catch (Exception $e) {
            Log::error('Controller - LoginController, function - login, Err:' . $e->getMessage());
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

    public function _getUserType()
    {
        $usertypes = UserTypeMaster::select('UT_ID', 'UT_NAME', 'UT_STATUS', 'UT_EMPLOYER_STATUS')->get();
        return $usertypes;
    }

        /**
     * This REST resource To logout user
     * @param  \Illuminate\Http\Request  $request
     * @return Response as JSON Object
     */
    public function logout(Request $request)
    {
        try {
            $tokid = $request->header('apitoken');
            $tm1 = date("Y-m-d H:i:s");
            $tokObj = UserToken::where('UT_TOKEN', '=', $tokid)->update(['UT_DELETED_DATE' => $tm1]);
            return $tokObj;
        } catch (Exception $e) {
            Log::error('Controller - LoginController, function - logout, Err:' . $e->getMessage());
            return response(array(
                'error' => 'Cannot able to log out'
            ), 400);
        }
    }
}

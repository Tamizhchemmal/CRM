<?php

namespace App\Http\Middleware;

use Closure;
use Route;
use Log;
use Illuminate\Http\Request;

use Modules\Crm\Entities\UserToken;
use Carbon\Carbon;


class UserTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $tok = $request->header('apitoken');
        Log::info($tok);
        $curtime = Carbon::now('UTC')->format('Y-m-d H:i:s'); //date("Y-m-d H:i:s");
        $tokObj =  UserToken::where('UT_TOKEN', '=', $tok)->where('UT_EXPIRE_DATE', '>=', $curtime)->where('UT_DELETED_DATE', '=', NULL)->get();
        if (count($tokObj) == 0) {
            return response('Unauthorized token.', 401);
        } else {
            $rememberMe = $tokObj[0]->UT_IS_REMEMBER;
            if ($rememberMe) {
                $updateTimeString = strtotime(Carbon::now('UTC')->addDays(180)->format('Y-m-d H:i:s'));
            } else {
                $updateTimeString = strtotime($curtime) + (3600 * 3);
            }
            $updateTime =  UserToken::where('UT_TOKEN', '=', $tok)->update(['UT_EXPIRE_DATE' => date('Y-m-d H:i:s', $updateTimeString)]);
        }

        //File upload check
        if (in_array(strtolower($request->method()), ['put', 'post', 'options'])) {
            $input = $request->all();

            $encrypted_ids = ['id'];
            foreach ($encrypted_ids as $id) {
                if ($request->route()->parameter($id) && strlen($request->route()->parameter($id)) == 36) {
                    $request->route()->setParameter($id,  _userIdByUUID($request->route()->parameter($id)));
                }
            }

            array_walk_recursive($input, function(&$input, $input1) {
                if (request()->hasFile($input1)){
                    $file = request()->file($input1);
                   // echo "getMimeType - ".$file->getMimeType();
                    $validimageArry = array('image/jpeg', 'image/png', 'application/msword', 'application/pdf','application/vnd.openxmlformats-officedocument.wordprocessingml.document','text/plain', 'text/csv', 'application/csv', 'application/vnd.google-earth.kml+xml', 'application/xml','text/xml', 'application/vnd.ms-excel');
                    if (!in_array($file->getMimeType(), $validimageArry)){
                        abort(403);
                    }
                }
            });
        }

        //Filter Get method input parameters
        if (in_array(strtolower($request->method()), ['get'])) {
            
                $encrypted_ids = ['id', 'user', 'userinfo', 'dashboard', 'userid', 'userId', 'user_id', 'uid', 'studid', 'studentID', 'userID', 'clubid', 'stuid', 'uesrid', 'usernotesinfo', 'insurance', 'edituserid', 'created_by', 'modifiedBy', 'usrid', 'CLASS_CREATED_BY', 'CLASS_MODIFIED_BY', 'instrid', 'insid', 'followupby'];
                foreach ($encrypted_ids as $id) {
                    if ($request->route()->parameter($id) && strlen($request->route()->parameter($id)) == 36) {
                        $request->route()->setParameter($id,  _userIdByUUID($request->route()->parameter($id)));
                    }
                }

                $input = $request->all();
                array_walk_recursive($input, function(&$input, $input1) {
                    if (preg_match("/\s(AND|OR+)\s([^\s]+)\=([^\s]+)/m", $input)){
                        return response(array('message' => 'Forbidden'), 403);
                    }
                    $encrypted_ids = ['id', 'userid', 'userId', 'user_id', 'uid', 'studentID', 'userID', 'instructorId'];
                    if(($input1 == 'createdby' || $input1 == 'modifiedby') || (in_array($input1, $encrypted_ids) && strlen($input) == 36)) {
                        $input = _userIdByUUID($input);
                    }
                });
                $request->merge($input);
                return $next($request);
            
        }

        if (in_array(strtolower($request->method()), ['post'])) {
            if ($request->route()->parameter('usrid') && strlen($request->route()->parameter('usrid')) == 36) {
                $request->route()->setParameter('usrid',  _userIdByUUID($request->route()->parameter('usrid')));
            }
        }


        //XSS Protection
        if (!in_array(strtolower($request->method()), ['put', 'post', 'options'])) {
            return $next($request);
        }

        $uri = $request->path();
        global $skipElement;
        $uriArr = array();
        $skipElement = array();
        //$uriArr['flightmaster'] = array('description'); //Add/Edit Aircraft
        

        $input = $request->all();
        

        if(isset($uriArr[$uri])){
            $skipElement = $uriArr[$uri];
        } else {
            $skipElement = [];
        }

        if ($uri == 'api/user/changestatus' || $uri == 'api/user/checkTransaction') {
            $userids = $input['userid'];
            foreach ($userids as $key => $value) {
                if (strlen($value) == 36) {
                    $userids[$key] = _userIdByUUID($value);
                }
            }
            $input['userid'] = $userids;
        }

        array_walk_recursive($input, function (&$input, $input1) {
            global $skipElement;
            if (!in_array($input1, $skipElement) && ($input1 != 'eventDescription')) {
                $input = strip_tags($input);
                $input = preg_replace("/\s(AND|OR+)\s([^\s]+)\=([^\s]+)/m", "", $input);
                $input = preg_replace("/\s--/m", "", $input);
                // $input = preg_replace("/#/m", "", $input);
            }
            $encrypted_ids = ['id', 'userid', 'userId', 'user_id', 'uid', 'studentid', 'studentID', 'userID', 'createdby', 'modifiedby', 'createdBy', 'updatedby', 'acccreatedby', 'graduatedBy', 'studentId', 'instructorid', 'quiz_start_by', 'stuid', 'ACCINV_USER_ID', 'user_Id', 'signoffby', 'edituserid', 'created_by', 'modifiedBy', 'usrid', 'CLASS_CREATED_BY', 'CLASS_MODIFIED_BY', 'Tenantid', 'authorId', 'senderid', 'authourizedBy', 'instructor_id', 'followupby', 'verifiedby'];

            if(is_array($input) == false && (in_array($input1, $encrypted_ids) && strlen($input) == 36)) {
                
                    $input = _userIdByUUID($input);
        
            } 
        });
        $request->merge($input);
        return $next($request);
    }
}

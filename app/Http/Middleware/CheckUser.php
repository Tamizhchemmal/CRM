<?php

namespace App\Http\Middleware;

use Modules\Crm\Entities\UserToken;
use Request;
use Closure;
use Route;
use Log;

class CheckUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // print_r(Route::getFacadeRoot()->current()->uri()); exit;
        $tok = Request::header("apitoken");
        $curtime = date("Y-m-d H:i:s");
       // print_r($tok);exit;
        $tokObj = UserToken::with(["user" => function ($query) {
            $query->select("USER_ID", "USER_TYPE", "USER_UUID");
        }])->where("UT_TOKEN", "=", $tok)->where("UT_EXPIRE_DATE", ">=", $curtime)->where("UT_DELETED_DATE", "=", NULL)->get();
        if (sizeof($tokObj)) {
            //User role wise URI grouping starts
            $userroleURI = array();
            //Trainer
            $userroleURI[1] = [
                "api/crm/logout",
                "api/crm/user/getTrainerLists",
                "api/crm/user/getReferralLists",
                "api/crm/batch/create",
                "api/crm/batch/list",
                "api/crm/course/list",
                "api/crm/students/list",
                "api/crm/user/getMyStudents",
                "api/crm/user/getMyReferrals",
                "api/crm/students/{id}",
                "api/crm/batch/{id}",
                "api/crm/course/{id}",
                "api/crm/user/{uuid}",
               
            ];

            //Referral
            $userroleURI[2] = [
                "api/crm/logout",
                "api/crm/user/getMyReferrals",
                
            ];

            //Admin
            $userroleURI[3] = [
                "api/crm/logout",
                "api/crm/user/add",
                "api/crm/user/update",
                "api/crm/user/activeLists",
                "api/crm/user/getTrainerLists",
                "api/crm/user/getReferralLists",
                "api/crm/batch/create",
                "api/crm/batch/list",
                "api/crm/course/create",
                "api/crm/course/list",
                "api/crm/students/add",
                "api/crm/students/update",
                "api/crm/students/list",
                "api/crm/students/{id}",
                "api/crm/batch/{id}",
                "api/crm/course/{id}",
                "api/crm/user/{uuid}",
               
            ];

            $uri = Route::getFacadeRoot()->current()->uri();
            $userObj = $request->route()->parameters();
            $tokObjUserId = $tokObj[0]->user->USER_UUID;
            $tm1 = date("Y-m-d H:i:s");
            $activeUserType = $tokObj[0]->user->USER_TYPE;

           
            if (!in_array($uri, $userroleURI[$activeUserType], true)) {
                Log::error("Middleware - CheckUser, UserType: " . $activeUserType . ", URI = " . $uri);
                $tokObj = UserToken::where("UT_TOKEN", "=", $tok)->update(["UT_DELETED_DATE" => $tm1]);
                return response("Unauthorized token.", 401);
            }

            // if (isset($userId) && ($tokObjUserId != $userId)) {
            //     Log::error("Middleware - CheckUser, UserType: " . $activeUserType . ", URI = " . $uri. " User id Mismatch. Logged In User Id : " . $tokObjUserId . " Data For " . $userId);
            //     $tokObj = UserToken::where("UT_TOKEN", "=", $tok)->update(["UT_DELETED_DATE" => $tm1]);
            //     return response("Unauthorized token.", 401);
            // }

           
            return $next($request);
        } else {
            Log::error("User token expired");
            return response("Unauthorized token.", 401);
        }
    }
}

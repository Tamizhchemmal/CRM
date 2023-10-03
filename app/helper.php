<?php

use Illuminate\Http\Request;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Crm\Entities\User;
use Modules\Crm\Entities\UserToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Tenant\Entities\EmailLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers\FileHelper;
use Webpatser\Uuid\Uuid;
use Illuminate\Support\Facades\Crypt;
use Modules\Crm\Entities\UserTypeMaster;
use Modules\Crm\Entities\PaymentMethod;


function _getUserDetailsByToken(Request $request)
{
    try {
        $apitoken = $request->header('apitoken');
        $curtime = date("Y-m-d H:i:s");
        if (!$apitoken)
            return false;

        $userObj = UserToken::where('UT_TOKEN', '=', $apitoken)->where('UT_EXPIRE_DATE', '>=', $curtime)->where('UT_DELETED_DATE', '=', NULL)->select('UT_USER_ID')->first();
        if ($userObj)
            return $userObj->UT_USER_ID;
        else
            return false;
    } catch (Exception $e) {
        Log::error('Helper, function - getUserDetailsByToken, Err:' . $e->getMessage());
        return false;
    }
}



function _userIdByUUID($id = '')
{
    try {
        // get user id from UUID
        $user = User::firstOrNew(array('USER_UUID' => $id));
        return $user->USER_ID;
    } catch (Exception $e) {
        Log::error('Helper, function - _userIdByUUID, Err:' . $e->getMessage());
        return 'error';
    }
}

function _userTypeById($id = '')
{
    try {
        $user = User::firstOrNew(array('USER_ID' => $id));
        return $user->USER_TYPE;
    } catch (Exception $e) {
        Log::error('Helper, function - _userIdByUUID, Err:' . $e->getMessage());
        return 'error';
    }
}



function _getSubstrDate($date)
{
    try {
        $transactionDate = $date;
        if (10 == strlen($transactionDate)) {
            $transactionDate .= ' 00:00';
        } else if (19 == strlen($transactionDate)) {
            $transactionDate = substr($transactionDate, 0, (strlen($transactionDate) - 3));
        } else {
            $transactionDate = substr($transactionDate, 0, (strlen($transactionDate) - 9));
        }
        return $transactionDate;
    } catch (Exception $e) {
        Log::error('Helper, function - _getSubstrDate, Err:' . $e->getMessage());
        return false;
    }
}

function _convertToTimezone($dateString, $tenantId)
{
    try {
        $timezone = 'UTC';
        $tenant = Tenant::where('TENANT_ID', '=', $tenantId)->pluck('TENANT_TIMEZONE');
        if (sizeof($tenant)) {
            $timezone = $tenant[0];
        }
        return Carbon::parse(_timezoneconversion($dateString, $timezone, 'UTC'));
    } catch (Exception $e) {
        Log::error('Helper, function - _tenantIdByUUID, Err:' . $e->getMessage());
        return 'error';
    }
}

function _getCarbonDate($action, $days = 0, $format = 'datetime')
{
    $date = getCarbonObject();
    switch ($action) {
        case 'subDay':
            $date = getCarbonObject()->subDay();
            break;
        case 'addDay':
            $date = getCarbonObject()->addDay();
            break;
        case 'subDays':
            $date = getCarbonObject()->subDays($days);
            break;
        case 'addDays':
            $date = getCarbonObject()->addDays($days);
            break;
        case 'subHour':
            $date = getCarbonObject()->subHour();
            break;
        default:
            # code...
            break;
    }
    $date = ($format == 'datetime') ? $date->toDateTimeString() : $date->toDateString();
    return $date;
}

function _getFullUserName($userData)
{
    $name = '';
    if ($userData) {
        $name = $userData->USER_EMAIL;
        if ($userData->userinfo) {
            $userProfile = $userData->userinfo;
            $firstName = $userProfile->UI_FIRST_NAME;
            $lastName = $userProfile->UI_LAST_NAME;
            if (($firstName && ('null' != $firstName)) && ($lastName && ('null' != $lastName))) {
                $name = $firstName . ' ' . $lastName;
            } else if ($firstName && ('null' != $firstName)) {
                $name = $firstName;
            } else if ($lastName && ('null' != $lastName)) {
                $name = $lastName;
            }
        }
    }
    return $name;
}





function getCurrentDate()
{
    return getCarbonObject()->format('Y-m-d');
}

function parseAndGetDate($dateTimeString)
{
    return Carbon::parse($dateTimeString)->format('Y-m-d');
}

function createFromFormattedDate($dateString, $action)
{
    switch ($action) {
        case 1:
            return Carbon::createFromFormat('Y-m-d H:i', $dateString);
            break;
        case 2:
            return Carbon::createFromFormat('Y-m-d H:i:s', $dateString);
            break;
        default:
            return getCarbonObject();
    }
}

function createNewCollectionObject()
{
    return new Collection();
}



function getCarbonObject()
{
    return Carbon::now();
}


function parseDateObject($dateConvertedObj)
{
    return Carbon::parse($dateConvertedObj);
}

function getTodayStamp()
{
    return getCarbonObject()->format('Ymd\THis');
}

function createDateFromFormat($type, $dateTimeString)
{
    switch ($type) {
        case 'fullDateTimeString':
            return Carbon::createFromFormat('Y-m-d H:i:s', $dateTimeString);
            break;
        case 'dateTimeString':
            return Carbon::createFromFormat('Y-m-d H:i', $dateTimeString);
            break;
        default:
            return getCarbonObject();
    }
}


function generatePassword()
{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass);
}

function generateUUID()
{
    return Uuid::generate();
}

function hashedstring($dataString)
{
    return md5(trim($dataString));
}



function _sendEmail($personInfo)
{

    $user = User::firstOrNew(array('USER_EMAIL' => $personInfo['email']));
    if ($personInfo['template'] != 'rolechange') {
        if ($user->USER_TYPE == 6) {
            return false;
        }
    }
    $personInfo['support_email'] = config('constants.support_email');
    $personInfo['from_email'] = config('constants.from_email');
    $personInfo['team_name'] = config('constants.team_name');
    $personInfo['site_name'] = config('constants.site_name');
    $personInfo['email_title'] = 'Needlenine';
    $personInfo['email_content'] = isset($personInfo['email_content']) ? $personInfo['email_content'] : '';
    $personInfo['sent_flag'] = isset($personInfo['sent_flag']) ? $personInfo['sent_flag'] : 1;
    $data = ['personInfo' => $personInfo];
    $email_template = isset($personInfo['template']) ? 'emails.' . $personInfo['template'] : '';
    // insert into the email log queue
    _saveLog($personInfo, $user->USER_ID, $user->USER_TENANT_ID);

    $validation = true;
    if(_environmentCheck() && !_validateEmailDomain($personInfo['email']))
        $validation = false;

    if ($personInfo['sent_flag'] == 1 && $validation == true) {
        if ($email_template) {
            return Mail::send($email_template, $data, function ($m) use ($personInfo) {
                $m->from($personInfo['from_email'], $personInfo['email_title']);
                $m->to($personInfo['email'], $personInfo['name'])->subject($personInfo['subject']);
                // For Attachments
                if (isset($personInfo['attachment']) && count($personInfo['attachment']) > 0) {
                    foreach ($personInfo['attachment'] as $k => $val) {
                        if (isset($val['mime'])) {
                            $m->attach($val['file_path'], array('as' => $val['file_name'], 'mime' => $val['mime']));
                        } else {
                            $m->attachData($val['file_path'], $val['file_name']);
                        }
                    }
                }
            });
        } else {
            return Mail::raw($personInfo['email_content'], function ($m) use ($personInfo) {
                $m->from($personInfo['from_email'], config('constants.email_title'));
                $m->to($personInfo['email'], $personInfo['name'])->subject($personInfo['subject']);
                // For Attachments
                if (isset($personInfo['attachment']) && count($personInfo['attachment']) > 0) {
                    foreach ($personInfo['attachment'] as $k => $val) {
                        $m->attach($val['file_path'], array('as' => $val['file_name'], 'mime' => $val['mime']));
                    }
                }
            });
        }
    }
}

function getUniqueSysFileName($documentSubType, $id, $extension)
{
    if ($id) {
        return $documentSubType . '_' . $id . '_' . strtotime(date("Y-m-d") . date("H:i:s")) . '.' . $extension;
    } else {
        return $documentSubType . '_' . strtotime(date("Y-m-d") . date("H:i:s")) . '.' . $extension;
    }
}


function unlinkDocuments($doc, $path)
{
    $publicPath = public_path();
    switch ($path) {
        case 'tenant':
            $unlinkThumbnail = $publicPath . '/assets/uploads/tenant/thumbnails/' . $doc;
            if (file_exists($unlinkThumbnail)) {
                unlink($unlinkThumbnail);
            }
            $unlinkDoc = $publicPath . '/assets/uploads/tenant/' . $doc;
            if (file_exists($unlinkDoc)) {
                unlink($unlinkDoc);
            }
            break;
        default:
            return $publicPath;
    }
}


function _getUserByToken(Request $request)
{
    try {
        $apitoken = $request->header('apitoken');
        if (!$apitoken)
            return false;

        $userObj = UserToken::with(['user' => function ($query) {
            $query->select('USER_ID', 'USER_TYPE', 'USER_TENANT_ID');
        }])->where('UT_TOKEN', '=', $apitoken)->where('UT_DELETED_DATE', '=', NULL)->select('UT_USER_ID')->first();
        if($userObj)
            return $userObj->user;
        else
            return false;
    } catch(Exception $e) {
        Log::error('Helper, function - getUserDetailsByToken, Err:'.$e->getMessage());
        return false;
    }
}

function _removeToken(Request $request)
{
    try {
        $apitoken = $request->header('apitoken');
        $curtime = date("Y-m-d H:i:s");
        $tokObj = UserToken::where('UT_TOKEN', '=', $apitoken)->update(['UT_DELETED_DATE' => $curtime]);
        if($tokObj)
            return $tokObj;
        else
            return false;
    } catch(Exception $e) {
        Log::error('Helper, function - getUserDetailsByToken, Err:'.$e->getMessage());
        return false;
    }
}



function _paymentMode($paymentModeId)
{
    try {
       
        $cardType = PaymentMethod::where('PAYM_ID',$paymentModeId)->select('PAYM_NAME')->first();
        return $cardType->PAYM_NAME;
    } catch(Exception $e) {
        Log::error('Helper, function - paymentMode, Err:'.$e->getMessage());
        return false;
    }
}



function _getUserRole($roleId) {
    $userRole = UserTypeMaster::where("UT_ID", "=", $roleId)->where('UT_STATUS', '=', 1)->select('UT_NAME')->first();
    return $userRoleName = $userRole ? $userRole->UT_NAME : '';

}




?>

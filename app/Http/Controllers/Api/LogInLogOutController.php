<?php
namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Model\LogInLogOut;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use DB;

class LogInLogOutController extends BaseController
{
    public $timezone;
    public $limit;

    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';   
        $this->limit = 99999999;    
    }

    public function index()
    {
        return 'Index';
    }
    
    public function getLogInLogOut(request $request)
    {      
        $response = array();

        $this->validate($request, [
            'from_system' => 'required'
        ]);

        $limit          = $request->get('limit');
        $page           = $request->get('page');
        $from_system    = $request->get('from_system');
        $startDateTime  = $request->get('startDateTime');
        $endDateTime    = $request->get('endDateTime');
        $keyword        = $request->get('keyword');
        $type           = $request->get('type');
        $user_id        = $request->get('user_id');
        $result         = $request->get('result');
        $ip_address     = $request->get('ip_address');
        $user_agent     = $request->get('user_agent');
        
        if(isset($page) && !isset($limit) || !isset($page) && isset($limit))
        {
            return response(array(
                'Message' => 'Please send parameter ?page=x&limit=y'
            ), '400');
        }

        if(!isset($limit))
        {
            $limit = $this->limit;
        }

        $LogInLogOuts = LogInLogOut::where('from_system', '=', $from_system);
        
        if(isset($startDateTime) && !empty($startDateTime))
        {
            $startDateTime_ = Carbon::parse($startDateTime);
            $startDateTime_->setTimezone($this->timezone);
        }

        if(isset($endDateTime) && !empty($endDateTime))
        {
            $endDateTime_ = Carbon::parse($endDateTime);
            $endDateTime_->setTimezone($this->timezone);
        }

        if(isset($startDateTime) && !empty($startDateTime) && isset($endDateTime) && !empty($endDateTime))
        {
            $arr_start = explode(" ", $startDateTime_);
            $arr_startDate = explode("-", $arr_start[0]);
            $arr_startTime = explode(":", $arr_start[1]);

            $arr_end = explode(" ", $endDateTime_);
            $arr_endDate = explode("-", $arr_end[0]);
            $arr_endTime = explode(":", $arr_end[1]);

            $LogInLogOuts = $LogInLogOuts->whereBetween('created_at', array(
                Carbon::create($arr_startDate[0], $arr_startDate[1], $arr_startDate[2], $arr_startTime[0], $arr_startTime[1], $arr_startTime[2]),
                Carbon::create($arr_endDate[0], $arr_endDate[1], $arr_endDate[2], $arr_endTime[0], $arr_endTime[1], $arr_endTime[2])));
        }

        if(isset($keyword) && !empty($keyword))
        {
            $LogInLogOuts = $LogInLogOuts->where('keyword', '=', $keyword);
        }

        if(isset($type) && !empty($type))
        {
            $LogInLogOuts = $LogInLogOuts->where('type', '=', $type);
        }

        if(isset($user_id) && !empty($user_id))
        {
            $LogInLogOuts = $LogInLogOuts->where('user_id', '=', $user_id);
        }

        if(isset($result) && !empty($result))
        {
            $LogInLogOuts = $LogInLogOuts->where('result', '=', $result);
        }

        if(isset($ip_address) && !empty($ip_address))
        {
            $LogInLogOuts = $LogInLogOuts->where('ip_address', '=', $ip_address);
        }

        if(isset($user_agent) && !empty($user_agent))
        {
            $LogInLogOuts = $LogInLogOuts->where('user_agent', 'like', "%".$user_agent."%");
        }

        $LogInLogOuts = $LogInLogOuts->orderBy('created_at', 'asc')->paginate((int)$limit);

        $response['paging']['count'] = $LogInLogOuts->count();
        $response['paging']['currentPage'] = $LogInLogOuts->currentPage();
        $response['paging']['firstItem'] = $LogInLogOuts->firstItem();
        $response['paging']['hasMorePages'] = $LogInLogOuts->hasMorePages();
        $response['paging']['lastItem'] = $LogInLogOuts->lastItem();
        $response['paging']['lastPage'] = $LogInLogOuts->lastPage();

        if(!is_null($LogInLogOuts->nextPageUrl()))
        {
            $response['paging']['nextPageUrl'] = $LogInLogOuts->nextPageUrl()."&limit=".$limit."&from_system=".$from_system."&keyword=".$keyword."&type=".$type."&user_id=".$user_id."&result=".$result."&ip_address=".$ip_address."&user_agent=".$user_agent."&startDateTime=".$startDateTime."&endDateTime=".$endDateTime;
        }
        else
        {            
            $response['paging']['nextPageUrl'] = $LogInLogOuts->nextPageUrl();
        }

        $response['paging']['onFirstPage'] = $LogInLogOuts->onFirstPage();
        //$response['paging']['perPage'] = $LogInLogOuts->perPage();
                
        if(!is_null($LogInLogOuts->previousPageUrl()))
        {
            $response['paging']['previousPageUrl'] = $LogInLogOuts->previousPageUrl()."&limit=".$limit."&from_system=".$from_system."&keyword=".$keyword."&type=".$type."&user_id=".$user_id."&result=".$result."&ip_address=".$ip_address."&user_agent=".$user_agent."&startDateTime=".$startDateTime."&endDateTime=".$endDateTime;
        }
        else
        {            
            $response['paging']['previousPageUrl'] = $LogInLogOuts->previousPageUrl();
        }
                
        $response['paging']['total'] = $LogInLogOuts->total();

        foreach($LogInLogOuts as $key => $LogInLogOut)
        {    
                                                   
            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $LogInLogOut->created_at);
            $created_at = $dt->format(DateTime::ISO8601); 
            
            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $LogInLogOut->updated_at);
            $updated_at = $dt->format(DateTime::ISO8601); 

            $x = 0;

            foreach($LogInLogOut->toArray() as $keyColumn => $value)
            {                
                if($value)
                {    
                    if($keyColumn != 'created_at' || $keyColumn != 'updated_at')
                    {
                        $array_key[$x] = $keyColumn;
                        $array_val[$x] = $value;
    
                        $x++;
                    }
                }
            }
            
            $response['links'] = array();

            for($y=0;$y<$x;$y++)
            {
                $response['content'][$key][$array_key[$y]] = $array_val[$y];
            }

            $response['content'][$key]['created_at'] = $created_at;
            $response['content'][$key]['updated_at'] = $updated_at;
        }

        return $response;
    }

    public function postLogInLogOut(request $request)
    {
        $this->validate($request, [
            'keyword' => 'required',
            'from_system' => 'required'
        ]);

        $loginlogout = new LogInLogOut;

        foreach($request->request as $key => $value)
        {
            $loginlogout->$key = $value;
        }

        $loginlogout->save();

        if($loginlogout != null)
        {
            return response()->json([
                'status'     => true,
                'messages'   => 'Success',
                'errors'     => []
            ], 201);
            
        }
        else
        {
            return response()->json([
                'status'     => false,
                'messages'   => 'Internal Code Error',
                'errors'     => []
            ], 500);
        }
    }
}
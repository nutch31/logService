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
    public $rule_type;
    public $rule_from_system;

    public function __construct()
    {
        header('Content-Type: application/json;charset=UTF-8'); 
        $this->timezone = 'GMT';   
        $this->limit = 99999999;   
        $this->rule_type = array('login', 'logout'); 
        $this->rule_from_system = array('alpha1', 'alpha2');
    }

    public function index()
    {
        return 'Index';
    }
    
    public function getLogInLogOut_BACKUP(request $request)
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

    public function getLogInLogOut(request $request)
    {      
        $response = array();

        $this->validate($request, [
            'from_system' => 'required',
            'startDateTime' => 'required|date_format:Y-m-d\TH:i:sO',
            'endDateTime' => 'required|date_format:Y-m-d\TH:i:sO'
        ]);

        $page           = $request->get('page');
        $limit          = $request->get('limit');
        $from_system    = $request->get('from_system');
        $keyword        = $request->get('keyword');
        $type           = $request->get('type');
        $user_id        = $request->get('user_id');
        $result         = $request->get('result');
        $ip_address     = $request->get('ip_address');
        $user_agent     = $request->get('user_agent');
        $startDateTime  = $request->get('startDateTime');
        $endDateTime    = $request->get('endDateTime');
        
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

            $dt = $arr_startDate[0]."-".$arr_startDate[1]."-".$arr_startDate[2]." ".$arr_startTime[0].":".$arr_startTime[1].":".$arr_startTime[2];
            $dt2 = $arr_endDate[0]."-".$arr_endDate[1]."-".$arr_endDate[2]." ".$arr_endTime[0].":".$arr_endTime[1].":".$arr_endTime[2];

            $LogInLogOuts = $LogInLogOuts->whereBetween('date_time.date', array($dt, $dt2));
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
            $LogInLogOuts = $LogInLogOuts->where('content.user_id', '=', $user_id);
        }

        if(isset($result) && !empty($result))
        {
            $LogInLogOuts = $LogInLogOuts->where('content.result', '=', $result);
        }

        if(isset($ip_address) && !empty($ip_address))
        {
            $LogInLogOuts = $LogInLogOuts->where('content.ip_address', '=', $ip_address);
        }

        if(isset($user_agent) && !empty($user_agent))
        {
            $LogInLogOuts = $LogInLogOuts->where('content.user_agent', 'like', "%".$user_agent."%");
        }

        $LogInLogOuts = $LogInLogOuts->orderBy('date_time.date', 'asc')->paginate((int)$limit);

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
            $dt = Carbon::createFromFormat('Y-m-d H:i:s.u', $LogInLogOut->date_time["date"]);
            $date_time = $dt->format(DateTime::ISO8601); 
                                                   
            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $LogInLogOut->created_at);
            $created_at = $dt->format(DateTime::ISO8601); 
            
            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $LogInLogOut->updated_at);
            $updated_at = $dt->format(DateTime::ISO8601); 
            
            $response['links'] = array();

            $response['content'][$key]['_id'] = $LogInLogOut->_id;
            $response['content'][$key]['keyword'] = $LogInLogOut->keyword;
            $response['content'][$key]['from_system'] = $LogInLogOut->from_system;
            $response['content'][$key]['type'] = $LogInLogOut->type;
            $response['content'][$key]['date_time'] = $date_time;
            $response['content'][$key]['content'] = $LogInLogOut->content;
            $response['content'][$key]['created_at'] = $created_at;
            $response['content'][$key]['updated_at'] = $updated_at;
        }

        return $response;
    }

    public function postLogInLogOut_BACKUP(request $request)
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
    
    public function postLogInLogOut(request $request)
    {
        $json = file_get_contents("php://input");
        $array = json_decode($json, true);

        $request['rule_type'] = $this->rule_type;
        $request['rule_from_system'] = $this->rule_from_system;

        $request['keyword']     = $array['keyword'];
        $request['from_system'] = $array['from_system'];
        $request['type']        = $array['type'];
        $request['date_time'] = $array['date_time'];
        $request['content']     = $array['content'];

        $this->validate($request, [
            'keyword' => 'required',
            'from_system' => 'required|in_array:rule_from_system.*',
            'type' => 'required|in_array:rule_type.*',
            'date_time' => 'required|date_format:Y-m-d\TH:i:sO'
        ]);

        
        $date_time = Carbon::parse($request->date_time);
        $date_time->setTimezone($this->timezone);

        $loginlogout = new LogInLogOut;
        $loginlogout->keyword       = $request->keyword;
        $loginlogout->from_system   = $request->from_system;
        $loginlogout->type          = $request->type;
        $loginlogout->date_time     = $date_time;
        $loginlogout->content       = $request->content;
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
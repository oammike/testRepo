<?php

namespace OAMPI_Eval\Http\Controllers;

use Carbon\Carbon;
use Excel;
use \PDF;
use \App;
use \DB;
use \Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;

use Yajra\Datatables\Facades\Datatables;

use OAMPI_Eval\Http\Requests;
use OAMPI_Eval\User;
use OAMPI_Eval\Campaign;
use OAMPI_Eval\Status;
use OAMPI_Eval\Team;
use OAMPI_Eval\Floor;
use OAMPI_Eval\UserType;
use OAMPI_Eval\Position;
use OAMPI_Eval\ImmediateHead;
use OAMPI_Eval\ImmediateHead_Campaign;
use OAMPI_Eval\EvalForm;
use OAMPI_Eval\EvalSetting;
use OAMPI_Eval\Schedule;
use OAMPI_Eval\Restday;
use OAMPI_Eval\Cutoff;
use OAMPI_Eval\Biometrics;
use OAMPI_Eval\Biometrics_Uploader;
use OAMPI_Eval\Logs;
use OAMPI_Eval\LogType;
use OAMPI_Eval\TempUpload;
use OAMPI_Eval\User_DTR;

class LogsController extends Controller
{
    protected $user;
   	protected $logs;
   	protected $paycutoff;

     public function __construct(Logs $logs)
    {
        $this->middleware('auth');
        $this->logs = $logs;
        $this->user =  User::find(Auth::user()->id);
        $this->paycutoff = Cutoff::first();
    }

    public function index()
    {
    	
    	//return $this->cutoff->first()->startingPeriod(). " - " . $paycutoff->endingPeriod();
    }

    public function myDTR()
    {
    	//$roles = UserType::find($this->user->userType_id)->roles->pluck('label'); //->where('label','MOVE_EMPLOYEES');
        //$canMoveEmployees =  ($roles->contains('MOVE_EMPLOYEE')) ? '1':'0';
        //$canEditEmployees =  ($roles->contains('EDIT_EMPLOYEE')) ? '1':'0';

        //$hrDept = Campaign::where('name',"HR")->first();

       // return $this->paycutoff->startingPeriod(). " - " . $this->paycutoff->endingPeriod();

        $user = $this->user; //User::find($id); 
        $leadershipcheck = ImmediateHead::where('employeeNumber', $user->employeeNumber)->first();
        $immediateHead = ImmediateHead::find(ImmediateHead_Campaign::find($user->supervisor->immediateHead_Campaigns_id)->immediateHead_id);

         if (!empty($leadershipcheck)){ $camps = $leadershipcheck->campaigns->sortBy('name'); } else $camps = $user->campaign;

    	$dtr = $this->user->logs->sortBy('id')->groupBy('biometrics_id');
    	//return $myDTR;
    	$myDTR = new Collection;

    	 foreach ($dtr as $daily) {

    	 	$logIN = $daily->where('logType_id',1)->sortBy('id')->pluck('logTime'); //->get();
    	 	$logOUT = $daily->where('logType_id',2)->sortBy('id')->pluck('logTime'); //->get();

    	 	if (count($logIN) > 0)
    	 	{
    	 		$in = $logIN->first();
    	 		$timeStart = Carbon::parse($in);

    	 	}  else { $in=null; $timeStart=null; }
    	 	if (count($logOUT) > 0)
    	 	{
    	 		$out = $logOUT->first();
    	 		$timeEnd = Carbon::parse($out); 
    	 	} else { $out=null; $timeEnd=null; }

    	 	if ($in !== null && $out !== null)
    	 	{
    	 		//$coll->push(['in'=>$in, 'out'=>$out]);
    	 		$workedHours = $timeEnd->diffInMinutes($timeStart->addHour());

    	 	} else $workedHours=null;

    	 	//DB::table('user_dtr')->insert(['user_id'=>$key[0]->user_id, 'timeIN']);
    	 $myDTR->push(['biometrics_id'=>$daily[0]->biometrics_id, 'user_id'=>$daily[0]->user_id, 'Time IN'=> $in, 'Time OUT'=> $out, 'Hours Worked'=> round($workedHours/60,2) ]);
    	 }
    	 //return $myDTR;

    	return view('timekeeping.myDTR', compact('myDTR','camps','user'));
    }


    public function saveDailyUserLogs(Request $request)
    {
    	DB::connection()->disableQueryLog();
    	$biometrics_id = $request->biometrics_id;

    	$ctr = 0;
    	// DB::table('temp_uploads')->select('employeeNumber','logTime','logType')->where('productionDate',date('Y-m-d', strtotime($request->productionDate)))->chunk(100, function($biosToGet, $biometrics_id, $ctr)
    	// {
    	// 	foreach ($biosToGet as $bio) {
	    // 		$logType = LogType::where('code',$bio->logType)->first()->id;
	    // 		$user_id = User::where('employeeNumber',$bio->employeeNumber)->first()->id;

	    // 		DB::table('logs')->insert(['user_id'=>$user_id, 'logTime'=>$bio->logTime, 'logType_id'=>$logType, 'biometrics_id'=>$biometrics_id]);
	    // 		$ctr++;
	    // 	}
    	// })->get();


    	$productionDate = date('Y-m-d', strtotime($request->productionDate));
    	$biosToGet = TempUpload::where('productionDate',$productionDate)->get();
    	foreach ($biosToGet as $bio) {
	    		$logType = LogType::where('code', strtoupper($bio->logType) )->first()->id;
	    		$user_id = User::where('employeeNumber',$bio->employeeNumber)->get();
	    		if (count($user_id) > 0 )
	    		{
	    			DB::table('logs')->insert(['user_id'=>$user_id->first()->id, 'logTime'=>$bio->logTime, 'logType_id'=>$logType, 'biometrics_id'=>$biometrics_id]);

	    			//save actual user DTR table
	    			// switch ($logType) {
	    			// 	case '1':
	    			// 				$logIN = $bio->logTime;
	    			// 		break;

	    			// 	case '2':
	    			// 				$logOUT = $bio->logTime;
	    			// 		break;

	    			// 	case '3':
	    			// 				$breakIN = $bio->logTime;
	    			// 		break;

	    			// 	case '4':
	    			// 				$breakOUT = $bio->logTime;
	    			// 		break;

	    			// 	case '5':
	    			// 				$breakOUT = $bio->logTime;
	    			// 		break;

	    			// 	case '6':
	    			// 				$breakIN = $bio->logTime;
	    			// 		break;
	    				
	    			// 	default:
	    			// 				$logIN = $bio->logTime;
	    			// 		break;
	    			// }

	    			// DB::table('user_dtr')->insert(['user_id'=>$user_id->first()->id, 'timeIN'=> ]);


	    			/* ---------------SAVE USER_DTR -------------*/

	    			//$logIN = $daily->where('logType_id',1)->sortBy('id')->pluck('logTime'); //->get();
		    	 	// $logOUT = $daily->where('logType_id',2)->sortBy('id')->pluck('logTime'); //->get();

		    	 	// if (count($logIN) > 0)
		    	 	// {
		    	 	// 	$in = $logIN->first();
		    	 	// 	$timeStart = Carbon::parse($in);

		    	 	// }  else { $in=null; $timeStart=null; }
		    	 	// if (count($logOUT) > 0)
		    	 	// {
		    	 	// 	$out = $logOUT->first();
		    	 	// 	$timeEnd = Carbon::parse($out); 
		    	 	// } else { $out=null; $timeEnd=null; }

		    	 	// if ($in !== null && $out !== null)
		    	 	// {
		    	 	// 	//$coll->push(['in'=>$in, 'out'=>$out]);
		    	 	// 	$workedHours = $timeEnd->diffInMinutes($timeStart);
		    	 	// } else $workedHours=null;


	    			/* ---------------END SAVE DTR --------------*/


	    			$ctr++;

	    		}//enf if
	    		

	    		
	    	}
    	return response()->json(['save'=>'success', 'records'=>$ctr]);

    	

    }

    public function viewRawBiometricsData($id)
    {
        $user = User::find($id);
        $dtr = $user->logs->sortBy('id')->groupBy('biometrics_id');
        //return $dtr;

        $record = new Collection;
        $record1 = new Collection;
        
        foreach ($dtr as $daily) {

            $rdata = new Collection;
            foreach ($daily as $data) {

              $rdata->push(['Employee Number'=>User::find($data['user_id'])->employeeNumber, 'Log Type'=>LogType::find($data->logType_id)->name, 'Log Time'=>$data->logTime]);
           }
            $record1->push(['id'=>$daily[0]['biometrics_id'], 'Production Date'=>date('M d, Y D',strtotime(Biometrics::find($daily[0]['biometrics_id'])->productionDate)),'data'=>$rdata]);
        }
        $record = $record1->sortByDesc('id');
        
        return view('timekeeping.rawBio', compact('record'));

    }
}

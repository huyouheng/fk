<?php

namespace App\Http\Controllers;

use App\Company;
use App\Employee;
use Illuminate\Http\Request;
use App\Report;
use App\Enterprise;
use App\Industry;
use App\TownType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class StatisticalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->town_id){
            //局
            $enterprise_ids = Enterprise::whereBetween('IndustryTableID',[$user->industry_id_min, $user->industry_id_max])
                ->select('EnterpriseID')
                ->pluck('EnterpriseID');
            $statusGroup =  Report::whereIn('enterprise_id',$enterprise_ids)
                ->groupBy('status')
                ->selectRaw('status, count(status)')
                ->pluck('count','status')
                ->toJson();

        } else {
            //乡镇
            $statusGroup = Report::where('town_id',$user->town_id)
                ->groupBy('status')
                ->selectRaw('status, count(status)')
                ->pluck('count','status')
                ->toJson();
        }
        return view('statistic.reported', compact('statusGroup'));

    }

    /**
     * statistical data
     */
    public function statisticalData(Request $request)
    {
        $user = $request->user();
        $model = Report::with(['enterpry:EnterpriseID,BackEmpNumber,EmployeesNumber']);
        if (!empty($user->industry_id_min) && !empty($user->industry_id_max)) {
            if ($_data = Redis::hGet('blgov:statistic:admins', $user->industry_id_min.$user->industry_id_max)) {
                return json_decode($_data, true);
            }
            $model->industryBetween($user->industry_id_min, $user->industry_id_max);
        }
        if (!empty($user->town_id)) {
            if ($_data = Redis::hGet('blgov:statistic:town_ids', $user->town_id)) {
                return json_decode($_data, true);
            }
            $model->where('town_id', $user->town_id);
        }

        $data = $model->get()->pluck('enterpry');

        $BackEmpNumber = $data->sum('BackEmpNumber');
        $EmployeesNumber = $data->sum('EmployeesNumber');
        $isNeedMedicalObservation = 0;
        $outgoingDesc = [];
        $contactSituation = [
            'isContactSituation' => 0,
            'notContactSituation' => 0,
        ];
        $wu = 0; //0
        $hubei = 0;//1
        $wenzhou = 0;//2
        $taizhou = 0;//3
        $other = 0;//4

        $users = [];
        foreach ($data->pluck('users') as $key => $value) {
            if ($value) {
                $users = array_merge($users, $value->toArray());
            }
        }
        foreach ($users as $k => $v) {
            switch ($v['OutgoingSituation']) {
                case 0:
                    $wu += 1;
                    break;
                case 1:
                    $hubei += 1;
                    break;
                case 2:
                    $wenzhou += 1;
                    break;
                case 3:
                    $taizhou += 1;
                    break;
                case 4:
                    $other += 1;
                    break;
            }
            if ($v['IsMedicalObservation']) {
                $isNeedMedicalObservation += 1;
            }

            if ($v['OutgoingDesc']) {
                $outgoingDesc[$v['OutgoingDesc']] = ($outgoingDesc[$v['OutgoingDesc']] ?? 0) + 1;
            }

            if ($v['ContactSituation']) {
                $contactSituation['isContactSituation'] += 1;
            } else {
                $contactSituation['notContactSituation'] += 1;
            }
        }

        return [
            'BackEmpNumber' => $BackEmpNumber,
            'EmployeesNumber' => $EmployeesNumber,
            'isNeedMedicalObservation' => $isNeedMedicalObservation,
            'outgoingDesc' => $outgoingDesc,
            'contactSituation' => $contactSituation,
            'wu' => $wu,
            'hubei' => $hubei,
            'wenzhou' => $wenzhou,
            'taizhou' => $taizhou,
            'other' => $other,
        ];
    }

    public function register(Request $request)
    {
        $user = $request->user();
        $town_id = $user->town_id;
        $urls = [
            'summary'   => config('app.api_url.summary'),
            'medical'   => config('app.api_url.medical'),
            'touch'     => config('app.api_url.touch'),
            'back'      => config('app.api_url.back'),
        ];
        return view('statistic.registed', compact('town_id', 'urls'));
    }

    public function company(Request $request)
    {
        $user = $request->user();
        $town_id = $user->town_id;
        $industry = Industry::where('pid', '!=', 0)->select('id', 'name')->get();
        $enterprises = Company::orderBy('company.id', 'desc');
        $townType = TownType::where('TownID', 700011)
                ->select('TownID', 'TownName')
                ->pluck('TownName', 'TownID');
        $reportStatus = $request->get('reportStatus', '');
        $smClass = $request->get('industry', '');

        if ($reportStatus != '' && is_numeric($reportStatus) && $reportStatus < 2000000000){
            $enterprises->rightJoin('report', 'report.enterprise_id', '=', 'company.id')
                ->where('report.status', $reportStatus);
//            $request->session()->flash('reportStatus',$reportStatus);
        }

        if ($smClass) {
            $request->session()->flash('industry',$smClass);
            $enterprises->where('company.sm_class', $smClass);
        } else {
            $request->session()->flash('industry','');
        }

        $enterprises = $enterprises->get();

        return view('statistic.company', compact('industry', 'townType','enterprises'));


//        if ($town_id) {
//            //乡镇
//            $townType = TownType::where('TownID', $town_id)
//                ->select('TownID', 'TownName')
//                ->pluck('TownName', 'TownID');
//        } else {
//            //TODO 局
//            $townType = TownType::select('TownID', 'TownName')
//                ->pluck('TownName', 'TownID');
//            $industry->whereBetween('id',[$user->industry_id_min, $user->industry_id_max]);
//        }
//        return $industry = $industry->pluck('name', 'id');
//
//        $reportStatus = $request->get('reportStatus', '');
//        $ind = $request->get('industry', 0);
//        $town = $request->get('townType');
//        $EnterpriseName = $request->get('EnterpriseName');
//        $Address = $request->get('Address');
//        $request->session()->flash('reportStatus',$reportStatus);
//        $request->session()->flash('industry',$ind);
//        $request->session()->flash('townType',$town);
//        $request->session()->flash('EnterpriseName',$EnterpriseName);
//        $request->session()->flash('Address',$Address);
//        if (!$perPage = $request->get('per_page')){
//            $perPage = 10;
//        }
//        $start = $request->get('start');
//        $end = $request->get('end');
//
//        $request->session()->flash('per_page',$perPage);
//
//        if (!$town_id){
//            //局
//            if ($ind && is_numeric($ind) && $ind < 2000000000){
//                //其他
//                if ($ind == 600026){
//                    $enterprises =  Enterprise::whereRaw('("enterpriseInfoTable"."IndustryTableID" = 600026 or "enterpriseInfoTable"."IndustryTableID" is null)');
//                } else {
//                    $enterprises = Enterprise::where('enterpriseInfoTable.IndustryTableID', $ind);
//                }
//            } else {
//                $enterprises = Enterprise::whereRaw('("enterpriseInfoTable"."IndustryTableID" between '.$user->industry_id_min.' and '.$user->industry_id_max.' or "enterpriseInfoTable"."IndustryTableID" is null)');
//            }
//        } else {
//            //乡镇
//            $enterprises = Enterprise::where('enterpriseInfoTable.TownID', $town_id);
//            if ($ind && is_numeric($ind) && $ind < 2000000000){
//                if ($ind == 600026){
//                    $enterprises->whereRaw('("enterpriseInfoTable"."IndustryTableID" = '.$ind.' or "enterpriseInfoTable"."IndustryTableID" is null)');
//                } else {
//                    $enterprises->where('enterpriseInfoTable.IndustryTableID', $ind);
//                }
//            } elseif($ind == 0) {
//                // $enterprises->orWhereRaw('"enterpriseInfoTable"."IndustryTableID" is null');
//            }
//        }
//
//        if ($reportStatus && is_numeric($reportStatus) && $reportStatus < 2000000000){
//            $enterprises->rightJoin('report', 'report.enterprise_id', '=', 'enterpriseInfoTable.EnterpriseID')
//                ->where('report.status', $reportStatus);
//                info('reportStatus = '.$reportStatus);
//        }
//
//        if ($town && is_numeric($town) && $town < 1000000000){
//            $enterprises->where('enterpriseInfoTable.TownID', $town);
//        }
//
//        if ($EnterpriseName && !empty($EnterpriseName)){
//            $enterprises->where('enterpriseInfoTable.EnterpriseName', 'like', '%' . $EnterpriseName . '%');
//        }
//
//        if ($Address && !empty($Address)){
//            $Address = str_replace('；',';',$Address);
//            $arr = explode(";",$Address);
//            $enterprises->where(function ($enterprises) use ($arr) {
//                foreach ($arr as $key=>$value){
//                    if($key == 0){
//                        $enterprises->where('enterpriseInfoTable.Address', 'like', '%' . $value . '%');
//                    }else{
//                        $enterprises->orwhere('enterpriseInfoTable.Address', 'like', '%' . $value . '%');
//                    }
//                }
//            });
//        }
//
//        //开工时间
//        if ($end && $start) {
//            if ($start - $end > 0){
//                $mi = $start;
//                $start = $end;
//                $end = $mi;
//            }
//            $request->session()->flash('start',$start);
//            $request->session()->flash('end',$end);
//
//            $start = date('Y-m-d',substr($start,0,-3));
//            $end = date('Y-m-d',substr($end,0,-3));
//            $request->session()->flash('showStart',$start);
//            $request->session()->flash('showEnd',$end);
//
//            $enterprises->whereBetween('StartDate', [$start, $end]);
//        } else {
//            $request->session()->flash('start','');
//            $request->session()->flash('end','');
//            $request->session()->flash('showStart','');
//            $request->session()->flash('showEnd','');
//        }
//
//
//        $enterprises = $enterprises->with(['report:id,enterprise_id,status', 'town', 'industries:IndustryTableID,IndustryName'])
//            ->select('EnterpriseID','EnterpriseName', 'Address', 'EnterpriseScale', 'StartDate', 'District', 'IndustryTableID', 'TownID')
//            ->paginate($perPage);
//
//        return view('statistic.company', compact('industry','townType', 'enterprises'));
    }

    public function queryCompany(Request $request)
    {
        $user = $request->user();
        $town_id = $user->town_id;
        $reportStatus = $request->get('reportStatus', '');
        $industry = $request->get('industry', '');
        $townType = $request->get('townType');
        if (!$perPage = $request->get('per_page')){
            $perPage = 10;
        }

        if (!$town_id){
            //局
            $enterprises = Enterprise::whereBetween('enterpriseInfoTable.IndustryTableID',[$user->industry_id_min, $user->industry_id_max]);
        } else {
            //乡镇
            $enterprises = Enterprise::where('enterpriseInfoTable.TownID', $townType);
        }

        if ($reportStatus && is_numeric($reportStatus)){
            $enterprises->rightJoin('report', 'report.enterprise_id', '=', 'enterpriseInfoTable.EnterpriseID')
                ->where('report.status', $reportStatus);
        }

        //行业
        if ($industry && is_numeric($industry)){
            $enterprises->where('enterpriseInfoTable.IndustryTableID', $industry);
        }

        if ($townType && is_numeric($townType)){
            $enterprises->where('enterpriseInfoTable.TownID', $townType);
        }


        $enterprises = $enterprises->with(['report', 'town', 'industries:IndustryTableID,IndustryName'])
            ->select('EnterpriseName', 'EnterpriseScale', 'StartDate', 'District', 'IndustryTableID', 'TownID')
            ->paginate($perPage);

        return response()->json($enterprises);
    }

    public function industry(Request $request)
    {
        $user = $request->user();
        $town_id = $request->get('town_id');
        $industry_id_min = $request->get('industry_id_min', 600001);
        $industry_id_max = $request->get('industry_id_max', 600026);
        if ($user) {
            $town_id = $user->town_id;
            $industry_id_min = $user->industry_id_min;
            $industry_id_max = $user->industry_id_max;
        }
        if (!$town_id){
            //局
            $enterprises = Enterprise::whereRaw('("enterpriseInfoTable"."IndustryTableID" between '.$industry_id_min.' and '.$industry_id_max.' or "enterpriseInfoTable"."IndustryTableID" is null)');
        } else {
            //乡镇
            $enterprises = Enterprise::where('enterpriseInfoTable.TownID', $town_id);
        }

        $enterprises = $enterprises->selectRaw('count(*), "IndustryTableID"')
            ->orderBy('IndustryTableID','asc')
            ->groupBy('IndustryTableID')
            ->get();

        return response()->json($enterprises);
    }

    public function cockpit(Request $request)
    {
        /*$user = $request->user();
        $town_id = $request->get('town_id');
        $industry_id_min = $request->get('industry_id_min', 600001);
        $industry_id_max = $request->get('industry_id_max', 600026);
        if ($user) {
            $town_id = $user->town_id;
            $industry_id_min = $user->industry_id_min;
            $industry_id_max = $user->industry_id_max;
        }
        if (!$town_id){
            //局
            $enterprises = Enterprise::whereRaw('("enterpriseInfoTable"."IndustryTableID" between '.$industry_id_min.' and '.$industry_id_max.' or "enterpriseInfoTable"."IndustryTableID" is null)');
        } else {
            //乡镇
            $enterprises = Enterprise::where('enterpriseInfoTable.TownID', $town_id);
        }*/

        //1.计划开工时间
        $data['startdate'] = Enterprise::selectRaw('count(*) AS value, "StartDate" AS item')
            ->where('StartDate','<>',null)
            ->orderBy('StartDate','asc')
            ->groupBy('StartDate')
            ->get();
        //2.工作交通方式
        $data['worktraffic'] = Employee::selectRaw('count(*) AS value, "WorkTraffic" AS item')
            ->where('WorkTraffic','<>',null)
            ->orderBy('WorkTraffic','asc')
            ->groupBy('WorkTraffic')
            ->get();
        //3.近14天外出情况
        $data['outgoingsituation'] = Employee::selectRaw('count(*) AS value, "OutgoingSituation" AS item')
            ->where('OutgoingSituation','<>',null)
            ->orderBy('OutgoingSituation','asc')
            ->groupBy('OutgoingSituation')
            ->get();
        //4.企业所属局
        $data['govunitname'] = Enterprise::selectRaw('count(*) AS value, "GovUnitName" AS item')
            ->where('GovUnitName','<>',null)
            ->orderBy('GovUnitName','asc')
            ->groupBy('GovUnitName')
            ->get();
        //5.企业规模
        $data['enterprisescale'] = Enterprise::selectRaw('count(*) AS value, "EnterpriseScale" AS item')
            ->where('EnterpriseScale','<>',null)
            ->orderBy('EnterpriseScale','asc')
            ->groupBy('EnterpriseScale')
            ->get();
        //6.返甬交通方式
        $data['returntraffic'] = DB::select('select item,count(*) AS value from
(select (case when "ReturnTraffic" like \'%火车%\' then \'火车\' when "ReturnTraffic" like \'%飞机%\' then \'飞机\' when "ReturnTraffic" like \'%自驾%\' then \'自驾\' else \'其他\' end) as item from "employeeInfoTable" where "ReturnTraffic" is not null) as b group by item
');
        //7. 职工居住地
        $data['address'] = DB::select('select item,count(*) AS value from
    (select (case when "Address" like \'%厂区内宿舍%\' then \'厂区内宿舍\' when "Address" like \'%厂区外宿舍%\' then \'厂区外宿舍\'  else \'其他\' end) as item from "employeeInfoTable" where "Address" is not null) as b group by item');

        //8.是否租房
        $data['ishire'] = Employee::selectRaw('count(*) AS value, "IsHire" AS item')
            ->where('IsHire','<>',null)
            ->orderBy('IsHire','asc')
            ->groupBy('IsHire')
            ->get();

        return response()->json($data);
    }

}

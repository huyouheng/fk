<?php

namespace App\Http\Controllers;

use App\Company;
use App\Library\PWord;
use App\Report;
use App\Revision;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Enterprise;
use App\TownType;
use App\Industry;
use App\Http\Requests\CompanyRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->enterprise_id) {
            $company = Company::with(['files', 'report'])->findOrFail($request->user()->enterprise_id);
            if ($request->isMethod('POST')) {
                return response()->json(['status' => true, 'data' => $company]);
            }
            $industry = Industry::fetchData();
            $data = ['company' => $company, 'industry' => $industry];
            return view('company.index')->with($data);
        }
        return "Access deny";
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->enterprise_id != $id) {
            return response()->json(['status' => false]);
        }
        $field = $request->get('key');
        $value = $request->get('value');
        $company = Company::where('id', $id)->select($field)->first();
        if ($company[$field] != $value) {
            Company::where('id', $id)->update([$field => $value]);
            return response()->json(['status' => true]);
        }
    }

    public function export(Request $request)
    {
        $cid = $request->get('cid');
        if (!$company = Company::find($cid)) {
            return response()->json(['status' => false, 'data' => ''], 404);
        };
        self::getPath();
        $fileName = $cid . DIRECTORY_SEPARATOR.'申请表.docx';
        $path = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $fileName);
        $companyInfo = [
            [
                '单位名称（盖章）',
                $company->title,
                '纳税人识别号',
                $company->slug
            ],
            [
                '经办人',
                $company->operator,
                '联系电话',
                $company->phone
            ],
            [
                '所属行业',
                $company->sm_class,
                '复工时间',
                substr($company->start_at, 0, 10)
            ],
            [
                '2019年年末职工人数',
                $company->users,
                '2019年营业收入',
                $company->ye_shouru
            ],
            // 表格中间部分, 2019年末资产总额
            [
                '2019年末资产总额',
                $company->total_money,
                '',
                ''
            ],
            //2019年增值税
            [
                '2019年增值税',
                '2019年企业所得税',
                '合计',
                '开工后次月起两个月增值税合计'
            ],
            [
                $company->zz_shui,
                $company->sd_shui,
                $company->zz_sd_total,
                $company->two_zz
            ],
            //
            [
                '开户银行及户名',
                $company->bank_type,
                '银行账号',
                $company->bank_num,
            ]
        ];
        $data = [
            'year' => date('Y'),
            'month' => date('m'),
            'day' => date('d'),
            'company' => $companyInfo,
            'money' => $company->money,
            'path' => $path,
        ];
        PWord::fetchCompanyWord($data);
        return response()->json(['status' => true, 'data' => 'storage'.DIRECTORY_SEPARATOR.$fileName]);
    }

    public function apply(Request $request)
    {
        try {
            DB::beginTransaction();
            $town_id = $request->get('town', 700011);
            $user = $request->user();
            $attribute = [
                'enterprise_id' => $user->enterprise_id,
                'town_id' => $town_id,
                'version' => 1,
                'status' => 1,
                'comment' => '',
                'docs' => '',
                'report_at' => Carbon::now()
            ];
            $report = Report::where('enterprise_id', $user->enterprise_id)->first();

            if (!empty($report)) {
                $attribute['version'] = $report->version + 1;
                $report->update($attribute);
            } else {
                $report = Report::create($attribute);
            }
            $attribute['report_id'] = $report->id;
            Arr::forget($attribute, 'enterprise_id');
            Revision::create($attribute);
            DB::commit();
            return response()->json(['status' => true, 'msg' => '已提交申请', 'data'=> '']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'msg' => '提交申请失败', 'data'=> '']);
        }
    }
}

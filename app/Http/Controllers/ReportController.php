<?php

namespace App\Http\Controllers;

use App\Company;
use App\Enterprise;
use App\Industry;
use App\Report;
use App\TownType;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReportController extends Controller
{
    protected $towns;
    protected $industries;

    public function __construct()
    {
        $this->towns = TownType::all()->pluck('TownName', 'TownID');
        $this->industries = Industry::all()->pluck('IndustryName', 'IndustryTableID');
    }

    public function list(Request $request)
    {
        try {
            $user = $request->user();
            $yz_town_id = 700000;
            if (!$user->is_admin) {
                return "Access deny";
            }
//            if ($request->input('draw', 0) == 0) {
//                $request->offsetSet('draw', 1);
//            }
//            if ($request->input('page', -1) == -1) {
//                $request->offsetSet('page', 0);
//            }
 //           $page =  intval($request->input('page', 1));
//            if ($request->input('length', 0) == 0) {
//                $request->offsetSet('length', 10);
//            }
            // $length = $request->input('length', 10);
            // $request->offsetSet('start', ($page - 1) * $length);
            $model = Report::with('enterprise');
            if (!empty($user->town_id) && $user->town_id != $yz_town_id) {
                $model->where('town_id', $user->town_id);
            }
            if (!empty($user->industry_id_min) && !empty($user->industry_id_max)) {
                $model->industryBetween($user->industry_id_min, $user->industry_id_max);
            }
            $filter = $request->input();
            $this->getModel($model, $filter);

            return \datatables()
                ->eloquent($model)
                ->addColumn('EnterpriseName', function (Report $report) {
                    return $report->enterprise->EnterpriseName ?? '';
                })
                ->addColumn('Address', function (Report $report) {
                    return $report->enterprise->Address ?? '';
                })
                ->addColumn('EnterpriseID', function (Report $report) {
                    return $report->enterprise->EnterpriseID ?? '0';
                })
                ->addColumn('Industry', function (Report $report) {
                    return $report->enterprise->Industry ?? '';
                })
                ->addColumn('town', function (Report $report) {
                    return $report->town->TownName ?? '';
                })
                ->editColumn('version', function (Report $report) {
                    return $report->version ?? '';
                })
                ->editColumn('report_at', function (Report $report) {
                    return $report->report_at ?? '';
                })
                ->editColumn('status', function (Report $report) {
                    $ret = [1 => '审核中', 2 => '审核通过', 3 => '不通过'];
                    return $ret[$report->status];
                })
                ->toJson();
        } catch (\Exception $e) {
            return response(["success" => false, "message" => $e->getMessage()]);
        }
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $data = [];
        if (!empty($user->is_admin)) {
            $yz_town_id = 700000;
            $model = Report::with('enterprise');
            if (!empty($user->town_id) && $user->town_id != $yz_town_id) {
                $model->where('town_id', $user->town_id);
            }
            if (!empty($user->industry_id_min) && !empty($user->industry_id_max)) {
                $model->industryBetween($user->industry_id_min, $user->industry_id_max);
            }
            $filter = $request->input();
            $this->getModel($model, $filter);
            $count = $model->count();
            return $model->get();
            if ($count > 0) {
                $data[] = ['单位名称', '纳税人识别号', '复工时间', '联系人', '联系电话', '职工人数', '所属行业', '审核状态', '申请时间'];
                $model->chunk(100, function ($enterprises) use (&$data) {
                    foreach ($enterprises as $enterprise) {
                        $EnterpriseName = $enterprise->title ?? '';
                        $OrganizationCode = $enterprise->slug ?? '';
                        $start_at = $enterprise->start_at ?? '';
                        $operator = $enterprise->operator ?? '';
                        $phone = $enterprise->phone ?? '';
                        $sm_class = $enterprise->sm_class ?? '';
                        $users = $enterprise->users ?? '';
                        $status = '未申报';
                        if (!empty($enterprise->report->status) && $enterprise->report->status == 1) {
                            $status = '审核中';
                        }
                        if (!empty($enterprise->report->status) && $enterprise->report->status == 2) {
                            $status = '审核通过';
                        }
                        if (!empty($enterprise->report->status) && $enterprise->report->status == 3) {
                            $status = '未通过';
                        }
                        $ReportAt = $enterprise->report->report_at ?? '';
                        $data[] = [$EnterpriseName, $OrganizationCode, $start_at ,$operator , $phone, $users, $sm_class, $status, $ReportAt];
                    }
                });
                return $data;
                $spreadsheet = new Spreadsheet();
                // Set document properties
                $spreadsheet->getProperties()->setCreator('宝略科技')
                    ->setLastModifiedBy('宝略科技');

                $spreadsheet->setActiveSheetIndex(0);
                $spreadsheet->getActiveSheet()->fromArray($data);

                $style = [
                    'font' => [
                        'name' => '仿体',
                        'size' => 11
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_MEDIUM
                        ],
                    ],
                ];
                $spreadsheet->getActiveSheet()->getStyle('A1:P' . count($data))->applyFromArray($style);
                $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth("30");
                $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth("36");
                $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth("10");
                $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth("10");
                $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth("36");
                $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth("12");
                $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth("10");
                $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth("14");
                $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth("48");
                $filename = '企业申请列表.xlsx';
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '"');
                header('Cache-Control: max-age=0');

                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save('php://output');
            }
        }
    }


    private function getModel(&$model, $filter = [])
    {
        if (!empty($filter['status'])) {
            $model->where('status', $filter['status']);
        }
        if (!empty($filter['town'])) {
            $model->where('town_id', $filter['town']);
        }
        if (!empty($filter['industry'])) {
            $model->whereHas('enterprise', function ($query) use ($filter) {
                return $query->where('sm_class',  $filter['industry']);
            });
        }
        if (!empty($filter['enterprise'])) {
            $model->whereHas('enterprise', function ($query) use ($filter) {
                return $query->where('title', 'like', '%' . $filter['enterprise'] . '%');
            });
        }
        $model->orderByDesc('report_at');
    }


    public function exportlist(Request $request)
    {
        $user = $request->user();
        $data = [];
        if (!empty($user->is_admin)) {
            $model = Company::with('report:id,enterprise_id,status,report_at');

            $filter = $request->input();

            $ind = $filter['industry'];

            if ($ind && is_numeric($ind) && $ind < 2000000000){
                $model->where('company.sm_class', $ind);
            }

            $this->getModellist($model, $filter);
            $count = $model->count();
            if ($count > 0) {
                $data[] = ['单位名称', '纳税人识别号', '复工时间', '联系人', '联系电话', '职工人数', '所属行业', '审核状态', '申请时间'];
                $model->chunk(100, function ($enterprises) use (&$data) {
                    foreach ($enterprises as $enterprise) {
                        $EnterpriseName = $enterprise->title ?? '';
                        $OrganizationCode = $enterprise->slug ?? '';
                        $start_at = $enterprise->start_at ?? '';
                        $operator = $enterprise->operator ?? '';
                        $phone = $enterprise->phone ?? '';
                        $sm_class = $enterprise->sm_class ?? '';
                        $users = $enterprise->users ?? '';
                        $status = '未申报';
                        if (!empty($enterprise->report->status) && $enterprise->report->status == 1) {
                            $status = '审核中';
                        }
                        if (!empty($enterprise->report->status) && $enterprise->report->status == 2) {
                            $status = '审核通过';
                        }
                        if (!empty($enterprise->report->status) && $enterprise->report->status == 3) {
                            $status = '未通过';
                        }
                        $ReportAt = $enterprise->report->report_at ?? '';
                        $data[] = [$EnterpriseName, $OrganizationCode, $start_at ,$operator , $phone, $users, $sm_class, $status, $ReportAt];
                    }
                });
                $spreadsheet = new Spreadsheet();
                // Set document properties
                $spreadsheet->getProperties()->setCreator('宝略科技')
                    ->setLastModifiedBy('宝略科技');

                $spreadsheet->setActiveSheetIndex(0);
                $spreadsheet->getActiveSheet()->fromArray($data);

                $style = [
                    'font' => [
                        'name' => '仿体',
                        'size' => 11
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_MEDIUM
                        ],
                    ],
                ];
                $spreadsheet->getActiveSheet()->getStyle('A1:P' . count($data))->applyFromArray($style);
                $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth("30");
                $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth("36");
                $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth("10");
                $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth("10");
                $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth("36");
                $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth("12");
                $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth("10");
                $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth("14");
                $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth("48");
                $filename = '企业申请列表.xlsx';
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '"');
                header('Cache-Control: max-age=0');

                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save('php://output');
            }
        }
    }


    private function getModellist(&$model, $filter = [])
    {
        if (!empty($filter['status'])) {
            $model->whereHas('report', function ($query) use ($filter) {
                return $query->where('status', $filter['status']);
            });
        }
        if (!empty($filter['town'])) {
            $model->where('TownID', $filter['town']);
        }
        if (!empty($filter['industry'])) {
            $model->where('sm_class', $filter['industry']);
        }
        if (!empty($filter['enterprise'])) {
            $model->where('title', 'like', '%' . $filter['enterprise'] . '%');
        }
        $model->orderByDesc('created_at');
    }

}

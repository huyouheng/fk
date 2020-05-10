<?php

namespace App\Http\Controllers;

use App\CompanyAdd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->hasFile('fk-files')) {
            return response()->json(['status' => false], 404);
        }
        $key = $request->header('name');
        $cid = $request->header('cid');

        $path = self::getPath();
        $file = $request->file('fk-files');
        $ext = $file->getClientOriginalExtension();

        $storeName = uniqid().'.'.$ext;
        $path = $file->storeAs($path, $storeName, 'public');

        $store = [
            'date' => date('Y-m-d H:i:s'),
            'name' => $file->getClientOriginalName(),
            'url'   => '/storage/'.$path,
            'ext' => $file->getClientOriginalExtension(),
            'store' => $storeName,
        ];

        $companyAdd = CompanyAdd::where('enterprise_id' ,$cid)->first();
        if (!$companyAdd) {
            CompanyAdd::create(['enterprise_id'=> intval($cid), $key => json_encode([$store])]);
        } else {
            $old = json_decode($companyAdd[$key], true);
            $old[] = $store;
            $companyAdd->update([$key => json_encode($old)]);
        }
        return response()->json(['status' => true], 200);
    }


    public function remove(Request $request, $id)
    {
        $file = $request->get('file');
        $name = $request->get('name');
        $companyAdd = CompanyAdd::where('enterprise_id' ,$id)->select($name)->first();
        if ($companyAdd) {
            $total = json_decode($companyAdd[$name], true);
            $result = array_search($file, array_column($total, 'store'));
            if ($result !== false) {
               unset($total[$result]);
            }
            $new = [];
            foreach ($total as $item) {
                $new[] = $item;
            }
            CompanyAdd::where('enterprise_id' ,$id)->update([$name => json_encode($new)]);
            return response()->json(['status' => true], 200);
        }
        return response()->json(['status' => false], 404);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Company;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $message = [
            'title.required' => '单位名称不能为空',
            'name.required' => '账号不能为空',
            'password.required' => '密码不能为空',
            'slug.required' => '纳税人识别号不能为空',
            'captcha.required' => '验证码错误',
            'captcha.captcha' => '无效的验证码',
        ];

        return Validator::make($data, [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'captcha' => 'required|captcha',
        ], $message);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
//        $this->validator($request->all())->validate();
        //账号
        $name = $request->get('name');
        if (!$name || strlen($name) > 50) {
            admin_toastr('warning', '账号错误!');
            return back()->withInput();
        }
        $title = $request->get('title');
        if (!$title || strlen($title) > 50) {
            admin_toastr('warning', '单位名称错误!');
            return back()->withInput();
        }
        $slug = $request->get('slug');
        if (!$slug || strlen($slug) > 50) {
            admin_toastr('warning', '纳税人识别号错误!');
            return back()->withInput();
        }
        $password = $request->get('password');
        if (!$password || strlen($password) < 6) {
            admin_toastr('warning', '密码错误且不能小于6字符!');
            return back()->withInput();
        }
        $rules = ['captcha' => 'required|captcha'];
        $validator = validator()->make(request()->all(), $rules);
        if ($validator->fails()) {
            admin_toastr('warning', '验证码错误!');
            return back()->withInput();
        }
        if (Company::where('title', $title)->first()) {
            admin_toastr('warning', '该企业已经存在!');
            return back()->withInput();
        }

        if (User::where('name', $name)->first()) {
            admin_toastr('warning', '该用户已经存在!');
            return back()->withInput();
        }

        if (!$user = $this->create($request->all())) {
            admin_toastr('warning', '注册失败,请稍后再试!');
            return back()->withInput();
        }

        admin_toastr('success', $name);
        return back()->withInput();
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        try {
            DB::beginTransaction();
            $company = Company::create(['title' => $data['title'], 'slug' => $data['slug'], 'year' => 2019]);
            $user = User::create([
                'enterprise_id' => $company->id,
                'name' => $data['name'],
                'town_id' => 700011,
                'is_admin' => false,
                'password' => Hash::make($data['password']),
            ]);
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            info($e->getMessage());
            DB::rollBack();
            return null;
        }
    }
}

@extends('adminlte::page')

@section('title', '企业详情')

@section('content_header')
    <h5 class="m-0 text-dark">企业详情</h5>
@stop
@section('css')
@stop

@section('content')
    <form class="form-inline" style="width: 100%;" id="app">
        <div style="background: #ffffff;width: 100%;">
            <company-info companies="{{json_encode($company)}}" industries="{{json_encode($industry)}}"></company-info>
        </div>
        <div class="content-header" style="display: block;width: 100%;">
            <h5 class="m-0 text-dark">附件上传</h5>
        </div>
        <div style="background: #ffffff;width: 100%;">
            <company-upload title="*企业营业执照复印件" name="ye_copy" cid="{{$company->id}}"
                            files="{{$company->files->ye_copy ?? ''}}"></company-upload>
            <company-upload title="*2019年企业所得税年报" name="sd_report" cid="{{$company->id}}"
                            files="{{$company->files->sd_report ?? ''}}"></company-upload>
            <company-upload title="*2019年全年纳税证明" name="ns_prove" cid="{{$company->id}}"
                            files="{{$company->files->ns_prove ?? ''}}"></company-upload>
            <company-upload title="*增值税完税证明" name="zzs_prove" cid="{{$company->id}}"
                            files="{{$company->files->zzs_prove ?? ''}}"></company-upload>
            <company-upload title="企业公章收据" name="cp_card" cid="{{$company->id}}"
                            files="{{$company->files->cp_card ?? ''}}"></company-upload>

            <div class="col-md-12 text-center">
                <export company="{{json_encode($company)}}"></export>
            </div>
        </div>
    </form>
    <div style="height: 50px;"></div>
@stop
@section('js')
    <script src="/js/app.min.js"></script>
@stop

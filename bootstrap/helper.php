<?php

if (!function_exists('admin_toastr')) {
    /**
     * @param string $type
     * @param string $message
     */
    function admin_toastr($type = 'success', $message = '')
    {
        $toastr = new \Illuminate\Support\MessageBag(get_defined_vars());
        \Illuminate\Support\Facades\Session::flash('toastr', $toastr);
    }
}

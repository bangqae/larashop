<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $data = []; // Used in all controller, we use this instead compact('value')

    public function __construct()
    {
        $this->initAdminMenu();
    }

    private function initAdminMenu()
    {
        $this->data['currentAdminMenu'] = 'dashboard'; // Menu yang aktif pertama kali
        $this->data['currentAdminSubMenu'] = ''; // Submenu
    }

    protected function load_theme($view, $data = [])
    {
        return view('themes/'. env('APP_THEME') .'/'. $view, $data);
    }
}

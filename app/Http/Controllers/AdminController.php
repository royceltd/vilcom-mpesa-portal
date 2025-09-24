<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;

class AdminController extends Controller
{
    //

    public function index()
    {

        $payments = Payment::orderBy('id', 'DESC')->paginate(50);
        $page = 'Payments';
        return view('admin.payments', compact('page', 'payments'));
    }
}

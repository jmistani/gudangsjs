<?php
   
namespace App\Http\Controllers;
   
use Illuminate\Http\Request;
use App\Models\User;
use PDF;
   
class PDFController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function users()
    {
        $data = [
            'title' => 'Daftar User',
            'date' => date('m/d/Y'),
            'users' => User::with('roles')->get()
        ];
        

        $pdf = PDF::loadView('users.list', $data);
     
        return $pdf->download('user-list.pdf');
    }
}
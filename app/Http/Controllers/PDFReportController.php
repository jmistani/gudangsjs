<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Arr;
use PdfReport;

class PDFReportController extends Controller
{

public function userList(Request $request)
{
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $sortBy = $request->input('sort_by');

    $title = 'Daftar user'; // Report title

    $meta = [ // For displaying filters description on header
        'Registered on' => $fromDate . ' To ' . $toDate,
        'Sort By' => $sortBy
    ];

    $queryBuilder = User::with('roles');
   //  $queryBuilder = User::select(['name', 'balance', 'registered_at']) // Do some querying..
   //                      ->whereBetween('registered_at', [$fromDate, $toDate])
   //                      ->orderBy($sortBy);

   //  $columns = [ // Set Column to be displayed
   //      'Name' => 'name',
   //      'Username => username', // if no column_name specified, this will automatically seach for snake_case of column name (will be registered_at) column from query result
      //   'Total Balance' => 'balance',
      //   'Status' => function($result) { // You can do if statement or any action do you want inside this closure
      //       return ($result->balance > 100000) ? 'Rich Man' : 'Normal Guy';
      //   }
   //  ];

    $columns = [
      'Name' => function($user) {
          return $user->name;
      },
      'Username' => function($user) {
          return $user->username;
      },
      'Role' => function($user) {
          return implode(",",Arr::pluck($user->roles,'name'));
      },
  ];
      // Generate Report with flexibility to manipulate column class even manipulate column value (using Carbon, etc).
    return PdfReport::of($title, $meta, $queryBuilder, $columns)
                  //   ->editColumn('Registered At', [ // Change column class or manipulate its data for displaying to report
                  //       'displayAs' => function($result) {
                  //           return $result->registered_at->format('d M Y');
                  //       },
                  //       'class' => 'left'
                  //   ])
                  //   ->editColumns(['Total Balance', 'Status'], [ // Mass edit column
                  //       'class' => 'right bold'
                  //   ])
                  //   ->showTotal([ // Used to sum all value on specified column on the last table (except using groupBy method). 'point' is a type for displaying total with a thousand separator
                  //       'Total Balance' => 'point' // if you want to show dollar sign ($) then use 'Total Balance' => '$'
                  //   ])
                  //   ->limit(20) // Limit record to be showed
                    ->stream(); // other available method: store('path/to/file.pdf') to save to disk, download('filename') to download pdf / make() that will producing DomPDF / SnappyPdf instance so you could do any other DomPDF / snappyPdf method such as stream() or download()
   }
}

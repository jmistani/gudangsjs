<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\api\ValidationController;
use App\Models\User;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['namespace' => 'api'], function () {
    Route::get('validation/username/{username}', [ValidationController::class, 'usernameValidation']);
    Route::post('users/admin-save-user', [RolePermissionController::class, 'saveUser']);
  });

  
Route::group(['prefix' => 'auth'], function () {
  Route::get('me', [AuthController::class, 'me'])->name('me')->name('profile');
  Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('register', [AuthController::class, 'register'])->name('register');

    Route::group(['middleware' => 'auth:sanctum'], function() {
      Route::get('logout', [AuthController::class, 'logout'])->name('logout');
      Route::get('user', [AuthController::class, 'user'])->name('user');
      Route::get('refresh', [AuthController::class, 'refresh'])->name('refresh');
    });
});

Route::group(['namespace' => 'api', 'middleware' => 'auth:sanctum'], function() {
  Route::get('users/roles-permissions/{username}', [RolePermissionController::class, 'rolePermissions']);
  Route::get('users/pageData', [RolePermissionController::class, 'pageData']);
  Route::get('users', [AuthController::class, 'users'])->name('users');
  Route::get('/users/{id}', function ($id) {
    return User::findOrFail($id);
  });

  Route::get('accounts/all', 'AccountController@all');
  Route::get('accounts/listFormData', 'AccountController@listFormData');
  Route::resource('accounts', 'AccountController');

  Route::get('inventories/all', 'InventoryController@all');
  Route::get('inventoriesEdit/{id}', 'InventoryController@inventoriesEdit');
  Route::post('inventories/tag/add', 'InventoryController@addTag');
  Route::post('inventories/tag/remove', 'InventoryController@removeTag');
  Route::post('inventories/tag/clear', 'InventoryController@clearTag');
  Route::post('inventories/receive', 'InventoryController@receive');
  Route::post('inventories/consume', 'InventoryController@consume');
  Route::get('inventories/transaction', 'InventoryController@transactionIndex');
  Route::get('inventories/formReceiveData', 'InventoryController@formReceiveData');
  Route::get('inventories/listFormData', 'InventoryController@listFormData');
  Route::get('inventories/consumeFormData', 'InventoryController@consumeFormData');
  Route::post('inventories/{$id}/add-image', 'InventoryController@addImage');
  Route::get('inventories/receives', 'InventoryController@receives');
  Route::get('inventories/consumes', 'InventoryController@consumes');
  Route::get('inventories/receivesDB', 'InventoryController@receivesDB');
  Route::get('inventories/consumesDB', 'InventoryController@consumesDB');
  Route::get('inventories/recalculateStock/{id?}', 'InventoryController@recalculateStock');
  Route::get('inventories/assignTicket/{stockMutationID}/{ticketID?}', 'InventoryController@assignTicket');
  Route::get('inventories/removeTicket/{stockMutationID}', 'InventoryController@removeTicket');
//    Route::get('inventories/transaction', 'InventoryController@lastTransaction');
  Route::resource('inventories', 'InventoryController');

  Route::get('vendors/all', 'VendorController@all');
  Route::resource('vendors', 'VendorController');

  Route::get('journals/all', 'AccountController@all');
  Route::get('journals/fetchTickets', 'AccountController@fetchTickets');
  Route::get('journals/expenses', 'AccountController@expenses');
  Route::get('journals/tickets', 'AccountController@tickets');
  Route::resource('journals', 'AccountController');

  Route::get('inventory-receives/showPDF/{id}', 'ReceiveJournalController@showPDF');
  Route::get('inventory-receives/all', 'ReceiveJournalController@all');
  Route::resource('inventory-receives', 'ReceiveJournalController');
  Route::resource('receive-journal', 'ReceiveJournalController');

  Route::get('inventory-consumes/showPDF/{id}', 'ConsumeJournalController@showPDF');
  Route::get('inventory-consumes/all', 'ConsumeJournalController@all');
  Route::resource('inventory-consumes', 'ConsumeJournalController');
  Route::resource('consume-journal', 'ConsumeJournalController');

  Route::get('tickets/showPDF/{id}', 'TicketController@showPDF');
  Route::get('tickets/all', 'TicketController@all');
  Route::resource('tickets', 'TicketController');
  Route::get('tickets/close/{id}', 'TicketController@close');

  Route::get('stock-mutations/all', 'StockMutationController@all');
  Route::resource('stockmutations', 'StockMutationController');

  Route::get('inventory-groups/all', 'InventoryGroupController@all');
  Route::resource('inventory-groups', 'InventoryGroupController');
  
  Route::get('inventory-units/all', 'InventoryUnitController@all');
  Route::resource('inventory-units', 'InventoryUnitController');

  Route::get('inventory/report/inventory-balance/{date}/{bygroup}', 'InventoryController@inventoryBalanceReport');
  Route::get('inventory/report/inventory-mutation/{date_start}/{date_end}', 'InventoryController@inventoryMutationReport');
  Route::get('inventory/report/account-mutation/{account}/{date_start}/{date_end}', 'InventoryController@accountMutationReport');

    // validation
    Route::get('validation/account/kode/{code}', 'ValidationController@kodeAccount');
    Route::get('validation/barang/kode/{code}', 'ValidationController@kodeBarang');
    Route::get('validation/inventory-consume/kode/{code}', 'ValidationController@kodeInvConsume');
    Route::get('validation/inventory-receive/kode/{code}', 'ValidationController@kodeInvReceive');
    Route::get('validation/stokBarang/{id}/{qty}', 'ValidationController@stokBarang');
    Route::get('validation/ticket/name/{name}', 'ValidationController@uniqueTicketName');
    Route::get('validation/supplier/name/{name}', 'ValidationController@uniqueSupplierName');
    Route::get('validation/inventory-group/name/{name}', 'ValidationController@uniqueGroupBarang');
    Route::get('validation/inventory-unit/name/{name}', 'ValidationController@uniqueUnitBarang');
  
    Route::group(['prefix' => 'inventory-consume'], function () {
      Route::get('codeExists/{code}', 'ConsumeJournalController@codeExists');
    });
  });

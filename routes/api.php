<?php
use App\Models\User;
use App\Http\Controllers\ExpenseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Expenses
Route::get('/expense/{id}', [ExpenseApiController::class, 'index']);
Route::post('/expense', [ExpenseApiController::class, 'store']);
Route::get('/users',function(Request $request){
    return User::all();
});
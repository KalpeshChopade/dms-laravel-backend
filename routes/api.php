<?php

use App\Http\Controllers\v1\AdminController;
use App\Http\Controllers\v1\AgentController;
use App\Http\Controllers\v1\IncomeController;
use App\Http\Controllers\v1\UserController;
use App\Http\Controllers\OtpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix("/v1")->group(function () {

    /** Route to create admin */
    Route::get("/create-admin", [AdminController::class, "createAdmin"]);

    /** Route to login admin */
    Route::get("/admin-login", [AdminController::class, "loginAdmin"]);

    /** Route to invite user */
    Route::get("/invite-user", [AdminController::class, "inviteUser"]);

    /** Route to user registration */
    Route::post("/create-user", [UserController::class, "createUser"]);

    /** Route to user login */
    Route::get("/user-login", [UserController::class, "loginUser"]);

    /** Route to get user details */
    Route::get("/user-details", [UserController::class, "userDetails"]);

    /** Route to generate agents */
    Route::get("/generate-agents", [AgentController::class, "generateAgents"]);

    /** Route to get agents */
    Route::get("/fetch-agents", [AgentController::class, "fetchAgents"]);

    /** Route to add investment amount */
    Route::get("/add-investment", [IncomeController::class, "addInvestment"]);

    /** Route to get investment amount */
    Route::get("/get-investment", [IncomeController::class, "getInvestment"]);

    /** Route to calculate income1 */
    Route::get("/calculate-income1", [IncomeController::class, "calculateIncome1"]);

    /** Route to get income1 */
    Route::get("/get-income1", [IncomeController::class, "getInvestment"]);

    /** Route to calculate income2 */
    Route::get("/calculate-income2", [IncomeController::class, "calculateIncome2"]);

    /** Route to get income2 */
    Route::get("/get-income2", [IncomeController::class, "getIncome2"]);

    /** Route to calculate income3 */
    Route::get("/calculate-income3", [IncomeController::class, "calculateIncome3"]);

    /** Route to get income3 */
    Route::get("/get-income3", [IncomeController::class, "getIncome3"]);

    /** Route to calculate income4 */
    Route::get("/calculate-income4", [IncomeController::class, "calculateIncome4"]);

    /** Route to get income4 */
    Route::get("/get-income4", [IncomeController::class, "getIncome4"]);

    /** Route to calculate income5 */
    Route::get("/calculate-income5", [IncomeController::class, "calculateIncome5"]);

    /** Route to get income5 */
    Route::get("/get-income5", [IncomeController::class, "getIncome5"]);

    /** Route to createMasterBlueUser */
    Route::get("/create-master-blue-user", [AdminController::class, "createMasterBlueUser"]);

    /** Route to get my hierarchy */
    Route::get("/get-my-hierarchy", [UserController::class, "getMyHierarchyNew"]);

    /** Route to get users list */
    Route::get("/get-users-list", [UserController::class, "getUsersList"]);

    /** Route to verify users */
    Route::get("/verify-users", [UserController::class, "verifyUsers"]);

    /** Route to add bkb id */
    Route::get("/add-bkb-id", [UserController::class, "addBkbId"]);

    /** Route to calculateIncomeForAllUser */
    Route::get("/calculate-income-for-all-user", [IncomeController::class, "calculateIncomeForAllUser"]);
});

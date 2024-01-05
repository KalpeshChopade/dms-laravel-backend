<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Income1;
use App\Models\Income2;
use App\Models\Income3;
use App\Models\Income4;
use App\Models\Registration;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IncomeController extends Controller
{
    /** Function to addInvestment */
    public function addInvestment(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "user_id" => "required",
                "investment" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }

            // Check User Exists
            $user = User::where("id", $request->input("user_id"))->first();
            if (!$user) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "User does not exists"
                ]);
            }

            $income1 = Income1::where("user_id", $request->input("user_id"))->first();
            if (!$income1)
                $income1 = new Income1();
            $income1->user_id = $request->input("user_id");
            $income1->investment = $request->input("investment");
            $income1->save();

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => $income1,
                "message" => "Investment added successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to getInvestment */
    public function getInvestment(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "user_id" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }

            $income1 = Income1::where("user_id", $request->input("user_id"))->first();

            if (!$income1) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Investment not found"
                ]);
            } else {
                return response()->json([
                    "status" => "success",
                    "status_code" => 200,
                    "data" => $income1,
                    "message" => "Investment fetched successfully"
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to calculateIncome1 */
    public function calculateIncome1(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "user_id" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }

            $income1 = Income1::where("user_id", $request->input("user_id"))->first();

            if (!$income1) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Investment not found"
                ]);
            }

            if ($income1->income1 > 0) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $income1,
                    "message" => "Income1 already calculated"
                ]);
            }

            // Generate Random Percentage between 1 to 15
            $profit_percentage = rand(1, 15);

            $income1->profit_percentage = $profit_percentage;
            $income1->income1 = $income1->investment * $profit_percentage / 100;
            $income1->save();

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => $income1,
                "message" => "Income1 calculated successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to calculateIncome2 */
    public function calculateIncome2(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "user_id" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }

            $income1 = Income1::where("user_id", $request->input("user_id"))->first();

            if (!$income1) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Income1 not found"
                ]);
            }

            $registration = Registration::where("user_id", $request->input("user_id"))->first();
            if (!$registration) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "User not registered"
                ]);
            }

            $blue_user_id = $registration->blue_user_id;

            $blue_income1 = Income1::where("user_id", $blue_user_id)->first();
            if (!$blue_income1) {
                $blue_income1 = new Income1();
                $blue_income1->user_id = $blue_user_id;
                $blue_income1->investment = 0;
                $blue_income1->income1 = 0;
                $blue_income1->profit_percentage = 0;
                $blue_income1->save();
            }

            $saffron_income = $income1->income1 * 2 / 100;
            $gold_income = $income1->income1 * 4 / 100;

            $income2 = Income2::where("user_id", $request->input("user_id"))->first();

            if (!$income2)
                $income2 = new Income2();

            $income2->user_id = $request->input("user_id");
            $income2->income2 = ($blue_income1->income1 * 6 / 100) + $saffron_income + $gold_income;
            $income2->blue_income1 = $blue_income1->income1 * 6 / 100;
            $income2->saffron_income = $saffron_income;
            $income2->gold_income = $gold_income;
            $income2->save();

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => $income2,
                "message" => "Income2 calculated successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to getIncome2 */
    public function getIncome2(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "user_id" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }

            $income2 = Income2::where("user_id", $request->input("user_id"))->first();

            if (!$income2) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Income2 not found"
                ]);
            } else {
                return response()->json([
                    "status" => "success",
                    "status_code" => 200,
                    "data" => $income2,
                    "message" => "Income2 fetched successfully"
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to calculateIncome3 */
    public function calculateIncome3(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "user_id" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }

            $my_leads = Registration::where("blue_user_id", $request->input("user_id"))
                ->where("isActive", 1)
                ->where("isVerified", 1)
                ->get();

            $income3_lead_1_income = 0;
            $income3_lead_2_income = 0;
            $income3_lead_3_income = 0;
            $income3_lead_1_id = 0;
            $income3_lead_2_id = 0;
            $income3_lead_3_id = 0;

            $i = 1;

            foreach ($my_leads as $lead) {
                $income1 = Income1::where("user_id", $lead->user_id)->first();
                if ($income1) {
                    if ($i == 1) {
                        $income3_lead_1_income = $income1->income1 * 10 / 100;
                        $income3_lead_1_id = $lead->user_id;
                    } else if ($i == 2) {
                        $income3_lead_2_income = $income1->income1 * 10 / 100;
                        $income3_lead_2_id = $lead->user_id;
                    } else if ($i == 3) {
                        $income3_lead_3_income = $income1->income1 * 10 / 100;
                        $income3_lead_3_id = $lead->user_id;
                    }
                }
            }

            $income3 = Income3::where("user_id", $request->input("user_id"))->first();

            if (!$income3)
                $income3 = new Income3();

            $income3->user_id = $request->input("user_id");
            $income3->income3 = $income3_lead_1_income + $income3_lead_2_income + $income3_lead_3_income;
            $income3->lead_1_id = $income3_lead_1_id;
            $income3->lead_2_id = $income3_lead_2_id;
            $income3->lead_3_id = $income3_lead_3_id;
            $income3->lead_1_income = $income3_lead_1_income;
            $income3->lead_2_income = $income3_lead_2_income;
            $income3->lead_3_income = $income3_lead_3_income;
            $income3->save();

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => $income3,
                "message" => "Income3 calculated successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to getIncome3 */
    public function getIncome3(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "user_id" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }

            $income3 = Income3::where("user_id", $request->input("user_id"))->first();

            if (!$income3) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Income3 not found"
                ]);
            } else {
                return response()->json([
                    "status" => "success",
                    "status_code" => 200,
                    "data" => $income3,
                    "message" => "Income3 fetched successfully"
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to calculateIncome4 */
    public function calculateIncome4(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "user_id" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }

            $my_leads = Registration::where("blue_user_id", $request->input("user_id"))
                ->where("isActive", 1)
                ->where("isVerified", 1)
                ->get();

            $income4_lead_1_income = 0;
            $income4_lead_2_income = 0;
            $income4_lead_3_income = 0;
            $income4_lead_1_id = 0;
            $income4_lead_2_id = 0;
            $income4_lead_3_id = 0;

            $i = 1;
            foreach ($my_leads as $lead) {
                $income1 = Income1::where("user_id", $lead->user_id)->first();
                if ($income1) {
                    if ($i == 1) {
                        $income4_lead_1_income = $income1->income1 * 10 / 100;
                        $income4_lead_1_id = $lead->user_id;
                    } else if ($i == 2) {
                        $income4_lead_2_income = $income1->income1 * 10 / 100;
                        $income4_lead_2_id = $lead->user_id;
                    } else if ($i == 3) {
                        $income4_lead_3_income = $income1->income1 * 10 / 100;
                        $income4_lead_3_id = $lead->user_id;
                    }
                }
            }

            $income4 = Income4::where("user_id", $request->input("user_id"))->first();

            if (!$income4)
                $income4 = new Income4();

            $income4->user_id = $request->input("user_id");
            $income4->income4 = $income4_lead_1_income + $income4_lead_2_income + $income4_lead_3_income;
            $income4->lead_1_id = $income4_lead_1_id;
            $income4->lead_2_id = $income4_lead_2_id;
            $income4->lead_3_id = $income4_lead_3_id;
            $income4->lead_1_income = $income4_lead_1_income;
            $income4->lead_2_income = $income4_lead_2_income;
            $income4->lead_3_income = $income4_lead_3_income;
            $income4->save();

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => $income4,
                "message" => "Income4 calculated successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to getIncome4 */
    public function getIncome4(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "user_id" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }

            $income4 = Income4::where("user_id", $request->input("user_id"))->first();

            if (!$income4) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Income4 not found"
                ]);
            } else {
                return response()->json([
                    "status" => "success",
                    "status_code" => 200,
                    "data" => $income4,
                    "message" => "Income4 fetched successfully"
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }
}

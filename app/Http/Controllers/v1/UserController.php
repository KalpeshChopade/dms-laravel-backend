<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Income1;
use App\Models\Registration;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /** Function to createUser */
    public function createUser(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "name" => "required",
                "link" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }

            // Check Link Exists in Registration Table or not
            $registration = Registration::where("link", $request->input("link"))->where("isActive", 0)->first();
            if (!$registration) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Link does not exists"
                ]);
            }

            $blue_user_id = $registration->blue_user_id;
            $check_blue_user_leads = Registration::where("blue_user_id", $blue_user_id)->where("isActive", 1)->where("isVerified", 1)->count();
            if ($check_blue_user_leads >= 3) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Blue User already has 3 leads"
                ]);
            }

            // profile_image upload
            $profile_image = "";
            if ($request->hasFile("profile_image")) {
                $file = $request->file("profile_image");
                $profile_image = time() . "_" . $file->getClientOriginalName();
                $file->move(public_path("users/profile_images"), $profile_image);
            }


            $user = new User();
            $user->name = $request->input("name");
            $user->profile_image = $profile_image;
            $user->save();

            $registration->user_id = $user->id;
            $registration->link = $request->input("link");
            $registration->isActive = 1;
            $registration->isVerified = 1;
            $registration->save();

            $gold_agent_id = $registration->gold_user_id;
            $gold_agent = Agent::where("id", $gold_agent_id)->first();
            if ($gold_agent) {
                $gold_agent->isConsented = 1;
                $gold_agent->total_leads = $gold_agent->total_leads + 1;
                $gold_agent->registration_id = $registration->id;
                $gold_agent->save();
            }

            $saffron_agent_id = $registration->saffron_user_id;
            $saffron_agent = Agent::where("id", $saffron_agent_id)->first();
            if ($saffron_agent) {
                $saffron_agent->isConsented = 1;
                $saffron_agent->total_leads = $saffron_agent->total_leads + 1;
                $saffron_agent->registration_id = $registration->id;
                $saffron_agent->save();
            }


            $income1 = new Income1();
            $income1->user_id = $user->id;
            $income1->investment = 0;
            $income1->income1 = 0;
            $income1->profit_percentage = 0;
            $income1->save();


            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => [
                    "user" => $user,
                    "registration" => $registration,
                    "income1" => $income1
                ],
                "message" => "User created successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to loginUser */
    public function loginUser(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "email_id" => "required",
                "mobile_number" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }

            $user = User::where("email_id", $request->input("email_id"))->first();
            if (!$user) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Email Id does not exists"
                ]);
            }

            $user = User::where("mobile_number", $request->input("mobile_number"))->first();
            if (!$user) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Mobile Number does not exists"
                ]);
            }

            $registration = Registration::where("user_id", $user->id)->first();
            if (!$registration) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "User not registered"
                ]);
            }

            $income1 = Income1::where("user_id", $user->id)->first();

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => [
                    "user" => $user,
                    "registration" => $registration,
                    "income1" => $income1
                ],
                "message" => "User logged in successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to fetch userDetails */
    public function userDetails(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "user_id" => "required",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
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

            $user = User::where("id", $request->input("user_id"))->first();
            if (!$user) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "User not found"
                ]);
            }

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => [
                    "user" => $user,
                    "registration" => $registration
                ],
                "message" => "User details fetched successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to getMyHierarchy */
    public function getMyHierarchy(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "user_id" => "required",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }


            $my_hierarchy = self::fetchLead($request->input("user_id"), []);


            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => $my_hierarchy,
                "message" => "My Hierarchy fetched successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to fetchLead */
    public static function fetchLead($user_id, $arr)
    {
        try {

            $registration = Registration::where("blue_user_id", $user_id)->where("isActive", 1)->where("isVerified", 1)->get();

            if (count($registration) > 0) {
                foreach ($registration as $key => $value) {
                    $value->user_details = User::where("id", $value->user_id)->first();
                    $arr[$user_id][$value->user_id] = $value;
                    $arr = self::fetchLead($value->user_id, $arr);
                }
            }

            return $arr;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}

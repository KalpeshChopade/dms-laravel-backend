<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Agent;
use App\Models\Registration;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /** Function to createAdmin */
    public function createAdmin(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "name" => "required",
                "username" => "required",
                "password" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }

            $admin = new Admin();
            $admin->name = $request->input("name");
            $admin->username = $request->input("username");
            $admin->password = Hash::make($request->input("password"));
            $admin->save();

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => $admin,
                "message" => "Admin created successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to loginAdmin */
    public function loginAdmin(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "username" => "required",
                "password" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }

            $admin = Admin::where("username", $request->input("username"))->first();

            if (!$admin) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Admin not found"
                ]);
            }

            if (!Hash::check($request->input("password"), $admin->password)) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Invalid credentials"
                ]);
            }

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => $admin,
                "message" => "Admin logged in successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to inviteUser */
    public function inviteUser(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "blue_id" => "required",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }

            $check_blue_agent_lead = Registration::where("blue_user_id", $request->input("blue_id"))->where("isActive", 1)->count();

            if ($check_blue_agent_lead == 3) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Blue agent has reached maximum limit"
                ]);
            }

            $saffron_agent = Agent::where("category", "saffron")->where("isConsented", 0)->get();
            if (count($saffron_agent) == 0) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Saffron agent not found"
                ]);
            }

            $saffron_agent_id = $saffron_agent[0]->id;

            $gold_agent = Agent::where("category", "gold")->where("isConsented", 0)->get();
            if (count($gold_agent) == 0) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Gold agent not found"
                ]);
            }

            $gold_agent_id = $gold_agent[0]->id;

            $blue_code = $this->generateRandomCode();
            $saffron_code = $this->generateRandomCode();
            $gold_code = $this->generateRandomCode();

            $link_generated = false;
            while (!$link_generated) {
                $link = $this->generateLink($blue_code, $saffron_code, $gold_code);
                $registration = Registration::where("link", $link)->first();
                if (!$registration && $link !== "") {
                    $link_generated = true;
                }
            }

            $registration = new Registration();
            $registration->link = $link;
            $registration->blue_user_id = $request->input("blue_id");
            $registration->blue_code = $blue_code;
            $registration->saffron_user_id = $saffron_agent_id;
            $registration->saffron_code = $saffron_code;
            $registration->gold_user_id = $gold_agent_id;
            $registration->gold_code = $gold_code;
            $registration->save();

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => $registration,
                "message" => "User invited successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to generateRandomCode */
    private function generateRandomCode()
    {
        try {
            $code = "";
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            for ($i = 0; $i < 12; $i++) {
                $code .= $characters[rand(0, $charactersLength - 1)];
            }
            return $code;
        } catch (Exception $e) {
            return "";
        }
    }

    /** Function to getAgents */
    private function generateLink($blue_code, $saffron_code, $gold_code)
    {
        try {
            $code = "";
            $characters = $blue_code . $saffron_code . $gold_code;
            $charactersLength = strlen($characters);
            for ($i = 0; $i < 12; $i++) {
                $code .= $characters[rand(0, $charactersLength - 1)];
            }
            return $code;
        } catch (Exception $e) {
            return "";
        }
    }

    /** Function to create Master Blue User */
    public function createMasterBlueUser()
    {
        try {

            $saffron_agent = Agent::where("category", "saffron")->where("isConsented", 0)->get();
            if (count($saffron_agent) == 0) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Saffron agent not found"
                ]);
            }

            $saffron_agent_id = $saffron_agent[0]->id;

            $gold_agent = Agent::where("category", "gold")->where("isConsented", 0)->get();
            if (count($gold_agent) == 0) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Gold agent not found"
                ]);
            }

            $gold_agent_id = $gold_agent[0]->id;

            $blue_code = $this->generateRandomCode();
            $saffron_code = $this->generateRandomCode();
            $gold_code = $this->generateRandomCode();

            $link_generated = false;
            while (!$link_generated) {
                $link = $this->generateLink($blue_code, $saffron_code, $gold_code);
                $registration = Registration::where("link", $link)->first();
                if (!$registration && $link !== "") {
                    $link_generated = true;
                }
            }


            $blue_user = new User();
            $blue_user->name = "Master Blue User";
            $blue_user->profile_image = "default_image.png";
            $blue_user->save();

            $registration = new Registration();
            $registration->link = $link;
            $registration->blue_user_id = $blue_user->id;
            $registration->blue_code = $blue_code;
            $registration->saffron_user_id = $saffron_agent_id;
            $registration->saffron_code = $saffron_code;
            $registration->gold_user_id = $gold_agent_id;
            $registration->gold_code = $gold_code;
            $registration->save();

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => $blue_user,
                "link" => $link,
                "message" => "Master Blue User created successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }
}

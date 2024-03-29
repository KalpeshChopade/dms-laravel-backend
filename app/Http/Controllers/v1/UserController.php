<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Income1;
use App\Models\Registration;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /** Function to createUser */
    public function createUser(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "bkb_id" => "required",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }

            $check_bkb_user = User::where("bkb_id", $request->input("bkb_id"))->first();
            if ($check_bkb_user) {
                return response()->json([
                    "status" => "success",
                    "status_code" => 200,
                    "data" => $check_bkb_user,
                    "message" => "Login Successfull",
                ]);
            }

            if (!$request->has("link")) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Invitation Link is required to login"
                ]);
            }

            // Check Link Exists in Registration Table or not
            $registration = Registration::where("link", $request->input("link"))->where("isActive", 0)->first();
            if (!$registration) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Invitation Expired"
                ]);
            }

            $blue_user_id = $registration->blue_user_id;
            $check_blue_user_leads = Registration::where("blue_user_id", $blue_user_id)->where("isActive", 1)->where("isVerified", 1)->count();
            if ($check_blue_user_leads >= 3) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "Invitation Expired"
                ]);
            }

            // profile_image upload
            $profile_image = "";
            if ($request->hasFile("profile_image")) {
                $file = $request->file("profile_image");
                $profile_image = time() . "_" . $file->getClientOriginalName();
                $file->move(public_path("users/profile_images"), $profile_image);
            }

            // return response()->json([
            //     "status" => "success",
            //     "status_code" => 200,
            //     "data" => [
            //         "isFile" => $request->hasFile("profile_image"),
            //         "profile_image" => $profile_image
            //     ]
            // ]);


            $user = new User();
            $user->bkb_id = $request->input("bkb_id");
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

            // Generate Random Percentage between 1 to 15
            $profit_percentage = rand(1, 15);
            $investment = rand(100000, 500000);

            $profit_percentage = $profit_percentage;
            $income1 = $investment * $profit_percentage / 100;

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


            // $my_hierarchy = self::fetchLead($request->input("user_id"), []);

            $self = User::where("id", $request->input("user_id"))->first();

            $registration = Registration::where("user_id", $request->input("user_id"))->where("isActive", 1)->where("isVerified", 1)->first();

            if (!$registration) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "message" => "User not registered"
                ]);
            }

            $parent_blue_user_id = $registration->blue_user_id;
            $parent_gold_user_id = $registration->gold_user_id;
            $parent_saffron_user_id = $registration->saffron_user_id;

            $parent["blue"] = User::where("id", $parent_blue_user_id)->first();
            $parent["gold"] = Agent::where("id", $parent_gold_user_id)->first();
            $parent["saffron"] = Agent::where("id", $parent_saffron_user_id)->first();

            $registration = Registration::where("blue_user_id", $request->input("user_id"))->where("isActive", 1)->where("isVerified", 1)->get();

            $count = 0;
            $children = [];
            foreach ($registration as $key => $value) {
                $user = User::where("id", $value->user_id)->first();
                $gold_agent = Agent::where("id", $value->gold_user_id)->first();
                $saffron_agent = Agent::where("id", $value->saffron_user_id)->first();
                $arr["user"] = $user;
                $arr["gold_agent"] = $gold_agent;
                $arr["saffron_agent"] = $saffron_agent;
                $children[] = $arr;
            }

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => [
                    "self" => $self,
                    "parent" => $parent,
                    "children" => $children
                ],
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

    /** Function to getUsersList */
    public function getUsersList()
    {
        try {
            $users = User::join("registrations", "registrations.user_id", "=", "users.id")->get();

            foreach ($users as $key => $value) {
                $value->blue_agent = User::where("id", $value->blue_user_id)->first();
                $value->gold_agent = Agent::where("id", $value->gold_user_id)->first();
                $value->saffron_agent = Agent::where("id", $value->saffron_user_id)->first();
            }

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => $users,
                "message" => "Users list fetched successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to verifyUsers */
    public function verifyUsers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "user_id" => "required",
                "isVerified" => "required",
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

            $registration->isVerified = $request->input("isVerified");
            $registration->save();

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => $registration,
                "message" => "User verification status updated successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to addBkbId */
    public function addBkbId(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "user_id" => "required",
                "bkb_id" => "required",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
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

            $user->bkb_id = $request->input("bkb_id");
            $user->save();

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => $user,
                "message" => "BKB Id added successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to getMyHierarchyNew */
    public function getMyHierarchyNew(Request $request)
    {
        $user_id = $request->input("user_id");

        $hierarchy = $this->getHierarchy($user_id, 0, 0);

        $registration = Registration::where("user_id", $user_id)->where("isDeleted", 0)->first();
        // $blue_user = User::select('id','name')->where("id", $registration->blue_user_id)->first();
        // $gold_agent = Agent::select('id','name')->where("id", $registration->gold_user_id)->first();
        // $saffron_agent = Agent::select('id','name')->where("id", $registration->saffron_user_id)->first();
        $blue_user = [];
        $gold_agent = [];
        $saffron_agent = [];

        $data = [
            'user_id' => $user_id,
            'level' => 0,
            'self' => $registration,
            'parent' => [
                'blue_user' => $blue_user,
                'gold_agent' => $gold_agent,
                'saffron_agent' => $saffron_agent
            ],
            'children' => $hierarchy
        ];

        return response()->json([
            "status" => "success",
            "status_code" => 200,
            "data" => $data,
            "message" => "My Hierarchy fetched successfully"
        ]);
    }

    private function getHierarchyPerfect($user_id, $level)
    {
        $leads = DB::table('registrations')
            ->select('id', 'user_id', 'blue_user_id', 'gold_user_id', 'saffron_user_id')
            ->where('blue_user_id', $user_id)
            ->where('isActive', 1)
            ->get();

        $hierarchy = [];

        foreach ($leads as $lead) {
            $subHierarchy = $this->getHierarchy($lead->user_id, $level + 1);
            $blue_user = User::select('id', 'name')->where("id", $lead->blue_user_id)->first();
            $gold_agent = Agent::select('id', 'name')->where("id", $lead->gold_user_id)->first();
            $saffron_agent = Agent::select('id', 'name')->where("id", $lead->saffron_user_id)->first();
            $hierarchy[] = [
                'user_id' => $lead->user_id,
                'level' => $level,
                // 'self' => $lead,
                'parent' => [
                    'blue_user' => $blue_user,
                    'gold_agent' => $gold_agent,
                    'saffron_agent' => $saffron_agent
                ],
                'children' => $subHierarchy
            ];
        }

        return $hierarchy;
    }

    private function getHierarchy2($user_id, $level)
    {
        $leads = DB::table('registrations')
            ->select('id', 'user_id', 'blue_user_id', 'gold_user_id', 'saffron_user_id')
            ->where('blue_user_id', $user_id)
            ->where('isActive', 1)
            ->get();

        $hierarchy = [];

        foreach ($leads as $lead) {
            $subHierarchy = $this->getHierarchy($lead->user_id, $level + 1);
            $blue_user = User::select('id', 'name')->where("id", $lead->blue_user_id)->first();
            $gold_agent = Agent::select('id', 'name')->where("id", $lead->gold_user_id)->first();
            $saffron_agent = Agent::select('id', 'name')->where("id", $lead->saffron_user_id)->first();

            // Check if there are fewer than 3 children and fulfill the requirement
            while (count($subHierarchy) < 3) {
                $subHierarchy[] = [
                    'user_id' => 0,
                    'level' => 0,
                    'parent' => [
                        'blue_user' => ['id' => 0, 'name' => ''],
                        'gold_agent' => ['id' => 0, 'name' => ''],
                        'saffron_agent' => ['id' => 0, 'name' => '']
                    ],
                    'children' => []
                ];
            }

            // Add blue_user_id in every child if its id is 0
            foreach ($subHierarchy as &$child) {
                if ($child['parent']['blue_user']['id'] == 0) {
                    $child['parent']['blue_user']['id'] = $lead->blue_user_id;
                }
            }

            $hierarchy[] = [
                'user_id' => $lead->user_id,
                'level' => $level,
                'parent' => [
                    'blue_user' => $blue_user,
                    'gold_agent' => $gold_agent,
                    'saffron_agent' => $saffron_agent
                ],
                'children' => $subHierarchy
            ];
        }

        return $hierarchy;
    }

    private function getHierarchy($user_id, $level, $parentBlueUserId = 0)
    {
        $leads = DB::table('registrations')
            ->select('id', 'user_id', 'blue_user_id', 'gold_user_id', 'saffron_user_id')
            ->where('blue_user_id', $user_id)
            ->where('isActive', 1)
            ->get();

        $hierarchy = [];

        if (count($leads) == 0) {
            $subHierarchy[] = [
                'user_id' => 0,
                'level' => $level + 1,
                'parent' => [
                    'blue_user' => ['id' => $user_id, 'name' => ''],
                    'gold_agent' => ['id' => 0, 'name' => ''],
                    'saffron_agent' => ['id' => 0, 'name' => '']
                ],
                'children' => []
            ];
            $subHierarchy[] = [
                'user_id' => 0,
                'level' => $level + 1,
                'parent' => [
                    'blue_user' => ['id' => $user_id, 'name' => ''],
                    'gold_agent' => ['id' => 0, 'name' => ''],
                    'saffron_agent' => ['id' => 0, 'name' => '']
                ],
                'children' => []
            ];
            $subHierarchy[] = [
                'user_id' => 0,
                'level' => $level + 1,
                'parent' => [
                    'blue_user' => ['id' => $user_id, 'name' => ''],
                    'gold_agent' => ['id' => 0, 'name' => ''],
                    'saffron_agent' => ['id' => 0, 'name' => '']
                ],
                'children' => []
            ];

            return $subHierarchy;
        }

        foreach ($leads as $lead) {
            $subHierarchy = $this->getHierarchy($lead->user_id, $level + 1, $lead->blue_user_id);
            $blue_user = User::select('id', 'name')->where("id", $lead->blue_user_id)->first();
            $gold_agent = Agent::select('id', 'name')->where("id", $lead->gold_user_id)->first();
            $saffron_agent = Agent::select('id', 'name')->where("id", $lead->saffron_user_id)->first();

            // Check if there are fewer than 3 children and fulfill the requirement
            while (count($subHierarchy) < 3) {
                $subHierarchy[] = [
                    'user_id' => 0,
                    'level' => $level,
                    'parent' => [
                        'blue_user' => ['id' => $lead->blue_user_id, 'name' => ''],
                        'gold_agent' => ['id' => 0, 'name' => ''],
                        'saffron_agent' => ['id' => 0, 'name' => '']
                    ],
                    'children' => []
                ];
            }

            $hierarchy[] = [
                'user_id' => $lead->user_id,
                'level' => $level + 1,
                'parent' => [
                    'blue_user' => $blue_user,
                    'gold_agent' => $gold_agent,
                    'saffron_agent' => $saffron_agent
                ],
                'children' => $subHierarchy
            ];
        }

        return $hierarchy;
    }
}

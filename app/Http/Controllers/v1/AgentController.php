<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgentController extends Controller
{
    /** Function to generateAgents */
    public function generateAgents(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "count" => "required",
                "category" => "required",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "failure",
                    "status_code" => 400,
                    "data" => $validator->errors(),
                    "message" => "Bad Request"
                ]);
            }

            $count = $request->input("count");
            $agents = [];
            for ($i = 0; $i < $count; $i++) {
                $agent = new Agent();
                $agent->name = "Agent " . $i + 1;
                $agent->category = $request->input("category");
                $agent->save();
                array_push($agents, $agent);
            }

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => $agents,
                "message" => "Agents generated successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    /** Function to fetchAgents */
    public function fetchAgents(Request $request)
    {
        // try {
            $validator = Validator::make($request->all(), [
                "category" => "required",
            ]);
            if ($validator->fails()) {
                $agents = Agent::where("isConsented", 0)->get();

                return response()->json([
                    "status" => "success",
                    "status_code" => 200,
                    "data" => $agents,
                    "message" => "Agents fetched successfully"
                ]);
            }

            $agents = Agent::where("category", $request->input("category"))->where("isConsented", 0)->get();

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => $agents,
                "message" => "Agents fetched successfully"
            ]);
        // } catch (Exception $e) {
        //     return response()->json([
        //         "status" => "error",
        //         "status_code" => 500,
        //         "message" => $e->getMessage()
        //     ]);
        // }
    }
}

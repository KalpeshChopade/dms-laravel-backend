<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Income1;

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

    // filter agents base on there category
    public function filterAgentIncome(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "days" => "required",
            ]);
            if ($validator->fails()) {

                $agent = Agent::where("isConsented", 0)->get();

                // Filter agents base on date
                if ($request->input("date")) {
                    $agent = $agent->where("days", $request->input("date"));
                }

                return response()->json([
                    "status" => "success",
                    "status_code" => 200,
                    "data" => $agent,
                    "message" => "Agents fetched successfully"
                ]);
            }

            $agents = Income1::where("isDeleted", 0)->get();

            // filter agents base on date
            if ($request->input("date")) {
                $agents = $agents->where("date", $request->input("date"));
            }

            return response()->json([
                "status" => "success",
                "status_code" => 200,
                "data" => $agents,
                "message" => "Agents fetched successfully"
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

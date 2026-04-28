<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ReportGenerationService;

class ReportController extends Controller
{
    public function generateAssessment(Request $request, ReportGenerationService $service)
    {
        $request->validate([
//            'rating_id' => 'required',
//            'assigned_long_term_rating' => 'required',
//            'assigned_short_term_rating' => 'required',
//            'rating_action' => 'required',
//            'outlook' => 'required',
//            'last_year_report_html' => 'required',
//            'financial_file_url' => 'required|url',
        ]);

//        try {
            $result = $service->generateAssessment($request->all());

            return response()->json($result);

//        } catch (\Exception $e) {
//            return response()->json([
//                'status' => 'error',
//                'message' => $e->getMessage()
//            ], 500);
//        }
    }
    public function generateRationale(Request $request, ReportGenerationService $service)
    {
        $request->validate([
//            'rating_id' => 'required',
//            'assigned_long_term_rating' => 'required',
//            'assigned_short_term_rating' => 'required',
//            'rating_action' => 'required',
//            'outlook' => 'required',
//            'last_year_report_html' => 'required',
//            'financial_file_url' => 'required|url',
        ]);

        try {
            $result = $service->generateRationale($request->all());

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

<?php
//Route::middleware('api.key')->post('/v1/generate-report-content', [\App\Http\Controllers\Api\ReportController::class, 'generate']);
Route::middleware('api.key')->post('/v1/generate-assessment-content',
    [\App\Http\Controllers\Api\ReportController::class, 'generateAssessment']
);
Route::middleware('api.key')->post('/v1/generate-rationale-content',
    [\App\Http\Controllers\Api\ReportController::class, 'generateRationale']
);

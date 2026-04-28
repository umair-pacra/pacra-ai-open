<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use App\Services\ClaudeService;
use App\Services\FileParserService;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReportGenerationService
{
    public function generateAssessment($data)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $prompt = $this->buildPromptAssessment($data);
        $claude = app(ClaudeService::class);
        $response = $claude->generate($prompt);
        $json = $this->cleanJson($response['text']);
        $usage = $this->calculateUsage(
            $response['input_tokens'],
            $response['output_tokens']
        );

        return [
            'status' => 'success',
            'rating_id' => $data['rating_id'],
            'data' => $json,
            'usage' => $usage
        ];
    }
    public function generateRationale($data)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $prompt = $this->buildPromptRationale($data);
        $claude = app(ClaudeService::class);
        $response = $claude->generate($prompt);
        $json = $this->cleanJson($response['text']);
        $usage = $this->calculateUsage(
            $response['input_tokens'],
            $response['output_tokens']
        );

        return [
            'status' => 'success',
            'rating_id' => $data['rating_id'],
            'data' => $json,
            'usage' => $usage
        ];
    }

    private function buildPromptAssessment($data)
    {
        return "
You are a senior credit analyst. Respond ONLY with a raw valid JSON object. No markdown, no explanation. No Hallucination.
OUTPUT FORMAT:
{
  \"profile\": \"...\",
  \"ownership\": \"...\",
  \"governance\": \"...\",
  \"management\": \"...\",
  \"business_risk\": \"...\",
  \"financial_risk\": \"...\"
}
Generate a formal credit rating report's assessment and rating analysis for the entity using the following STRICT format:
FORMAT:
    Rating Analysis:
        A.Profile
            •Legal Structure
            •Background
            •Operations
        B.Ownership
        C.Governance
        D.Management
        E.Business Risk
            •Industry Dynamics
            •Relative Position
            •Revenue & Diversification
        F.Financial Risk
            •Profitability
            •Coverage
            •Leverage
            •Liquidity / Working Capital
ANALYTICAL GUIDANCE:
-Anchor your assessment in the entity's relative position within its industry
-Reflect the risk profile of the industry (e.g., cyclicality, competitiveness, capital intensity, regulatory exposure) and how it constrains or supports the rating.
-Distinguish clearly between:
    -Business risk(industry + market position + revenue quality)
    -Financial risk(cash flows, leverage, coverage, liquidity)
-Evaluate revenue quality through:
    -Stability over time
    -Diversification (product, customer, geography)
-Assess competitive strength using:
    -Market share / scale
    -Growth relative to industry
    -Sustainability of competitive advantages
-Incorporate forward-looking sustainability, including:
    -Ability to withstand stress scenarios
    -Dependence on external factors (e.g., commodity prices, government policy)
-Reflect ownership strength and sponsor support, including:
    -Willingness and ability to provide financial support
    -Succession and stability considerations
-Evaluate governance and management quality in terms of:
    -Oversight effectiveness
    -Execution track record
    -Systems and controls
-Highlight constraints on the rating, including:
    -Industry ceiling effects
    -Structural weaknesses (e.g., working capital intensity, concentration risk) The analysis should:
        •Base all analysis strictly on the provided documents. Do not hallucinate.
        •Ensure internal consistency between analysis and rating and avoid generic statements
FINANCIAL DATA (Justify and analyze the current financial statement/summary):
<financial_data>
{$data['financial_data']}
</financial_data>

CLIENT NAME AND SECTOR (for also adding latest and authentic data via web search to refine overall content):
<client_data>
{$data['client_name']}, {$data['sector']}
</client_data>

PRIOR YEAR REPORT (for history, style and context only):
<prior_report>
{$data['last_year_report_html']}
</prior_report>

ADDITIONAL REFINED PROMPT FOR FURTHER PRECISION (used to enhance or fine-tune the existing default prompt if needed):
<custom_prompt>
{$data['custom_prompt']}
</custom_prompt>
";
    }
    private function buildPromptRationale($data)
    {
        return "
You are a senior credit analyst. Respond ONLY with a raw valid JSON object. No markdown, no explanation. No Hallucination.
OUTPUT FORMAT:
{
  \"Rating Rationale\": \"...\",
  \"Key Rating Drivers\": \"...\",
  \"About The Entity\": \"...\"
}
Generate a formal credit rating report's assessment and rating analysis for the entity using the following STRICT format:
FORMAT:
    1. Rating Rationale (1–2 paragraphs summarizing rating justification)
    2. Key Rating Drivers
ANALYTICAL GUIDANCE:
-Anchor your assessment in the entity's relative position within its industry
-Reflect the risk profile of the industry (e.g., cyclicality, competitiveness, capital intensity, regulatory exposure) and how it constrains or supports the rating.
-Distinguish clearly between:
    -Business risk(industry + market position + revenue quality)
    -Financial risk(cash flows, leverage, coverage, liquidity)
-Evaluate revenue quality through:
    -Stability over time
    -Diversification (product, customer, geography)
-Assess competitive strength using:
    -Market share / scale
    -Growth relative to industry
    -Sustainability of competitive advantages
-Incorporate forward-looking sustainability, including:
    -Ability to withstand stress scenarios
    -Dependence on external factors (e.g., commodity prices, government policy)
-Reflect ownership strength and sponsor support, including:
    -Willingness and ability to provide financial support
    -Succession and stability considerations
-Evaluate governance and management quality in terms of:
    -Oversight effectiveness
    -Execution track record
    -Systems and controls
-Highlight constraints on the rating, including:
    -Industry ceiling effects
    -Structural weaknesses (e.g., working capital intensity, concentration risk) The analysis should:
        •Base all analysis strictly on the provided documents. Do not hallucinate.
        •Ensure internal consistency between analysis and rating and avoid generic statements
INPUT PARAMETERS:
Justify the assigned long-term rating:
<long-term-rating>
{$data['long_term_rating']}
</long-term-rating>

Justify the assigned short-term rating:
<short-term-rating>
{$data['short_term_rating']}
</short-term-rating>

Justify the assigned rating action:
<rating-action>
{$data['rating_action']}
</rating-action>

Justify the assigned rating outlook:
<rating-outlook>
{$data['rating_outlook']}
</rating-outlook>

FINANCIAL DATA (Justify and analyze the current financial statement/summary):
<financial_data>
{$data['financial_data']}
</financial_data>

CLIENT NAME AND SECTOR (for also adding latest and authentic data via Internet browsing to refine overall content):
<client_data>
{$data['client_name']}, {$data['sector']}
</client_data>

PRIOR YEAR REPORT (for history, style and context only):
<prior_report>
{$data['last_year_report_html']}
</prior_report>

ADDITIONAL REFINED PROMPT FOR FURTHER PRECISION (used to enhance or fine-tune the existing default prompt if needed):
<custom_prompt>
{$data['custom_prompt']}
</custom_prompt>
";
    }

    private function cleanJson($text)
    {
        $clean = preg_replace('/```json|```/', '', $text);

        return json_decode($clean, true);
    }

    private function calculateUsage($input, $output)
    {
        $inputCost = $input * config('services.claude.input_cost', 0.000003);
        $outputCost = $output * config('services.claude.output_cost', 0.000015);

        $total = $inputCost + $outputCost;

        $balance = config('app.claude_balance') - $total;

        return [
            'input_tokens' => $input,
            'output_tokens' => $output,
            'estimated_cost_usd' => round($total, 4),
            'remaining_balance_estimate' => round($balance, 2),
        ];
    }
}

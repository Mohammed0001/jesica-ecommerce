<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AssetCheckerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AdminToolsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // STUB: created by enhancement script — safe to replace
        // Add appropriate middleware for admin access
        // $this->middleware(['auth', 'can:admin']);
    }

    /**
     * Show the asset report page
     */
    public function assetReport(AssetCheckerService $assetChecker)
    {
        $missingAssets = $assetChecker->getMissingAssets();
        $statistics = $assetChecker->getAssetStatistics();
        $linkReport = $this->getLinkCheckReport();

        return view('admin.tools.asset-report', compact(
            'missingAssets',
            'statistics',
            'linkReport'
        ));
    }

    /**
     * Get the latest link check report
     */
    private function getLinkCheckReport(): ?array
    {
        $reportPath = storage_path('logs/link-check-report.json');

        if (File::exists($reportPath)) {
            try {
                $reportContent = File::get($reportPath);
                return json_decode($reportContent, true);
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Run asset check via AJAX
     */
    public function runAssetCheck(AssetCheckerService $assetChecker)
    {
        $missingAssets = $assetChecker->getMissingAssets();
        $statistics = $assetChecker->getAssetStatistics();

        return response()->json([
            'success' => true,
            'missing_assets' => $missingAssets,
            'statistics' => $statistics,
            'message' => count($missingAssets) === 0
                ? 'No missing assets found!'
                : count($missingAssets) . ' missing assets found.'
        ]);
    }
}

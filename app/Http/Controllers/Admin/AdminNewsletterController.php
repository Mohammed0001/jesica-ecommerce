<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminNewsletterController extends Controller
{
    /**
     * Show recent newsletter log lines.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showLogs(Request $request)
    {
        // parameters: lines (legacy), perPage, q (search)
        $perPage = max(1, (int) $request->query('perPage', $request->query('lines', 200)));
        $query = $request->query('q', null);
        $path = storage_path('logs/newsletter.log');

        $lastModified = null;
        $totalLines = 0;
        $items = [];

        if (File::exists($path)) {
            $content = File::get($path);
            $arr = preg_split('/\r\n|\n|\r/', trim($content));
            $arr = is_array($arr) ? $arr : [];
            // Reverse so newest entries appear first
            $arr = array_reverse($arr);
            $totalLines = count($arr);

            if ($query) {
                $arr = array_filter($arr, function ($line) use ($query) {
                    return stripos($line, $query) !== false;
                });
            }

            // Convert to collection for easy slicing/pagination
            $collection = collect(array_values($arr));
            $page = LengthAwarePaginator::resolveCurrentPage();
            $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();

            $paginator = new LengthAwarePaginator(
                $results,
                $collection->count(),
                $perPage,
                $page,
                ['path' => url()->current(), 'query' => request()->query()]
            );

            $items = $results->all();
            $lastModified = date('Y-m-d H:i:s', File::lastModified($path));
        } else {
            $paginator = new LengthAwarePaginator([], 0, $perPage, 1, ['path' => url()->current()]);
        }

        return view('admin.newsletter.logs', [
            'perPage' => $perPage,
            'query' => $query,
            'logs' => $items,
            'paginator' => $paginator,
            'path' => $path,
            'lastModified' => $lastModified,
            'totalLines' => $totalLines,
        ]);
    }
}

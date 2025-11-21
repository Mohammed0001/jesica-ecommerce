@extends('layouts.admin')

@section('title', 'Asset Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Asset Checker Report</h3>
                    <button type="button" class="btn btn-primary" onclick="runAssetCheck()">
                        <i class="fas fa-sync-alt"></i> Run Check
                    </button>
                </div>

                <div class="card-body">
                    <!-- Statistics Overview -->
                    @if($statistics)
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-primary">Files Checked</h5>
                                    <h2 class="text-primary">{{ $statistics['total_files_checked'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-info">Assets Found</h5>
                                    <h2 class="text-info">{{ $statistics['total_assets_found'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-{{ $statistics['missing_assets_count'] > 0 ? 'danger' : 'success' }}">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-{{ $statistics['missing_assets_count'] > 0 ? 'danger' : 'success' }}">Missing Assets</h5>
                                    <h2 class="text-{{ $statistics['missing_assets_count'] > 0 ? 'danger' : 'success' }}">{{ $statistics['missing_assets_count'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-secondary">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-secondary">Last Checked</h5>
                                    <p class="text-secondary mb-0">
                                        {{ \Carbon\Carbon::parse($statistics['last_checked'])->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Missing Assets Table -->
                    @if(count($missingAssets) > 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> {{ count($missingAssets) }} missing assets found.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Source File</th>
                                    <th>Missing Asset</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($missingAssets as $asset)
                                <tr>
                                    <td>
                                        <span class="badge badge-{{ $asset['type'] === 'image' ? 'primary' : ($asset['type'] === 'database_image' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $asset['type'])) }}
                                        </span>
                                    </td>
                                    <td><code>{{ $asset['file'] }}</code></td>
                                    <td><code>{{ $asset['asset'] }}</code></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="copyToClipboard('{{ $asset['asset'] }}')"
                                                title="Copy path">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Great!</strong> No missing assets found.
                    </div>
                    @endif

                    <!-- Link Check Report -->
                    @if($linkReport)
                    <hr class="my-4">
                    <h4>Link Check Report</h4>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-success">Successful Routes</h6>
                                    <h4 class="text-success">{{ $linkReport['summary']['successfulRoutes'] }}/{{ $linkReport['summary']['totalRoutes'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-{{ $linkReport['summary']['failedRoutes'] > 0 ? 'danger' : 'success' }}">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-{{ $linkReport['summary']['failedRoutes'] > 0 ? 'danger' : 'success' }}">Failed Routes</h6>
                                    <h4 class="text-{{ $linkReport['summary']['failedRoutes'] > 0 ? 'danger' : 'success' }}">{{ $linkReport['summary']['failedRoutes'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-info">Avg Response Time</h6>
                                    <h4 class="text-info">{{ $linkReport['summary']['averageResponseTime'] }}ms</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($linkReport['summary']['failedRoutes'] > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>URL</th>
                                    <th>Status</th>
                                    <th>Response Time</th>
                                    <th>Error</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($linkReport['routes'] as $route)
                                    @if(!$route['success'])
                                    <tr>
                                        <td><code>{{ $route['url'] }}</code></td>
                                        <td>
                                            <span class="badge badge-danger">{{ $route['status'] ?: 'ERROR' }}</span>
                                        </td>
                                        <td>{{ $route['responseTime'] ? $route['responseTime'] . 'ms' : 'N/A' }}</td>
                                        <td>{{ $route['error'] }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                    @endif

                    <!-- Help Section -->
                    <hr class="my-4">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Help & Instructions</h5>
                        </div>
                        <div class="card-body">
                            <h6>Manual Commands:</h6>
                            <ul>
                                <li><code>php artisan assets:check</code> - Run asset check from command line</li>
                                <li><code>npm run check-links</code> - Run link checker from command line</li>
                                <li><code>php artisan storage:link</code> - Create storage symlink if missing</li>
                            </ul>

                            <h6>Adding New Pages to Link Checker:</h6>
                            <p>Edit <code>link-check-config.json</code> in the project root to add new routes to check.</p>

                            <h6>Common Solutions:</h6>
                            <ul>
                                <li><strong>Missing images:</strong> Check if files exist in <code>public/</code> or <code>storage/app/public/</code></li>
                                <li><strong>Storage images not found:</strong> Run <code>php artisan storage:link</code></li>
                                <li><strong>Database images missing:</strong> Update database records or upload missing files</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function runAssetCheck() {
    // This would make an AJAX call to trigger asset checking
    // For now, just reload the page
    location.reload();
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // You could show a toast notification here
        alert('Copied to clipboard: ' + text);
    });
}
</script>

<style>
.badge-primary { background-color: #007bff; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-secondary { background-color: #6c757d; }
.badge-danger { background-color: #dc3545; }
.badge-success { background-color: #28a745; }

.card-title {
    font-family: "Futura PT", system-ui, sans-serif;
    font-weight: 200;
    letter-spacing: 0.05em;
}

code {
    font-size: 0.875em;
    color: #e83e8c;
    word-break: break-all;
}
</style>
@endsection

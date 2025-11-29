@extends('layouts.admin')

@section('title', 'Create Size Chart')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header">
            <h6>Create Size Chart</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.size-charts.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" required value="{{ old('name') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Image (optional)</label>
                    <input type="file" name="image" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Measurements (key:value format)</label>

                    <div id="measurements-rows" class="mb-2">
                        <!-- rows will be injected by JS -->
                    </div>

                    <div class="d-flex gap-2 mb-2">
                        <button type="button" id="add-size" class="btn btn-sm btn-outline-primary">Add Size</button>
                        <button type="button" id="clear-sizes" class="btn btn-sm btn-outline-secondary">Clear</button>
                    </div>

                    <input type="hidden" name="measurements" id="measurements-input" value="{{ old('measurements') ? e(old('measurements')) : '' }}">

                    <div class="form-text mt-2">Enter a size label and measurements as comma-separated key:value pairs. Example for "S": <code>chest:84, waist:68</code>.</div>
                    <pre class="mt-2 p-2" style="background:#f8f9fa;border:1px solid #e9ecef">Example rows:
S => chest:84, waist:68
M => chest:88, waist:72</pre>
                </div>

                <button class="btn btn-primary">Create</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function(){
        const container = document.getElementById('measurements-rows');
        const addBtn = document.getElementById('add-size');
        const clearBtn = document.getElementById('clear-sizes');
        const hiddenInput = document.getElementById('measurements-input');
        const form = container.closest('form');

        // Helper: create a row
        function createRow(size = '', measurements = ''){
            const row = document.createElement('div');
            row.className = 'd-flex gap-2 align-items-start mb-2';

            const sizeInput = document.createElement('input');
            sizeInput.type = 'text';
            sizeInput.placeholder = 'Size (e.g. S, M, L)';
            sizeInput.className = 'form-control';
            sizeInput.style.maxWidth = '160px';
            sizeInput.value = size;

            const measurementsInput = document.createElement('input');
            measurementsInput.type = 'text';
            measurementsInput.placeholder = 'e.g. chest:84, waist:68';
            measurementsInput.className = 'form-control';
            measurementsInput.value = measurements;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-outline-danger btn-sm';
            removeBtn.textContent = 'Remove';
            removeBtn.addEventListener('click', function(){ row.remove(); });

            row.appendChild(sizeInput);
            row.appendChild(measurementsInput);
            row.appendChild(removeBtn);

            container.appendChild(row);
        }

        // Parse measurements string like "chest:84, waist:68" to object
        function parseMeasurementsStr(str){
            const out = {};
            if (!str) return out;
            const parts = str.split(',');
            for (let p of parts){
                const [k, v] = p.split(':');
                if (!k) continue;
                const key = k.trim();
                let val = v !== undefined ? v.trim() : '';
                if (val === '') continue;
                const num = Number(val);
                out[key] = isNaN(num) ? val : num;
            }
            return out;
        }

        // Convert object of measurements to string for input
        function measurementsObjToStr(obj){
            return Object.entries(obj).map(([k,v]) => `${k}:${v}`).join(', ');
        }

        // Populate from oldMeasurements (if any)
        try{
            const oldRaw = hiddenInput.value && hiddenInput.value.trim() !== '' ? hiddenInput.value : null;
            if (oldRaw){
                const parsed = JSON.parse(oldRaw);
                if (parsed && typeof parsed === 'object'){
                    for (const [size, meas] of Object.entries(parsed)){
                        if (meas && typeof meas === 'object'){
                            createRow(size, measurementsObjToStr(meas));
                        } else {
                            createRow(size, String(meas));
                        }
                    }
                }
            }
        } catch(e){
            // ignore parse errors and leave blank row
        }

        // If no old rows, add one starter row
        if (!container.children.length){
            createRow('', 'chest:84, waist:68');
        }

        addBtn.addEventListener('click', function(){ createRow('', ''); });
        clearBtn.addEventListener('click', function(){ container.innerHTML = ''; createRow('', ''); });

        // On form submit, serialize rows into JSON and place into hidden input
        form.addEventListener('submit', function(e){
            const rows = Array.from(container.children);
            const out = {};
            for (const row of rows){
                const inputs = row.getElementsByTagName('input');
                if (inputs.length < 2) continue;
                const size = inputs[0].value.trim();
                const measStr = inputs[1].value.trim();
                if (!size) continue; // skip empty size labels
                const parsed = parseMeasurementsStr(measStr);
                out[size] = parsed;
            }
            hiddenInput.value = JSON.stringify(out);
        });
    })();
</script>
</script>
@endpush

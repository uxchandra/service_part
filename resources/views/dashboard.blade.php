@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Dashboard</h1>
        <div class="ml-auto">
            <a href="{{ url('/andon') }}" target="_blank" class="btn btn-dark mr-2">
                <i class="fa fa-tv mr-1"></i> Andon Display
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body py-3">
                            <div class="text-dark" style="font-size:16px; font-weight: 800;">Total Orders</div>
                            <div id="kpi_total" class="h4 mb-0 text-dark mt-3">-</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body py-3">
                            <div class="text-dark" style="font-size:16px; font-weight: 800;">Planning</div>
                            <div id="kpi_planning" class="h4 mb-0 text-primary mt-3">-</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body py-3">
                            <div class="text-dark" style="font-size:16px; font-weight: 800;">Partial</div>
                            <div id="kpi_partial" class="h4 mb-0 text-warning mt-3">-</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body py-3">
                            <div class="text-dark" style="font-size:16px; font-weight: 800;">Pulling</div>
                            <div id="kpi_pulling" class="h4 mb-0 text-info mt-3">-</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body py-3">
                            <div class="text-dark" style="font-size:16px; font-weight: 800;">Delay</div>
                            <div id="kpi_delay" class="h4 mb-0 text-danger mt-3">-</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body py-3">
                            <div class="text-dark" style="font-size:16px; font-weight: 800;">Completed</div>
                            <div id="kpi_completed" class="h4 mb-0 text-success text-c mt-3">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header text-dark py-2" style="font-size:16px; font-weight: 800;"><strong>Orders Due Today</strong></div>
                <div class="card-body p-2">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" style="font-size: 14px;">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-dark">No Order</th>
                                    <th class="text-dark">Delivery</th>
                                    <th class="text-dark">Status</th>
                                    <th class="text-center text-dark">Progress</th>
                                </tr>
                            </thead>
                            <tbody id="tbl_due_today"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header py-2 d-flex justify-content-between align-items-center text-dark" style="font-size:16px; font-weight: 800;">
                    <strong>Monitoring Stok (aktif)</strong>
                    <a href="{{ route('barang.index') }}" class="btn btn-sm btn-outline-primary">Kelola Barang</a>
                </div>
                <div class="card-body p-2">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" style="font-size: 14px;" >
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-dark">Part No</th>
                                    <th class="text-dark">Part Name</th>
                                    <th class="text-center text-dark">Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stokList as $b)
                                <tr>
                                    <td>{{ $b->part_no }}</td>
                                    <td>{{ $b->part_name }}</td>
                                    <td class="text-center">{{ $b->stok }}</td>
                                </tr>
                                @endforeach
                                @if($stokList->isEmpty())
                                <tr><td colspan="3" class="text-center text-dark">Tidak ada data</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    refreshAll();
    setInterval(refreshAll, 15000);

    async function refreshAll() {
        try {
            const [orders, pulling, packing] = await Promise.all([
                fetchOrders(), fetchPulling(), fetchPacking()
            ]);
            renderKpis(orders);
            renderDueToday(orders);
            renderPulling(pulling);
            renderPacking(packing);
        } catch (e) {
            // ignore
        }
    }

    async function fetchOrders() {
        const res = await fetch('{{ route("orders.get-data") }}?length=-1', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const json = await res.json();
        return json.data || [];
    }
    async function fetchPulling() {
        const res = await fetch('{{ route("pulling.get-data") }}?length=10', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const json = await res.json();
        return json.data || [];
    }
    async function fetchPacking() {
        const res = await fetch('{{ route("packing.get-data") }}?length=10', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const json = await res.json();
        return json.data || [];
    }

    function renderKpis(orders) {
        const total = orders.length;
        const byStatus = orders.reduce((acc, o) => { acc[o.status] = (acc[o.status]||0)+1; return acc; }, {});
        document.getElementById('kpi_total').textContent = total;
        document.getElementById('kpi_planning').textContent = byStatus['planning']||0;
        document.getElementById('kpi_partial').textContent = byStatus['partial']||0;
        document.getElementById('kpi_pulling').textContent = byStatus['pulling']||0;
        document.getElementById('kpi_delay').textContent = byStatus['delay']||0;
        document.getElementById('kpi_completed').textContent = byStatus['completed']||0;
    }

    function parseIdDate(dmy) {
        // expects dd/mm/yyyy
        if (!dmy) return null;
        const parts = dmy.split('/');
        if (parts.length !== 3) return null;
        return new Date(parseInt(parts[2],10), parseInt(parts[1],10)-1, parseInt(parts[0],10));
    }

    function renderDueToday(orders) {
        const tbodyDue = document.getElementById('tbl_due_today');
        tbodyDue.innerHTML = '';
        const today = new Date(); today.setHours(0,0,0,0);
        const rows = orders.map(o => {
            const d = parseIdDate(o.delivery_date_display);
            return { no: o.no_transaksi_display, status: o.status, delivery: o.delivery_date_display, date: d, progress: o.progress_display };
        });
        const dueToday = rows.filter(r => r.date && r.date.getTime() === today.getTime() && r.status !== 'completed');
        dueToday.slice(0, 8).forEach(r => {
            const badge = r.status === 'planning' ? 'primary' : (r.status === 'partial' ? 'warning' : (r.status === 'pulling' ? 'info' : (r.status === 'delay' ? 'danger' : 'secondary')));
            const label = r.status.charAt(0).toUpperCase() + r.status.slice(1);
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${r.no}</td><td>${r.delivery||'-'}</td><td><span class="badge badge-${badge}">${label}</span></td><td class="text-center">${r.progress}</td>`;
            tbodyDue.appendChild(tr);
        });
    }

});
</script>
@endpush
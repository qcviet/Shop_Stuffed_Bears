<?php
// Check DB
if (!$db) {
    echo '<div class="alert alert-danger">Database connection failed. Please check your configuration.</div>';
    return;
}

// Prepare default range: show last 12 months via API call in JS
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Reports</h5>
    <small class="text-muted">Revenue and Orders by Month</small>
    </div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Monthly Revenue & Orders</h6>
        <form id="reportsFilter" class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm" style="max-width: 320px;">
                <span class="input-group-text">From</span>
                <input type="month" class="form-control" id="reportsFrom" name="from">
                <span class="input-group-text">To</span>
                <input type="month" class="form-control" id="reportsTo" name="to">
            </div>
            <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-search"></i> Filter</button>
        </form>
    </div>
    <div class="card-body">
        <canvas id="reportsMonthlyChart" height="90"></canvas>
        <div class="table-responsive mt-3">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Revenue (VND)</th>
                        <th>Orders</th>
                    </tr>
                </thead>
                <tbody id="reportsMonthlyTbody"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseFetch = async (from=null, to=null) => {
        const params = new URLSearchParams({ action: 'monthly_report' });
        if (from) params.set('from', from);
        if (to) params.set('to', to);
        const res = await fetch('actions/order_actions.php?' + params.toString());
        return res.json();
    };

    const render = (rows) => {
        const tbody = document.getElementById('reportsMonthlyTbody');
        tbody.innerHTML = '';
        const labels = [];
        const revenues = [];
        const orders = [];
        rows.forEach(r => {
            const rev = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(Number(r.revenue||0));
            tbody.insertAdjacentHTML('beforeend', `<tr><td>${r.ym}</td><td>${rev}</td><td>${r.orders}</td></tr>`);
            labels.push(r.ym);
            revenues.push(Number(r.revenue||0));
            orders.push(Number(r.orders||0));
        });
        const ctx = document.getElementById('reportsMonthlyChart');
        if (!ctx) return;
        if (window._reportsMonthlyChart) window._reportsMonthlyChart.destroy();
        window._reportsMonthlyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { type: 'bar', label: 'Revenue (VND)', data: revenues, backgroundColor: 'rgba(75,192,192,0.4)', borderColor: 'rgba(75,192,192,1)', borderWidth: 1, yAxisID: 'y' },
                    { type: 'line', label: 'Orders', data: orders, borderColor: 'rgba(54,162,235,1)', backgroundColor: 'rgba(54,162,235,0.2)', tension: 0.3, yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: v => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(v) } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } }
                }
            }
        });
    };

    // initial load
    baseFetch().then(res => { if (res.success) render(res.data || []); });

    document.getElementById('reportsFilter').addEventListener('submit', function(e){
        e.preventDefault();
        const from = document.getElementById('reportsFrom').value || null;
        const to = document.getElementById('reportsTo').value || null;
        baseFetch(from, to).then(res => { if (res.success) render(res.data || []); });
    });
});
</script>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Andon - Service Part</title>
    <link rel="icon" href="/favicon.ico">
    <style>
        html, body { height: 100%; }
        body {
            margin: 0;
            background: #000;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen,
                Ubuntu, Cantarell, 'Fira Sans', 'Droid Sans', 'Helvetica Neue', Arial, sans-serif;
        }
        .container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .header {
            display: grid;
            grid-template-columns: 220px 1fr 280px;
            gap: 12px;
            align-items: center;
            padding: 12px;
            border-bottom: 2px solid #fff;
        }
        .box {
            border: 2px solid #fff;
            border-radius: 6px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 70px;
        }
        .box.logo { justify-content: center; }
        .box.logo img { height: 100px; width: auto; }
        .box.title { font-size: 50px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
        .box.clock { font-size: 18px; font-weight: 600; text-align: center; flex-direction: column; }
        .clock-date { font-size: 25px; font-weight: 600; }
        .clock-time { font-size: 28px; font-weight: 800; line-height: 1.2; }
        .table-wrap { flex: 1; overflow: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th { position: sticky; top: 0; background: #161616; z-index: 1; font-size: 17px; }
        th, td { border: 2px solid #fff; padding: 10px; text-align: center; }
        th { font-size: 14px; font-weight: 800; }
        td { font-size: 16px; vertical-align: middle; }
        .status {
            display: inline-block;
            min-width: 90px;
            padding: 6px 12px;
            border-radius: 16px;
            font-weight: 800;
            color: #fff;
            text-transform: capitalize;
        }
        .status.planning { background: #1a548a; }
        .status.partial { background: #ffc107; color: #000; }
        .status.pulling { background: #17a2b8; }
        .status.completed { background: #28a745; }
        .status.delay { background: #dc3545; }
        .footer { padding: 6px 12px; border-top: 2px solid #fff; font-size: 12px; opacity: .6; }
        @media (max-width: 768px) {
            .header { grid-template-columns: 120px 1fr 160px; }
            .box.title { font-size: 20px; }
            .box.clock { font-size: 14px; }
            th, td { padding: 8px; }
            td { font-size: 14px; }
        }
    </style>
    <script>
        function formatDate(date) {
            const d = new Date(date);
            const pad = n => String(n).padStart(2, '0');
            const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            return days[d.getDay()] + ', ' + pad(d.getDate()) + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
        }

        function formatTime(date) {
            const d = new Date(date);
            const pad = n => String(n).padStart(2, '0');
            return pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
        }

        function setClock() {
            const elDate = document.getElementById('clock-date');
            const elTime = document.getElementById('clock-time');
            if (!elDate || !elTime) return;
            const now = Date.now();
            elDate.textContent = formatDate(now);
            elTime.textContent = formatTime(now);
        }

        async function fetchData() {
            const res = await fetch('/andon-data');
            if (!res.ok) return [];
            const json = await res.json();
            return json.data || [];
        }

        function renderRows(data) {
            const tbody = document.querySelector('#orders-body');
            if (!tbody) return;
            tbody.innerHTML = '';
            // Group by no_transaksi
            const groups = {};
            data.forEach(r => {
                const key = r.no_transaksi || '-';
                if (!groups[key]) groups[key] = [];
                groups[key].push(r);
            });

            Object.keys(groups).forEach(no => {
                const rows = groups[no];
                const span = rows.length;
                rows.forEach((row, idx) => {
                    const tr = document.createElement('tr');
                    let html = '';
                    if (idx === 0) {
                        html += `<td rowspan="${span}">${row.no_transaksi || '-'}</td>`;
                    }
                    html += `
                        <td>${row.part_no || '-'}</td>
                        <td>${row.qty_order}</td>
                        <td>${row.stok}</td>
                        <td>${row.qty_pulling}</td>
                        <td>${row.qty_packing}</td>
                    `;
                    if (idx === 0) {
                        html += `<td rowspan="${span}">${row.delivery_date || '-'}</td>`;
                        html += `<td rowspan="${span}"><span class="status ${row.status}">${row.status}</span></td>`;
                    }
                    tr.innerHTML = html;
                    tbody.appendChild(tr);
                });
            });
        }

        async function refresh() {
            try {
                const data = await fetchData();
                renderRows(data);
            } catch(e) {
                // ignore
            }
        }

        window.addEventListener('load', () => {
            setClock();
            setInterval(setClock, 1000);
            refresh();
            setInterval(refresh, 5000);
        });
    </script>
    <!-- Auto refresh full page each 10 minutes to avoid memory leaks -->
    <meta http-equiv="refresh" content="600">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="box logo">
                <img src="/images/logo.png" alt="Logo">
            </div>
            <div class="box title">Monitoring Service Part</div>
            <div class="box clock"><div class="clock-date" id="clock-date">-</div><div class="clock-time" id="clock-time">-</div></div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No Order</th>
                        <th>Part No</th>
                        <th>Order</th>
                        <th>Stok</th>
                        <th>Pulling</th>
                        <th>Packing</th>
                        <th>Delivery Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="orders-body"></tbody>
            </table>
        </div>
        {{-- <div class="footer">STEP • Service Part</div> --}}
    </div>
</body>
</html>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IP Dashboard</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="topbar">
  <h2>EXAMITY | IP Inventory Dashboard</h2>
</div>

<div class="layout">
<aside class="sidebar">
  <ul>
    <li class="active">Dashboard</li>
    <li><a href="inventory.php">IP Inventory</a></li>
    <li><a href="reports.php">Reports</a></li>
    <li><a href="manage.php">Manage IPs</a></li>
  </ul>
</aside>

<main class="content">
  <section class="summary">
    <a href="status.php?type=total" class="card blue">Total <span id="totalIPs">0</span></a>
    <a href="status.php?type=used" class="card green">Used <span id="usedIPs">0</span></a>
    <a href="status.php?type=free" class="card orange">Free <span id="freeIPs">0</span></a>
    <a href="status.php?type=reserved" class="card yellow">Reserved <span id="reservedIPs">0</span></a>
    <a href="status.php?type=static" class="card red">Static <span id="staticIPs">0</span></a>
  </section>

  <section class="grid-2">
    <div class="box chart-box">
      <h3>IP Status Overview</h3>
      <div class="chart-wrapper"><canvas id="ipChart"></canvas></div>
    </div>
    <div class="box chart-box">
      <h3>IP Count Comparison</h3>
      <div class="chart-wrapper"><canvas id="ipBarChart"></canvas></div>
    </div>
  </section>

  <section class="box location-box">
    <h3>Location-wise IP Summary</h3>
    <table>
      <thead>
        <tr>
          <th>Location</th><th>Used</th><th>Free</th><th>Reserved</th><th>Static</th><th>Total</th>
        </tr>
      </thead>
      <tbody id="locationStats"></tbody>
    </table>
  </section>
</main>
</div>

<script>
let pieChart = null;
let barChart = null;

function drawCharts(data) {
  const pieCtx = document.getElementById('ipChart');
  const barCtx = document.getElementById('ipBarChart');

  if (pieChart) pieChart.destroy();
  if (barChart) barChart.destroy();

  pieChart = new Chart(pieCtx, {
    type: 'pie',
    data: {
      labels: ['Used', 'Free', 'Reserved', 'Static'],
      datasets: [{
        data: [data.used, data.free, data.reserved, data.static],
        backgroundColor: ['#4f46e5', '#10b981', '#facc15', '#dc2626']
      }]
    },
    options: { responsive: true, maintainAspectRatio: false }
  });

  barChart = new Chart(barCtx, {
    type: 'bar',
    data: {
      labels: ['Total', 'Used', 'Free', 'Reserved', 'Static'],
      datasets: [{
        data: [data.total, data.used, data.free, data.reserved, data.static],
        backgroundColor: ['#2563eb', '#16a34a', '#f59e0b', '#facc15', '#dc2626'],
        borderRadius: 8
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });
}

function renderLocations(locations) {
  const table = document.getElementById('locationStats');
  table.innerHTML = '';

  if (!locations.length) {
    table.innerHTML = '<tr><td colspan="6">No data found</td></tr>';
    return;
  }

  locations.forEach((item) => {
    table.innerHTML += `
      <tr>
        <td>${item.location}</td>
        <td>${item.used}</td>
        <td>${item.free}</td>
        <td>${item.reserved}</td>
        <td>${item.static}</td>
        <td>${item.total}</td>
      </tr>
    `;
  });
}

async function loadDashboard() {
  try {
    const response = await fetch('api/dashboard.php', { headers: { 'Accept': 'application/json' } });
    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.error || 'API request failed');
    }

    document.getElementById('totalIPs').innerText = data.total;
    document.getElementById('usedIPs').innerText = data.used;
    document.getElementById('freeIPs').innerText = data.free;
    document.getElementById('reservedIPs').innerText = data.reserved;
    document.getElementById('staticIPs').innerText = data.static;

    drawCharts(data);
    renderLocations(data.locations || []);
  } catch (error) {
    console.error(error);
    document.getElementById('locationStats').innerHTML = '<tr><td colspan="6">Failed to load dashboard data</td></tr>';
  }
}

loadDashboard();
</script>
</body>
</html>

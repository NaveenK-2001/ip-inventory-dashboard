<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>IP Status Details</title>
<link rel="stylesheet" href="style.css">
<script src="app.js" defer></script>
</head>
<body>
<div class="topbar"><h2>EXAMITY | IP Inventory Dashboard</h2></div>
<aside class="sidebar"><ul>
<li><a href="index.php">Dashboard</a></li>
<li><a href="inventory.php">IP Inventory</a></li>
<li><a href="reports.php">Reports</a></li>
<li><a href="manage.php">Manage IPs</a></li>
</ul></aside>
<main class="content">
<h2 id="pageTitle">Status View</h2>
<div class="box"><table><thead><tr><th>IP</th><th>Hostname</th><th>Location</th><th>Zone</th><th>VLAN</th><th>Status</th><th>Owner</th></tr></thead><tbody id="statusTable"></tbody></table></div>
</main>
<script>
async function init(){
  const params = new URLSearchParams(window.location.search);
  const type = params.get('type') || 'total';
  const title = document.getElementById('pageTitle');
  const table = document.getElementById('statusTable');
  let list = await fetchIPs();
  if(type==='used'){list=list.filter(ip=>normalizeStatus(ip.status)==='Used');title.innerText='USED IP LIST';}
  else if(type==='free'){list=list.filter(ip=>normalizeStatus(ip.status)==='Free');title.innerText='FREE IP LIST';}
  else if(type==='reserved'){list=list.filter(ip=>normalizeStatus(ip.status)==='Reserved');title.innerText='RESERVED IP LIST';}
  else if(type==='static'){list=list.filter(ip=>normalizeStatus(ip.status)==='Static');title.innerText='STATIC IP LIST';}
  else {title.innerText='ALL IP LIST';}
  list.sort((a,b)=>compareIPs(a.ip,b.ip));
  table.innerHTML = list.length ? '' : '<tr><td colspan="7">No IPs found</td></tr>';
  list.forEach(ip=>{table.innerHTML += `<tr><td>${ip.ip}</td><td>${ip.hostname||''}</td><td>${ip.location}</td><td>${ip.zone}</td><td>${ip.vlan}</td><td>${normalizeStatus(ip.status)}</td><td>${ip.owner||''}</td></tr>`;});
}
init();
</script>
</body>
</html>

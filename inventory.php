<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IP Inventory</title>
<link rel="stylesheet" href="style.css">
<script src="app.js" defer></script>
</head>
<body>
<div class="topbar"><h2>EXAMITY | IP Inventory Dashboard</h2></div>
<aside class="sidebar"><ul>
<li><a href="index.php">Dashboard</a></li>
<li class="active">IP Inventory</li>
<li><a href="reports.php">Reports</a></li>
<li><a href="manage.php">Manage IPs</a></li>
</ul></aside>
<main class="content">
<h2 class="page-title">IP Inventory</h2>
<div class="inventory-card">
  <div class="section"><div class="section-title">Location</div><div class="level">
    <button class="level-btn active" onclick="selectLocation('HYDE', this)">HYDE</button>
    <button class="level-btn" onclick="selectLocation('HYDW', this)">HYDW</button>
  </div></div>
  <div class="section"><div class="section-title">Zone</div><div class="level">
    <button class="level-btn active" onclick="selectZone('Zone 1', this)">Zone 1</button>
    <button class="level-btn" onclick="selectZone('Zone 2', this)">Zone 2</button>
  </div></div>
  <div class="section">
    <div class="section-title">VLANs <button class="vlan-settings-btn" onclick="openVlanSettings()">⚙️</button></div>
    <div class="vlan-grid" id="vlanButtons"></div>
    <button id="addIpBtn" style="margin-top:16px; display:none" onclick="showAddIpForm()">➕ Add IP to this VLAN</button>
  </div>
</div>
<div class="inventory-card" id="ipListSection" style="margin-top:24px; display:none">
  <div class="section-title">IP Addresses — <span id="vlanLabel" style="color:#2563eb"></span></div>
  <table><thead><tr><th>IP</th><th>Hostname</th><th>Location</th><th>Zone</th><th>VLAN</th><th>Device</th><th>OS</th><th>Status</th><th>Owner</th></tr></thead><tbody id="ipListTable"></tbody></table>
</div>
<div class="inventory-card" id="addIpForm" style="margin-top:24px; display:none">
  <div class="section-title">Add IP</div>
  <div class="form-grid">
    <input id="ip" placeholder="IP Address"><input id="hostname" placeholder="Hostname"><input id="device" placeholder="Device"><input id="os" placeholder="OS">
    <select id="status"><option>Used</option><option>Free</option><option>Reserved</option><option>Static</option></select>
    <input id="owner" placeholder="Owner"><button onclick="addIpFromVlan()">Save IP</button>
  </div>
</div>
</main>
<script>
let currentLocation = 'HYDE';
let currentZone = 'Zone 1';
let selectedVlan = null;
let vlanCache = [];

function setActive(btn){btn.parentElement.querySelectorAll('button').forEach(b=>b.classList.remove('active'));btn.classList.add('active');}
function showAddIpForm(){document.getElementById('addIpForm').style.display='block';}
function hideAddIpUI(){document.getElementById('addIpForm').style.display='none';document.getElementById('addIpBtn').style.display='none';document.getElementById('ipListSection').style.display='none';}

async function renderVlans(){
  const box = document.getElementById('vlanButtons');
  vlanCache = await fetchVlans(currentLocation, currentZone);
  box.innerHTML = '';
  vlanCache.forEach(v=>{ box.innerHTML += `<button class="vlan-btn" onclick="selectVlan('${v.id}', this)">VLAN ${v.id}</button>`; });
}
function selectLocation(loc, btn){currentLocation=loc;selectedVlan=null;setActive(btn);hideAddIpUI();renderVlans();}
function selectZone(zone, btn){currentZone=zone;selectedVlan=null;setActive(btn);hideAddIpUI();renderVlans();}

async function selectVlan(vlan,btn){selectedVlan=vlan;document.querySelectorAll('.vlan-btn').forEach(b=>b.classList.remove('active'));btn.classList.add('active');document.getElementById('addIpBtn').style.display='inline-block';const v=vlanCache.find(x=>String(x.id)===String(vlan));document.getElementById('vlanLabel').innerText=`VLAN ${vlan} : ${v?.name || 'Unnamed VLAN'}`;await renderIpList();}

async function renderIpList(){
  const table=document.getElementById('ipListTable');
  const list=(await fetchIPs({location:currentLocation,zone:currentZone,vlan:selectedVlan})).sort((a,b)=>compareIPs(a.ip,b.ip));
  table.innerHTML='';
  if(!list.length){table.innerHTML='<tr><td colspan="9">No IPs</td></tr>';} else {
    list.forEach(ip=>{table.innerHTML += `<tr><td>${ip.ip}</td><td>${ip.hostname||''}</td><td>${ip.location}</td><td>${ip.zone}</td><td>${ip.vlan}</td><td>${ip.device||''}</td><td>${ip.os||''}</td><td>${normalizeStatus(ip.status)}</td><td>${ip.owner||''}</td></tr>`;});
  }
  document.getElementById('ipListSection').style.display='block';
}

async function addIpFromVlan(){
  const payload={ip:ip.value.trim(),hostname:hostname.value.trim(),location:currentLocation,zone:currentZone,vlan:selectedVlan,device:device.value.trim(),os:os.value.trim(),status:status.value,owner:owner.value.trim()};
  if(!payload.ip){alert('IP required');return;}
  try{await createIP(payload);await renderIpList();['ip','hostname','device','os','owner'].forEach(id=>document.getElementById(id).value='');}
  catch(e){alert(e.message);}
}

async function openVlanSettings(){
  const c=prompt('VLAN SETTINGS\n\n1 = Add VLAN\n2 = Delete VLAN');
  if(c==='1'){const id=prompt('VLAN ID');if(!id)return;const name=prompt('VLAN Name',`VLAN ${id}`)||`VLAN ${id}`;try{await saveVlan({id,name,location:currentLocation,zone:currentZone});await renderVlans();}catch(e){alert(e.message);}return;}
  if(c==='2'){if(!selectedVlan)return alert('Select VLAN first');if(!confirm(`Delete VLAN ${selectedVlan}?`))return;try{await removeVlan(selectedVlan,currentLocation,currentZone);selectedVlan=null;await renderVlans();hideAddIpUI();}catch(e){alert(e.message);}}
}

renderVlans();
</script>
</body>
</html>

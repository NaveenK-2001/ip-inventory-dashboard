<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage IPs</title>
<link rel="stylesheet" href="style.css">
<script src="app.js" defer></script>
</head>
<body>
<div class="topbar"><h2>EXAMITY | IP Inventory Dashboard</h2></div>
<aside class="sidebar"><ul>
<li><a href="index.php">Dashboard</a></li>
<li><a href="inventory.php">IP Inventory</a></li>
<li><a href="reports.php">Reports</a></li>
<li class="active">Manage IPs</li>
</ul></aside>
<main class="content">
<h2 class="page-title">🔍 Search & Manage IPs</h2>
<section class="box"><div class="form-grid" style="grid-template-columns:repeat(4,1fr)">
<select id="sLocation" onchange="onLocationChange()"><option value="">Select Location</option></select>
<select id="sZone" onchange="onZoneChange()" disabled><option value="">Select Zone</option></select>
<select id="sVlan" disabled><option value="">Select VLAN</option></select>
<input id="sIp" placeholder="Search IP (optional)"></div><button style="margin-top:16px" onclick="searchIPs()">Search</button></section>
<section class="box" style="margin-top:20px"><h3>Results</h3><div style="overflow-x:auto"><table><thead><tr><th>IP</th><th>Hostname</th><th>Location</th><th>Zone</th><th>VLAN</th><th>Status</th><th>Owner</th><th>Actions</th></tr></thead><tbody id="resultsTable"></tbody></table></div></section>
</main>
<script>
let allIPs=[]; let searchResults=[];
async function initDropdowns(){ allIPs = await fetchIPs(); const locations=[...new Set(allIPs.map(i=>i.location))]; const s=document.getElementById('sLocation'); locations.forEach(l=>s.innerHTML += `<option value="${l}">${l}</option>`); }
function onLocationChange(){ const loc=sLocation.value; sZone.innerHTML='<option value="">Select Zone</option>'; sVlan.innerHTML='<option value="">Select VLAN</option>'; sVlan.disabled=true; if(!loc){sZone.disabled=true;return;} const zones=[...new Set(allIPs.filter(i=>i.location===loc).map(i=>i.zone))]; zones.forEach(z=>sZone.innerHTML += `<option value="${z}">${z}</option>`); sZone.disabled=false; }
function onZoneChange(){ const loc=sLocation.value, zone=sZone.value; sVlan.innerHTML='<option value="">Select VLAN</option>'; if(!zone){sVlan.disabled=true;return;} const vlans=[...new Set(allIPs.filter(i=>i.location===loc&&i.zone===zone).map(i=>String(i.vlan)))].sort((a,b)=>a-b); vlans.forEach(v=>sVlan.innerHTML += `<option value="${v}">VLAN ${v}</option>`); sVlan.disabled=false; }
function searchIPs(){ const loc=sLocation.value,zone=sZone.value,vlan=sVlan.value,q=sIp.value.trim(); searchResults = allIPs.filter(ip=>(!loc||ip.location===loc)&&(!zone||ip.zone===zone)&&(!vlan||String(ip.vlan)===vlan)&&(!q||ip.ip.includes(q))); renderResults(); }
function renderResults(){ const table=resultsTable; table.innerHTML=''; if(!searchResults.length){table.innerHTML='<tr><td colspan="8">No matching IPs found</td></tr>';return;} searchResults.forEach((ip,rowIdx)=>{table.innerHTML += `<tr><td><input value="${ip.ip}" disabled></td><td><input value="${ip.hostname||''}"></td><td><input value="${ip.location}" disabled></td><td><input value="${ip.zone}" disabled></td><td><input value="${ip.vlan}" disabled></td><td><select><option ${normalizeStatus(ip.status)==='Used'?'selected':''}>Used</option><option ${normalizeStatus(ip.status)==='Free'?'selected':''}>Free</option><option ${normalizeStatus(ip.status)==='Reserved'?'selected':''}>Reserved</option><option ${normalizeStatus(ip.status)==='Static'?'selected':''}>Static</option></select></td><td><input value="${ip.owner||''}"></td><td><button onclick="saveIP(${rowIdx})">Save</button><button onclick="deleteIP(${rowIdx})">Delete</button></td></tr>`; }); }
async function saveIP(rowIdx){ const row=resultsTable.rows[rowIdx]; const ip=searchResults[rowIdx]; try{ await updateIP(ip.id,{hostname:row.cells[1].querySelector('input').value,status:row.cells[5].querySelector('select').value,owner:row.cells[6].querySelector('input').value}); allIPs=await fetchIPs(); alert('IP updated successfully'); searchIPs(); }catch(e){alert(e.message);} }
async function deleteIP(rowIdx){ if(!confirm('Are you sure you want to delete this IP?')) return; const ip=searchResults[rowIdx]; try{ await deleteIPById(ip.id); allIPs=await fetchIPs(); searchIPs(); }catch(e){alert(e.message);} }
initDropdowns();
</script>
</body>
</html>

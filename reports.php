<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IP Reports</title>
<link rel="stylesheet" href="style.css">
<script src="app.js" defer></script>
</head>
<body>
<div class="topbar"><h2>EXAMITY | IP Inventory Dashboard</h2></div>
<aside class="sidebar"><ul>
<li><a href="index.php">Dashboard</a></li>
<li><a href="inventory.php">IP Inventory</a></li>
<li class="active">Reports</li>
<li><a href="manage.php">Manage IPs</a></li>
</ul></aside>
<main class="content">
<section class="report-section"><h3>📦 Export IP Inventory</h3><div class="report-controls"><button style="background:#16a34a;color:#fff" onclick="exportFullInventory()">Export Full Inventory</button></div></section>
<section class="report-section"><h3>🛠 Custom Report</h3><div class="report-controls"><select id="reportType"><option value="status">IP Status Summary</option><option value="location">Location-wise Report</option><option value="vlan">VLAN-wise Report</option><option value="used">Used IPs</option><option value="free">Free IPs</option></select><button onclick="runCustomReport()">Run Report</button></div></section>
<section class="report-section"><h3>📥 Import Inventory (Strict)</h3><div class="report-controls"><input type="file" id="csvFile" accept=".csv"><button onclick="importCSV()">Import CSV</button></div></section>
</main>
<script>
let ipData=[];
function downloadFile(content, filename){const blob=new Blob([content],{type:'text/csv'});const url=URL.createObjectURL(blob);const a=document.createElement('a');a.href=url;a.download=filename;a.click();URL.revokeObjectURL(url);}
function csvFromRows(headers, rows){let csv=headers.join(',')+'\n';rows.forEach(r=>{csv += headers.map(h=>`"${(r[h.toLowerCase()]||'').toString().replaceAll('"','""')}"`).join(',')+'\n';}); return csv;}
async function load(){ipData = await fetchIPs();}
function exportFullInventory(){if(!ipData.length){alert('No data available');return;} const headers=['IP','Hostname','Location','Zone','VLAN','Device','OS','Status','Owner']; downloadFile(csvFromRows(headers,ipData),'ip_full_inventory.csv');}
function runCustomReport(){const type=reportType.value; let rows=ipData, headers=['IP','Status'], filename='report.csv'; if(type==='status'){headers=['Status','IP'];rows=ipData.map(i=>({status:i.status,ip:i.ip}));filename='status_report.csv';} if(type==='location'){headers=['Location','IP'];rows=ipData.map(i=>({location:i.location,ip:i.ip}));filename='location_report.csv';} if(type==='vlan'){headers=['VLAN','IP'];rows=ipData.map(i=>({vlan:i.vlan,ip:i.ip}));filename='vlan_report.csv';} if(type==='used'){headers=['IP','Status'];rows=ipData.filter(i=>['used','static'].includes((i.status||'').toLowerCase())).map(i=>({ip:i.ip,status:'Used'}));filename='used_ips.csv';} if(type==='free'){headers=['IP','Status'];rows=ipData.filter(i=>(i.status||'').toLowerCase()==='free').map(i=>({ip:i.ip,status:'Free'}));filename='free_ips.csv';} let csv=headers.join(',')+'\n'; rows.forEach(r=>{csv += headers.map(h=>r[h.toLowerCase()]||'').join(',')+'\n';}); downloadFile(csv,filename);}
async function importCSV(){const file=csvFile.files[0]; if(!file){alert('Please select a CSV file.');return;} const text = await file.text(); const lines=text.split(/\r?\n/).filter(Boolean); const expected=['IP','Hostname','Location','Zone','VLAN','Device','OS','Status','Owner']; const header=lines[0].split(',').map(h=>h.replace(/"/g,'').trim()); if(expected.some((h,i)=>h!==header[i])){alert('Invalid header format'); return;} let count=0; for(let i=1;i<lines.length;i++){const cols=lines[i].split(',').map(v=>v.replace(/"/g,'').trim()); if(!cols[0]||!cols[2]||!cols[4]||!cols[7]){alert(`Line ${i+1}: required fields missing`);return;} await createIP({ip:cols[0],hostname:cols[1],location:cols[2],zone:cols[3],vlan:cols[4],device:cols[5],os:cols[6],status:cols[7],owner:cols[8]}); count++; }
await load(); alert(`Import successful. Rows added: ${count}`);}
load();
</script>
</body>
</html>

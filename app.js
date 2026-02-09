

/******************** DATA ********************/
let ipData = JSON.parse(localStorage.getItem("ipData") || "[]");
let vlanData = JSON.parse(localStorage.getItem("vlanData") || "[]");
let chart = null;
let barChart = null;


/******************** SAVE ********************/
function saveData() {
  localStorage.setItem("ipData", JSON.stringify(ipData));
}
function saveVlans() {
  localStorage.setItem("vlanData", JSON.stringify(vlanData));
}

/******************** STATUS NORMALIZER ********************/
function normalizeStatus(status) {
  if (!status) return "Used";
  const s = status.toString().trim().toLowerCase();
  if (s === "free") return "Free";
  if (s === "reserved") return "Reserved";
  if (s === "static") return "Static";   // ✅ ADD
  return "Used";
}


/******************** IP SORT HELPER ********************/
function compareIPs(a, b) {
  const pa = a.split(".").map(Number);
  const pb = b.split(".").map(Number);
  for (let i = 0; i < 4; i++) {
    if (pa[i] !== pb[i]) return pa[i] - pb[i];
  }
  return 0;
}

/* ======================================================
   DASHBOARD
   ====================================================== */

function loadDashboard() {
  if (!document.getElementById("totalIPs")) return;

  const used = ipData.filter(i => normalizeStatus(i.status) === "Used").length;
  const free = ipData.filter(i => normalizeStatus(i.status) === "Free").length;
  const reserved = ipData.filter(i => normalizeStatus(i.status) === "Reserved").length;
  const staticCount = ipData.filter(i => normalizeStatus(i.status) === "Static").length;


  totalIPs.innerText = ipData.length;
  usedIPs.innerText = used;
  freeIPs.innerText = free;
  reservedIPs.innerText = reserved;
  staticIPs.innerText = staticCount;

  drawPie(used, free, reserved, staticCount);
  renderLocationStats();
}

function drawPie(used, free, reserved, staticCount) {
  const ctx = document.getElementById("ipChart");
  if (!ctx || typeof Chart === "undefined") return;
  if (chart) chart.destroy();

  chart = new Chart(ctx, {
    type: "pie",
    data: {
      labels: ["Used", "Free", "Reserved", "Static"],
      datasets: [{
        data: [used, free, reserved, staticCount],
        backgroundColor: [
          "#4f46e5",
          "#10b981",
          "#facc15",
          "#dc2626"
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  });

  drawBarChart(used, free, reserved, staticCount);
}


function renderLocationStats() {
  const table = document.getElementById("locationStats");
  if (!table) return;
  table.innerHTML = "";

  ["HYDE", "HYDW"].forEach(loc => {
    const list = ipData.filter(i => i.location === loc);

    const used = list.filter(i => normalizeStatus(i.status) === "Used").length;
    const free = list.filter(i => normalizeStatus(i.status) === "Free").length;
    const reserved = list.filter(i => normalizeStatus(i.status) === "Reserved").length;

    // ✅ TEMP LOGIC
    const staticCount = list.filter(
  i => normalizeStatus(i.status) === "Static"
).length;


    table.innerHTML += `
      <tr>
        <td>${loc}</td>
        <td>${used}</td>
        <td>${free}</td>
        <td>${reserved}</td>
        <td>${staticCount}</td>
        <td>${list.length}</td>
      </tr>`;
  });
}


/* ======================================================
   INVENTORY
   ====================================================== */

/* ❌ NO DEFAULT VLANs */
function initDefaultVlans() {
  // intentionally empty
}

let currentLocation = "HYDE";
let currentZone = "Zone 1";
let selectedVlan = null;

function selectLocation(loc, btn) {
  currentLocation = loc;
  setActive(btn);
  hideAddIpUI();
  renderVlans();
}

function selectZone(zone, btn) {
  currentZone = zone;
  setActive(btn);
  hideAddIpUI();
  renderVlans();
}

function renderVlans() {
  const box = document.getElementById("vlanButtons");
  if (!box) return;

  box.innerHTML = "";

  vlanData
    .filter(v => v.location === currentLocation && v.zone === currentZone)
    .sort((a, b) => a.id - b.id)
    .forEach(v => {
      box.innerHTML += `
        <button class="vlan-btn" onclick="selectVlan(${v.id}, this)">
          VLAN ${v.id}
        </button>`;
    });
}

function selectVlan(vlan, btn) {
  selectedVlan = vlan;
  document.querySelectorAll(".vlan-btn").forEach(b => b.classList.remove("active"));
  btn.classList.add("active");

  addIpBtn.style.display = "inline-block";
  renderIpList();

  const v = vlanData.find(x =>
    x.id === vlan &&
    x.location === currentLocation &&
    x.zone === currentZone
  );
  vlanLabel.innerText = `VLAN ${vlan} : ${v?.name || "Unnamed VLAN"}`;
}

/* ---------- VLAN SETTINGS ---------- */

function openVlanSettings() {
  const c = prompt(
    "VLAN SETTINGS\n\n" +
    "1 = Add VLAN\n" +
    "2 = Edit VLAN ID\n" +
    "3 = Delete VLAN"
  );

  if (c === "1") {
    addVlan(); // ✅ allow first VLAN
    return;
  }

  if (selectedVlan === null) {
    alert("Select a VLAN first");
    return;
  }

  if (c === "2") editVlanId();
  else if (c === "3") deleteVlan();
}

function addVlan() {
  const idInput = prompt("VLAN ID (number)");
  if (!idInput || isNaN(idInput)) return alert("Valid VLAN ID required");

  const vlanId = Number(idInput);
  if (vlanData.some(v => v.id === vlanId && v.location === currentLocation && v.zone === currentZone))
    return alert("VLAN already exists");

  vlanData.push({
    id: vlanId,
    name: `VLAN ${vlanId}`,
    location: currentLocation,
    zone: currentZone
  });

  saveVlans();
  renderVlans();
}

function editVlanId() {
  const newIdInput = prompt("New VLAN ID", selectedVlan);
  if (!newIdInput || isNaN(newIdInput)) return alert("Numeric VLAN ID required");

  const newId = Number(newIdInput);
  if (vlanData.some(v => v.id === newId && v.location === currentLocation && v.zone === currentZone))
    return alert("VLAN ID already exists");

  vlanData.forEach(v => {
    if (v.id === selectedVlan && v.location === currentLocation && v.zone === currentZone)
      v.id = newId;
  });

  ipData.forEach(ip => {
    if (ip.location === currentLocation && ip.zone === currentZone && String(ip.vlan) === String(selectedVlan))
      ip.vlan = newId;
  });

  saveVlans();
  saveData();
  renderVlans();
  renderIpList();
}

function deleteVlan() {
  if (ipData.some(ip => ip.location === currentLocation && ip.zone === currentZone && String(ip.vlan) === String(selectedVlan)))
    return alert("Cannot delete VLAN with IPs");

  if (!confirm(`Delete VLAN ${selectedVlan}?`)) return;

  vlanData = vlanData.filter(v =>
    !(v.id === selectedVlan && v.location === currentLocation && v.zone === currentZone)
  );

  saveVlans();
  selectedVlan = null;
  renderVlans();
  hideAddIpUI();
}

function editVlanName() {
  if (!selectedVlan) return;
  const v = vlanData.find(x => x.id === selectedVlan && x.location === currentLocation && x.zone === currentZone);
  if (!v) return;

  const n = prompt("Edit VLAN Name", v.name);
  if (!n) return;

  v.name = n.trim();
  saveVlans();
  vlanLabel.innerText = `VLAN ${v.id} : ${v.name}`;
}

/* ---------- IP LIST ---------- */

function renderIpList() {
  const table = ipListTable;
  table.innerHTML = "";

  const list = ipData
    .filter(ip => ip.location === currentLocation && ip.zone === currentZone && String(ip.vlan) === String(selectedVlan))
    .sort((a, b) => compareIPs(a.ip, b.ip));

  if (!list.length) {
    table.innerHTML = `<tr><td colspan="9">No IPs</td></tr>`;
  } else {
    list.forEach(ip => {
      table.innerHTML += `
        <tr>
          <td>${ip.ip}</td>
          <td>${ip.hostname || ""}</td>
          <td>${ip.location}</td>
          <td>${ip.zone}</td>
          <td>${ip.vlan}</td>
          <td>${ip.device || ""}</td>
          <td>${ip.os || ""}</td>
          <td>${normalizeStatus(ip.status)}</td>
          <td>${ip.owner || ""}</td>
        </tr>`;
    });
  }
  ipListSection.style.display = "block";
}

/* ---------- ADD IP ---------- */

function addIpFromVlan() {
  const ipValue = document.getElementById("ip").value.trim();
  if (!ipValue) return alert("IP required");

  if (ipData.some(i =>
    i.ip === ipValue &&
    i.location === currentLocation &&
    i.zone === currentZone &&
    String(i.vlan) === String(selectedVlan)
  )) return alert("Duplicate IP");

  ipData.push({
    ip: ipValue,
    hostname: document.getElementById("hostname").value.trim(),
    location: currentLocation,
    zone: currentZone,
    vlan: selectedVlan,
    device: document.getElementById("device").value.trim(),
    os: document.getElementById("os").value.trim(),
    status: document.getElementById("status").value,
    owner: document.getElementById("owner").value.trim()
  });

  saveData();
  renderIpList();

  document.getElementById("ip").value = "";
  document.getElementById("hostname").value = "";
  document.getElementById("device").value = "";
  document.getElementById("os").value = "";
  document.getElementById("owner").value = "";
}

/* ======================================================
   MANAGE PAGE HELPERS
   ====================================================== */

function getIPData() {
  return JSON.parse(localStorage.getItem("ipData") || "[]");
}
function setIPData(data) {
  ipData = data;
  saveData();
}
function updateIPAtIndex(index, updatedIP) {
  if (!ipData[index]) return false;
  ipData[index] = updatedIP;
  saveData();
  return true;
}
function deleteIPAtIndex(index) {
  if (!ipData[index]) return false;
  ipData.splice(index, 1);
  saveData();
  return true;
}

/* ---------- UI HELPERS ---------- */

function showAddIpForm() {
  addIpForm.style.display = "block";
}
function hideAddIpUI() {
  addIpForm.style.display = "none";
  addIpBtn.style.display = "none";
  ipListSection.style.display = "none";
}
function setActive(btn) {
  btn.parentElement.querySelectorAll("button").forEach(b => b.classList.remove("active"));
  btn.classList.add("active");
}



function drawBarChart(used, free, reserved) {
  const total = ipData.length;

  // ✅ Static = Used (for now)
const staticCount = ipData.filter(
  i => normalizeStatus(i.status) === "Static"
).length;


  const ctx = document.getElementById("ipBarChart");

  if (barChart) barChart.destroy();

barChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["Total", "Used", "Free", "Reserved", "Static"],
      datasets: [{
        data: [total, used, free, reserved, staticCount],
        backgroundColor: [
          "#2563eb",
          "#16a34a",
          "#f59e0b",
          "#facc15",
          "#dc2626"
        ],
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



function syncVlansFromIPs() {
  let changed = false;

  ipData.forEach(ip => {
    if (!ip.vlan || !ip.location || !ip.zone) return;

    const exists = vlanData.some(v =>
      String(v.id) === String(ip.vlan) &&
      v.location === ip.location &&
      v.zone === ip.zone
    );

    if (!exists) {
      vlanData.push({
        id: Number(ip.vlan),
        name: `VLAN ${ip.vlan}`,
        location: ip.location,
        zone: ip.zone
      });
      changed = true;
    }
  });

  if (changed) {
    localStorage.setItem("vlanData", JSON.stringify(vlanData));
  }
}


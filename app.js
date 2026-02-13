async function apiFetch(url, options = {}) {
  const response = await fetch(url, {
    headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
    ...options
  });
  const data = await response.json();
  if (!response.ok) {
    throw new Error(data.error || 'Request failed');
  }
  return data;
}

function compareIPs(a, b) {
  const pa = a.split('.').map(Number);
  const pb = b.split('.').map(Number);
  for (let i = 0; i < 4; i += 1) {
    if (pa[i] !== pb[i]) return pa[i] - pb[i];
  }
  return 0;
}

function normalizeStatus(status) {
  const s = String(status || '').trim().toLowerCase();
  if (s === 'free') return 'Free';
  if (s === 'reserved') return 'Reserved';
  if (s === 'static') return 'Static';
  return 'Used';
}

async function fetchIPs(params = {}) {
  const query = new URLSearchParams(params);
  const data = await apiFetch(`api/ips.php?${query.toString()}`);
  return data.data || [];
}

async function createIP(payload) {
  return apiFetch('api/ips.php', {
    method: 'POST',
    body: JSON.stringify(payload)
  });
}

async function updateIP(id, payload) {
  return apiFetch(`api/ips.php?id=${id}`, {
    method: 'PUT',
    body: JSON.stringify(payload)
  });
}

async function deleteIPById(id) {
  return apiFetch(`api/ips.php?id=${id}`, { method: 'DELETE' });
}

async function fetchVlans(location, zone) {
  const query = new URLSearchParams({ location, zone });
  const data = await apiFetch(`api/vlans.php?${query.toString()}`);
  return data.data || [];
}

async function saveVlan(payload) {
  return apiFetch('api/vlans.php', {
    method: 'POST',
    body: JSON.stringify(payload)
  });
}

async function removeVlan(id, location, zone) {
  const query = new URLSearchParams({ id, location, zone });
  return apiFetch(`api/vlans.php?${query.toString()}`, { method: 'DELETE' });
}

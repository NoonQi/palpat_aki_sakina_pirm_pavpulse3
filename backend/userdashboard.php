<?php
session_start();
include("db.php");
if (!isset($_SESSION['user_id'])) {
    header("Location: userlogin.php");
    exit();
}
$user_name = $_SESSION['name'];
$user_email = $_SESSION['email'];
$user_role = $_SESSION['role'];
$user_id=$_SESSION['user_id'];
$countQuery=mysql_query("Select count(*) as total from reports where user_id ='$user_id'");
$countData = mysql_fetch_assoc($countQuery);
$reportCount=$countData['total'];
$reportsQuery = mysql_query("Select * from reports where user_id='$user_id' order by created_at DESC");
$reports=array();
while($row=mysql_fetch_assoc($reportsQuery))
  {
    $reports[]=$row;
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PIRM — Citizen Portal</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --asphalt: #24262b;
    --safety-orange: #e8590c;
    --route-blue: #1971c2;
    --fixed-green: #2f9e44;
    --caution-yellow: #f5b700;
  }
  body { font-family: 'Inter', sans-serif; background: #f2efe8; padding-bottom: 60px; }
  .pirm-display { font-family: 'Oswald', sans-serif; text-transform: uppercase; letter-spacing: 0.03em; }
  .navbar-pirm { background: var(--asphalt); }
  .navbar-pirm .navbar-brand { color: #fff; font-family: 'Oswald', sans-serif; font-weight: 600; letter-spacing: 0.02em; }
  .navbar-pirm .navbar-brand i { color: var(--safety-orange); }

  .pirm-hero { background: var(--asphalt); color: #fff; padding: 34px 0 42px; margin-bottom: -26px; }
  .pirm-hero h1 { font-size: 26px; }
  .pirm-hero p { color: #c9cbd1; font-size: 14px; max-width: 560px; }

  .pirm-form-card { border: none; border-radius: 12px; box-shadow: 0 8px 24px rgba(36,38,43,0.12); }
  .pirm-form-card .card-header { background: var(--safety-orange); color: #fff; font-weight: 600; border-radius: 12px 12px 0 0 !important; padding: 14px 20px; }
  .pirm-upload-preview { width: 56px; height: 56px; border-radius: 8px; object-fit: cover; display: none; }
  .btn-pirm-primary { background: var(--fixed-green); border-color: var(--fixed-green); font-weight: 600; }
  .btn-pirm-primary:hover { background: #268a3d; border-color: #268a3d; }
  .btn-pirm-geo { background: var(--safety-orange); border-color: var(--safety-orange); color: #fff; font-weight: 600; font-size: 13px; }
  .btn-pirm-geo:hover { background: #d14e0a; color: #fff; }

  .pirm-severity-Low { background: #868e96; }
  .pirm-severity-Medium { background: var(--caution-yellow); color: #24262b !important; }
  .pirm-severity-High { background: #f08c00; }
  .pirm-severity-Critical { background: #e03131; }
  .pirm-status-Reported { background: var(--caution-yellow); color: #24262b !important; }
  .pirm-status-Acknowledged { background: var(--route-blue); }
  .pirm-status-Fixed { background: var(--fixed-green); }

  .pirm-report-card { border: none; border-radius: 10px; box-shadow: 0 4px 14px rgba(36,38,43,0.08); overflow: hidden; transition: transform .12s ease; cursor: pointer; }
  .pirm-report-card:hover { transform: translateY(-2px); }
  .pirm-report-photo { height: 140px; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; font-size: 40px; color: #fff; }
  .pirm-upvote-btn { border-radius: 20px; font-weight: 700; }
  .pirm-upvote-btn.voted { background: var(--safety-orange); border-color: var(--safety-orange); color: #fff; }
  .pirm-progress-wrap .progress { height: 6px; border-radius: 4px; }
  .pirm-progress-labels { display: flex; justify-content: space-between; font-size: 9.5px; text-transform: uppercase; color: #868e96; margin-top: 3px; }

  .pirm-filter-bar { background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(36,38,43,0.06); padding: 14px 16px; }
  .pirm-empty-state { text-align: center; padding: 50px 20px; color: #868e96; }

  .auth-nav-btn { font-size: 13px; font-weight: 600; border-radius: 20px; padding: 6px 16px; text-decoration: none; }
</style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-pirm navbar-dark py-3 mb-4">
  <div class="container d-flex justify-content-between align-items-center">
    <a class="navbar-brand mb-0 h1 text-decoration-none" href="index.html">
      <i class="bi bi-cone-striped me-2"></i>PIRM Citizen Portal
    </a>

    <div class="d-flex align-items-center gap-2">
      <span class="badge bg-success rounded-pill px-3 py-2 d-none d-md-inline-block me-2">
        <i class="bi bi-broadcast-pin me-1"></i>Live tracking
      </span>
      
      <span id="pirm-user-badge" class="badge bg-secondary px-3 py-2 text-white">Welcome back,<?php echo(htmlspecialchars($user_name));?>!</span>
      <a href="logout.php" class="btn btn-outline-light btn-sm auth-nav-btn">
        <i class="bi bi-box-arrow-right me-1"></i>Log Out
      </a>
    </div>
  </div>
</nav>

<div class="pirm-hero">
  <div class="container">
    <h1 class="pirm-display" id="pirm-greeting">Report & Track Infrastructure Issues</h1>
    <p>Snap a photo, tag the location, and let your neighbors upvote it so the city knows what to fix first.</p>
    <div class="mt-2">
      <span class="badge bg-light text-dark px-3 py-2">
        <i class="bi bi-file-earmark-check me-1"></i>Reports you've submitted: <strong><?php echo($reportCount);?></strong>
      </span>
    </div>
  </div>
</div>

<div class="container mt-4">

  <!-- Report submission form -->
  <div class="card pirm-form-card mb-4">
    <div class="card-header"><i class="bi bi-camera-fill me-2"></i>Submit a New Report</div>
    <div class="card-body p-4">
      <form id="pirm-report-form" action="submit_report.php" method="post" enctype="multipart/form-data">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Title</label>
            <input type="text" class="form-control" id="pirm-in-title" name="title" placeholder="e.g. Large pothole near bus stop" required>
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-semibold">Severity</label>
            <select class="form-select" id="pirm-in-severity" name="severity" required>
              <option>Low</option><option selected>Medium</option><option>High</option><option>Critical</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-semibold">Road Type</label>
            <select class="form-select" id="pirm-in-roadtype" name="road_type" required>
              <option>Highway</option><option>Main Road</option><option selected>Residential Street</option><option>Service Lane</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label small fw-semibold">Description</label>
            <textarea class="form-control" id="pirm-in-desc" name="description" rows="2" placeholder="Add any details that would help — size, how long it's been there, nearby landmark..." required></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Photo <span class="text-danger">*</span></label>
            <div class="d-flex align-items-center gap-2">
              <input type="file" class="form-control" id="pirm-in-photo" name="photo" accept="image/*" capture="environment" required>
              <img id="pirm-upload-preview" class="pirm-upload-preview" alt="preview">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Location <span class="text-danger">*</span></label>
            <div class="d-flex align-items-center gap-2">
              <button type="button" class="btn btn-pirm-geo" id="pirm-geo-btn"><i class="bi bi-geo-alt-fill me-1"></i>Use My Location</button>
              <small id="pirm-geo-status" class="text-muted text-truncate">No location attached yet</small>
            </div>
          </div>
          <input type="hidden" name="latitude" id="prim-latitude">
          <input type="hidden" name="longitude" id="prim-longitude">
          <div class="col-12">
            <div id="pirm-form-error" class="text-danger small" style="display:none;"></div>
          </div>
          <div class="col-12 mt-2">
            <button type="submit" class="btn btn-pirm-primary text-white px-4"><i class="bi bi-send-fill me-1"></i>Submit Report</button>
            <small class="text-muted ms-2">Photo and location help the city verify and prioritize your report.</small>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Filter bar -->
  <div class="pirm-filter-bar mb-4">
    <div class="row g-2 align-items-center">
      <div class="col-md-5">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input type="text" class="form-control" id="pirm-search" placeholder="Search by title or location...">
        </div>
      </div>
      <div class="col-md-3">
        <select class="form-select" id="pirm-filter-status">
          <option value="All">All Statuses</option>
          <option>Reported</option><option>Acknowledged</option><option>Fixed</option>
        </select>
      </div>
      <div class="col-md-3">
        <select class="form-select" id="pirm-filter-severity">
          <option value="All">All Severities</option>
          <option>Low</option><option>Medium</option><option>High</option><option>Critical</option>
        </select>
      </div>
      <div class="col-md-1 text-md-end">
        <span class="badge bg-secondary" id="pirm-result-count"></span>
      </div>
    </div>
  </div>

  <!-- Reported Incidents Grid -->
  <div class="row g-4" id="pirm-report-grid"></div>
</div>

<!-- Detailed Incident Review Modal -->
<div class="modal fade" id="pirmDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" id="pirm-detail-body">
      <!-- Populated Dynamically -->
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>

<script>
  const reportsFromPHP=<?php echo json_encode($reports);?>;
  (function() {

  let reports = reportsFromPHP.map(function(r) {
    return {
      id: r.id,
      title: r.title,
      description: r.description,
      severity: r.severity,
      roadType: r.road_type,
      status: r.status,
      location: r.latitude + ', ' + r.longitude,
      upvotes: 0,
      date: r.created_at,
      photo: r.photo
    };
  });
  console.log("Reports from PHP:",reports);
  let filters = {
    status:'All',
    severity:'All',
    search:''
  };
  let votedIds=new Set();
  let pendingPhoto = null;
  let pendingCoords= null;

  function progressPercent(status) {
    return { 
      Reported: 15, 
      Acknowledged: 55, 
      Fixed: 100 }[status]|| 15;
  }
  function progressBarClass(status) {
    return { 
      Reported: 'bg-warning', 
      Acknowledged: 'bg-primary', 
      Fixed: 'bg-success' }[status]||'bg-warning';
  }

  function cardHTML(r) {
    return `
      <div class="col-md-6 col-lg-4">
        <div class="card pirm-report-card h-100" data-detail-id="${r.id}">
          <div class="pirm-report-photo" style="${r.photo ? `background-image:url('${r.photo}')` : 'background:#495057'}">
            ${r.photo ? '' : '<i class="bi bi-cone-striped"></i>'}
          </div>
          <div class="card-body">
            <div class="d-flex flex-wrap gap-1 mb-2">
              <span class="badge pirm-severity-${r.severity}">${r.severity}</span>
              <span class="badge pirm-status-${r.status}">${r.status}</span>
              <span class="badge bg-light text-dark border">${r.roadType}</span>
            </div>
            <h6 class="fw-bold mb-1">${r.title}</h6>
            <p class="small text-muted mb-2">${r.description || 'No additional details provided.'}</p>
            <p class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i>${r.location} &nbsp;·&nbsp; <i class="bi bi-clock me-1"></i>${r.date}</p>
            <div class="pirm-progress-wrap mb-3">
              <div class="progress"><div class="progress-bar ${progressBarClass(r.status)}" style="width:${progressPercent(r.status)}%"></div></div>
              <div class="pirm-progress-labels"><span>Reported</span><span>Ack'd</span><span>Fixed</span></div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <button class="btn btn-outline-secondary btn-sm pirm-upvote-btn ${votedIds.has(r.id) ? 'voted' : ''}" data-vote-id="${r.id}">
                <i class="bi bi-hand-thumbs-up-fill me-1"></i>${r.upvotes}
              </button>
              <small class="text-muted">#${r.id}</small>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  function render() {
    const filtered = reports.filter(function(r){
      if (filters.status !== 'All' && r.status !== filters.status) return false;
      if (filters.severity !== 'All' && r.severity !== filters.severity) return false;
      if (filters.search && !(r.title + r.location).toLowerCase().includes(filters.search.toLowerCase())) return false;
      return true;
    });
    const resultCount = document.getElementById('pirm-result-count');
    if(resultCount)
    {
      resultCount.textContent =
                `${filtered.length}/${reports.length}`;
    }
    const grid = document.getElementById('pirm-report-grid');
    if(!grid){
      console.error("Error: prim-report-grid was not found.");
      return;
    }
    if(filtered.length===0){
    grid.innerHTML = `<div class="col-12 pirm-empty-state"><i class="bi bi-inboxes fs-1 d-block mb-2"></i>No reports match your filters.</div>`;
}
else
{
  grid.innerHTML= filtered.map(cardHTML).join('');
}

    grid.querySelectorAll('.pirm-upvote-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const id = String(btn.getAttribute('data-vote-id'));
        const report = reports.find(function(r){ return String(r.id) === id});
        if(!report)
        {
          return;
        }
        if (votedIds.has(id)) { 
          votedIds.delete(id); 
        report.upvotes--; 
      }
        else {
           votedIds.add(id);
            report.upvotes ++;
           }
        render();
      });
    });

    grid.querySelectorAll('[data-detail-id]').forEach(function(card) 
    {
      card.addEventListener('click', function() {
        const id = String(card.getAttribute('data-detail-id'));
    openDetailModal(id);
    });
  });
}
  function openDetailModal(id) {
    const report = reports.find(function(r){

    return String(r.id) === String(id)});
    if (!report) 
      {return};

    const modalBody = document.getElementById('pirm-detail-body');
    if(!modalBody)
    {
      return;
    }
    modalBody.innerHTML = `
      <div class="modal-header">
        <h5 class="modal-title fw-bold">${report.title}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        ${report.photo ? `<img src="${report.photo}" class="img-fluid rounded mb-3" alt="Report image">` : ''}
        <p class="text-muted small">${report.description || 'No additional description provided.'}</p>
        <div class="row g-2">
          <div class="col-6"><small class="text-muted d-block">Report ID</small><strong>${report.id}</strong></div>
          <div class="col-6"><small class="text-muted d-block">Severity</small><span class="badge pirm-severity-${report.severity}">${report.severity}</span></div>
          <div class="col-6"><small class="text-muted d-block">Road Type</small><strong>${report.roadType}</strong></div>
          <div class="col-6"><small class="text-muted d-block">Status</small><span class="badge pirm-status-${report.status}">${report.status}</span></div>
          <div class="col-12 mt-2"><small class="text-muted d-block">Location</small><strong><i class="bi bi-geo-alt me-1"></i>${report.location}</strong></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    `;
const modalElement = document.getElementById('pirmDetailModal');
if(modalElement) {
    const detailModal = new bootstrap.Modal(modalElement);
    detailModal.show();
  }
  }
  // Filter/search listeners
 const search= document.getElementById('pirm-search');
 if(search)
  {search.addEventListener('input', function(e){ filters.search = e.target.value; render(); }); }
 const statusFilter =
        document.getElementById('pirm-filter-status');


    if (statusFilter) {

        statusFilter.addEventListener('change', function(e) {

            filters.status = e.target.value;

            render();

        });

    }
    const severityFilter =
        document.getElementById('pirm-filter-severity');


    if (severityFilter) {

        severityFilter.addEventListener('change', function(e) {

            filters.severity = e.target.value;

            render();

        });

    }

  // Photo upload preview
  const photoInput = document.getElementById('pirm-in-photo');
  if(photoInput){
    photoInput.addEventListener('change', function(e){
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev) {
      pendingPhoto = ev.target.result;
      const preview = document.getElementById('pirm-upload-preview');
     if(preview) {
      preview.src = pendingPhoto;
      preview.style.display = 'block';
    }};
    reader.readAsDataURL(file);
  });
  }
  // Geotag capture
  const geoButton = document.getElementById('pirm-geo-btn');
  if(geoButton){
    geoButton.addEventListener('click',function(){
    const statusEl = document.getElementById('pirm-geo-status');
    if (!navigator.geolocation) { statusEl.textContent = 'Geolocation not supported'; return; }
    statusEl.textContent = 'Detecting location...';
    navigator.geolocation.getCurrentPosition(
      function(pos)
      { pendingCoords = { 
        lat: pos.coords.latitude, 
      lng: pos.coords.longitude };
      const latitude = document.getElementById('prim-latitude');
      const longitude = document.getElementById('prim-longitude');
      if(latitude){
        latitude.value=pendingCoords.lat;}
        if(longitude){
          longitude.value=pendingCoords.lng;}
      statusEl.textContent = `📍 ${pendingCoords.lat.toFixed(4)}, ${pendingCoords.lng.toFixed(4)}`; 
    },
      function(){ statusEl.textContent = 'Location access denied'; },
      { enableHighAccuracy: true, timeout: 8000 }
    );
  });
}
  // Submit new report
  const reportForm = document.getElementById('pirm-report-form');
    if (reportForm) {

        reportForm.addEventListener(
            'submit',
            function(e) {

                const errorEl =
                    document.getElementById(
                        'pirm-form-error'
                    );
    const title = document.getElementById('pirm-in-title').value.trim();
    const photo =
        document.getElementById('pirm-in-photo').files[0];
        const latitude =
        document.getElementById('pirm-latitude').value;

    const longitude =
        document.getElementById('pirm-longitude').value;
    const severity = document.getElementById('pirm-in-severity').value;
    const roadType = document.getElementById('pirm-in-roadtype').value;
    const description = document.getElementById('pirm-in-desc').value.trim();

    if (!title) {
      e.preventDefault();
      errorEl.textContent = 'Please enter a title for the report.';
      errorEl.style.display = 'block';
      return;
    }
    if (!photo) {
      e.preventDefault();
      errorEl.textContent = 'A photo is required — please upload one before submitting.';
      errorEl.style.display = 'block';
      return;
    }
    if (!latitude||!longitude) {
      e.preventDefault();
      errorEl.textContent = 'Location is required — click "Use My Location" before submitting.';
      errorEl.style.display = 'block';
      return;
    }
    errorEl.style.display = 'none';
            }
);
    }

  render();
})();
</script>
</body>
</html>

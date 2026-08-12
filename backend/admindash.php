<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: userlogin.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: userdashboard.php");
    exit();
}

include("db.php");
$query = mysql_query("
    SELECT 
        r.*,
        u.name AS user_name,
        u.email AS user_email
    FROM reports r
    INNER JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
");

$reports = array();

while ($row = mysql_fetch_assoc($query)) {
    $reports[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PIRM Admin Console — Potholes</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<style>
  body { margin: 0; background: #e9e7e0; padding: 20px; }
  #pirm-admin-root {
    --asphalt: #24262b; --asphalt-soft: #3a3d44; --paper: #f6f4ee; --paper-line: #e4e0d6;
    --safety-orange: #e8590c; --route-blue: #1971c2; --fixed-green: #2f9e44; --caution-yellow: #f5b700;
    --rejected-red: #c92a2a; --sev-critical: #e03131; --sev-high: #f08c00; --sev-medium: #f5b700; --sev-low: #868e96;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: var(--paper); color: var(--asphalt); padding: 28px 22px 60px; border-radius: 10px;
    max-width: 960px; margin: 0 auto; box-sizing: border-box; position: relative;
  }
  #pirm-admin-root * { box-sizing: border-box; }
  .pirm-display { font-family: 'Oswald', 'Arial Narrow', sans-serif; text-transform: uppercase; letter-spacing: 0.03em; }
/*
  .pirm-login-wrap { max-width: 380px; margin: 40px auto; background: var(--asphalt); color: var(--paper); border-radius: 10px; padding: 32px 28px; text-align: center; }
  .pirm-login-badge { width: 52px; height: 52px; border-radius: 8px; background: var(--safety-orange); display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 14px; }
  .pirm-login-wrap h2 { margin: 0 0 4px; font-size: 20px; }
  .pirm-login-wrap p { font-size: 12.5px; color: #b8bac0; margin: 0 0 20px; }
  .pirm-login-wrap input { width: 100%; font-family: inherit; font-size: 13px; padding: 10px 12px; margin-bottom: 10px; border: none; border-radius: 6px; background: #fff; color: var(--asphalt); }
  .pirm-login-btn { width: 100%; font-family: 'Oswald', sans-serif; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; background: var(--safety-orange); color: #fff; border: none; padding: 11px; border-radius: 6px; cursor: pointer; margin-top: 4px; }
  .pirm-login-error { color: #ff8787; font-size: 12px; margin-top: 10px; min-height: 14px; }*/
 /* .pirm-login-hint { font-size: 11px; color: #7c7f87; margin-top: 16px; border-top: 1px solid #3a3d44; padding-top: 14px; }*/

  .pirm-header { display: flex; align-items: flex-end; justify-content: space-between; border-bottom: 4px solid var(--asphalt); padding-bottom: 14px; margin-bottom: 22px; flex-wrap: wrap; gap: 10px; }
  .pirm-header-left { display: flex; align-items: center; gap: 12px; }
  .pirm-badge-icon { width: 42px; height: 42px; border-radius: 6px; background: var(--safety-orange); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; box-shadow: 3px 3px 0 var(--asphalt); }
  .pirm-title { font-size: 24px; font-weight: 600; margin: 0; line-height: 1; }
  .pirm-subtitle { font-size: 12.5px; color: var(--asphalt-soft); margin-top: 4px; font-family: 'Inter', sans-serif; text-transform: none; letter-spacing: 0; }
  .pirm-header-right { display: flex; align-items: center; gap: 10px; }
  .pirm-live-tag { font-size: 11px; font-weight: 700; color: #fff; background: var(--fixed-green); padding: 4px 10px; border-radius: 20px; display: flex; align-items: center; gap: 6px; }
  .pirm-live-dot { width: 6px; height: 6px; border-radius: 50%; background: #fff; animation: pirm-pulse 1.6s infinite; }
  @keyframes pirm-pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }
  .pirm-logout-btn { font-family: inherit; font-size: 11.5px; font-weight: 700; background: none; border: 2px solid var(--asphalt); color: var(--asphalt); padding: 5px 12px; border-radius: 20px; cursor: pointer; }

  .pirm-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 12px; margin-bottom: 22px; }
  .pirm-stat { background: var(--asphalt); color: var(--paper); border-radius: 8px; padding: 14px 16px; position: relative; overflow: hidden; }
  .pirm-stat::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 5px; background: var(--stat-color, var(--safety-orange)); }
  .pirm-stat-num { font-family: 'Oswald', sans-serif; font-size: 28px; font-weight: 600; line-height: 1; }
  .pirm-stat-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; color: #b8bac0; margin-top: 4px; }
/*.pirm-analytics { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 26px; }
  .pirm-chart-card { background: #fff; border: 2px solid var(--asphalt); border-radius: 8px; padding: 14px; }
  .pirm-chart-title { font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; color: var(--asphalt-soft); margin-bottom: 8px; }
  .pirm-chart-card canvas { max-height: 160px; }*/

  .pirm-controls { display: flex; gap: 10px; margin-bottom: 18px; flex-wrap: wrap; align-items: center; }
  .pirm-controls input, .pirm-controls select { font-family: inherit; font-size: 13px; padding: 8px 12px; border: 2px solid var(--asphalt); border-radius: 6px; background: #fff; color: var(--asphalt); }
  .pirm-controls input { flex: 1; min-width: 160px; }
  .pirm-result-count { font-size: 12px; color: var(--asphalt-soft); margin-left: auto; }

  .pirm-list { display: flex; flex-direction: column; gap: 14px; }
  .pirm-card { background: #fff; border: 2px solid var(--asphalt); border-radius: 8px; display: grid; grid-template-columns: 90px 1fr auto; gap: 16px; padding: 14px; position: relative; align-items: center; cursor: pointer; }
  .pirm-card:hover { background: #fcfbf8; }
  .pirm-card.pirm-rejected { opacity: 0.6; }
  .pirm-card::before { content: ''; position: absolute; left: 106px; top: 0; bottom: 0; width: 0; border-left: 2px dashed var(--paper-line); }
  .pirm-photo { width: 90px; height: 74px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 26px; color: #fff; flex-shrink: 0; background-size: cover; background-position: center; }
  .pirm-card-main { min-width: 0; }
  .pirm-card-top { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px; }
  .pirm-card-title { font-weight: 600; font-size: 15px; }
  .pirm-tag { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; padding: 2px 8px; border-radius: 20px; color: #fff; white-space: nowrap; }
  .pirm-meta { font-size: 12px; color: var(--asphalt-soft); display: flex; gap: 14px; flex-wrap: wrap; margin-top: 6px; }

  .pirm-track { display: flex; align-items: center; margin-top: 10px; max-width: 260px; }
  .pirm-track-seg { flex: 1; height: 4px; background: var(--paper-line); }
  .pirm-track-seg.filled { background: var(--asphalt); }
  .pirm-track-dot { width: 12px; height: 12px; border-radius: 50%; background: var(--paper-line); border: 2px solid var(--asphalt); flex-shrink: 0; z-index: 1; }
  .pirm-track-dot.active { background: var(--safety-orange); }
  .pirm-track-dot.done { background: var(--fixed-green); }
  .pirm-track-labels { display: flex; justify-content: space-between; max-width: 260px; margin-top: 3px; }
  .pirm-track-labels span { font-size: 9px; color: var(--asphalt-soft); text-transform: uppercase; width: 60px; text-align: center; }
  .pirm-track-labels span:first-child { text-align: left; width: 40px; }
  .pirm-track-labels span:last-child { text-align: right; width: 40px; }

  .pirm-card-actions { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
  .pirm-upvote { display: flex; align-items: center; gap: 5px; font-size: 13px; font-weight: 700; color: var(--asphalt); background: var(--paper); border: 2px solid var(--asphalt); border-radius: 20px; padding: 4px 12px; cursor: pointer; font-family: inherit; }
  .pirm-upvote.voted { background: var(--safety-orange); color: #fff; border-color: var(--safety-orange); }
  .pirm-view-btn { font-family: inherit; font-size: 11px; font-weight: 700; text-transform: uppercase; background: var(--asphalt); color: #fff; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
  .pirm-empty { text-align: center; padding: 40px 20px; color: var(--asphalt-soft); font-size: 13px; }

  .pirm-modal-overlay { position: fixed; inset: 0; background: rgba(20,20,22,0.65); display: none; align-items: center; justify-content: center; z-index: 999; padding: 20px; }
  .pirm-modal-overlay.open { display: flex; }
  .pirm-modal { background: #fff; border-radius: 10px; max-width: 480px; width: 100%; max-height: 88vh; overflow-y: auto; padding: 22px; position: relative; }
  .pirm-modal-close { position: absolute; top: 14px; right: 16px; background: none; border: none; font-size: 20px; cursor: pointer; color: var(--asphalt-soft); }
  .pirm-modal-photo { width: 100%; height: 180px; border-radius: 8px; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; font-size: 40px; color: #fff; margin-bottom: 14px; }
  .pirm-modal h3 { margin: 0 0 6px; font-size: 18px; }
  .pirm-modal-desc { font-size: 13px; color: var(--asphalt-soft); margin-bottom: 10px; }
  .pirm-modal-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px; margin-bottom: 14px; }
  .pirm-modal-meta-grid div { background: var(--paper); border-radius: 6px; padding: 8px 10px; }
  .pirm-modal-meta-grid b { display: block; font-size: 9.5px; text-transform: uppercase; color: var(--asphalt-soft); margin-bottom: 2px; }
  .pirm-modal label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; color: var(--asphalt-soft); display: block; margin-bottom: 5px; margin-top: 12px; }
  .pirm-modal select, .pirm-modal textarea { width: 100%; font-family: inherit; font-size: 13px; padding: 9px 11px; border: 2px solid var(--paper-line); border-radius: 6px; }
  .pirm-modal textarea { resize: vertical; min-height: 60px; }
  .pirm-rejected-banner { background: var(--rejected-red); color: #fff; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 6px 10px; border-radius: 6px; margin-bottom: 10px; }
  .pirm-modal-actions { display: flex; gap: 10px; margin-top: 18px; flex-wrap: wrap; }
  .pirm-modal-btn { font-family: inherit; font-size: 12.5px; font-weight: 700; padding: 9px 14px; border-radius: 6px; border: none; cursor: pointer; flex: 1; }
  .pirm-btn-save { background: var(--asphalt); color: #fff; }
  .pirm-btn-reject { background: var(--rejected-red); color: #fff; }
  .pirm-btn-unreject { background: var(--route-blue); color: #fff; }
  .pirm-btn-delete { background: #fff; color: var(--rejected-red); border: 2px solid var(--rejected-red); }
.pirm-btn-gov {
    background: var(--route-blue);
    color: #fff;
}
  @media (max-width: 700px) { .pirm-analytics { grid-template-columns: 1fr; } }
  @media (max-width: 560px) {
    .pirm-modal-meta-grid { grid-template-columns: 1fr; }
    .pirm-card { grid-template-columns: 60px 1fr; }
    .pirm-card::before { left: 76px; }
    .pirm-photo { width: 60px; height: 54px; font-size: 18px; }
    .pirm-card-actions { grid-column: 1 / -1; flex-direction: row; justify-content: space-between; margin-top: 6px; }
  }
</style>
</head>
<body>
<div id="pirm-admin-root"></div>
<script>
const reportsFromPHP = <?php echo json_encode($reports); ?>;
(function () {

    // ==========================================
    // BASIC SETTINGS
    // ==========================================

    const root = document.getElementById('pirm-admin-root');

    if (!root) {
        console.error('pirm-admin-root was not found.');
        return;
    }


    // ==========================================
    // STATUS / SEVERITY
    // ==========================================

    const STATUSES = [
        'Reported',
        'Acknowledged',
        'Fixed'
    ];

    const SEVERITIES = [
        'Low',
        'Medium',
        'High',
        'Critical'
    ];

    const SEVERITY_COLOR = {
        Low: '#868e96',
        Medium: '#f0ad00',
        High: '#ff8c00',
        Critical: '#e63946'
    };


    // ==========================================
    // PHP DATA
    // ==========================================

    const phpReports =
        typeof reportsFromPHP !== 'undefined'
            ? reportsFromPHP
            : [];

    console.log('Reports received from PHP:', phpReports);


    // ==========================================
    // CONVERT PHP DATA INTO JAVASCRIPT DATA
    // ==========================================

    let reports = phpReports.map(function (r) {

        return {

            id: String(r.id || ''),

            title: r.title || '',

            description: r.description || '',

            severity: r.severity || 'Medium',

            roadType: r.road_type || '',

            status: r.status || 'Reported',

            location:
                (r.latitude || '') +
                ', ' +
                (r.longitude || ''),

            latitude: r.latitude || '',

            longitude: r.longitude || '',

            upvotes: Number(r.upvotes || 0),

            date: r.created_at || '',

            photo: r.photo || '',

            rejected:
                Number(r.rejected || 0) === 1 ||
                r.rejected === true

        };

    });

    console.log('Admin reports:', reports);


    // ==========================================
    // FILTERS
    // ==========================================

    let filters = {

        status: 'All',

        severity: 'All',

        search: ''

    };


    // ==========================================
    // OTHER VARIABLES
    // ==========================================

    let votedIds = new Set();

    let activeModalId = null;


    // ==========================================
    // STATISTICS
    // ==========================================

    function statCounts() {

        return {

            Reported:
                reports.filter(function (r) {

                    return !r.rejected &&
                        r.status === 'Reported';

                }).length,


            Acknowledged:
                reports.filter(function (r) {

                    return !r.rejected &&
                        r.status === 'Acknowledged';

                }).length,


            Fixed:
                reports.filter(function (r) {

                    return !r.rejected &&
                        r.status === 'Fixed';

                }).length,


            Rejected:
                reports.filter(function (r) {

                    return r.rejected;

                }).length

        };

    }


    // ==========================================
    // PROGRESS TRACK
    // ==========================================

    function trackHTML(status) {

        const acknowledgedDone =
            status === 'Acknowledged' ||
            status === 'Fixed';

        const fixedDone =
            status === 'Fixed';


        return `

            <div class="pirm-track">

                <div class="pirm-track-seg filled"></div>

                <div class="pirm-track-dot active"></div>

                <div class="pirm-track-seg ${
                    acknowledgedDone ? 'filled' : ''
                }"></div>

                <div class="pirm-track-dot ${
                    acknowledgedDone ? 'done' : ''
                }"></div>

                <div class="pirm-track-seg ${
                    fixedDone ? 'filled' : ''
                }"></div>

                <div class="pirm-track-dot ${
                    fixedDone ? 'done' : ''
                }"></div>

            </div>


            <div class="pirm-track-labels">

                <span>Reported</span>

                <span>Ack'd</span>

                <span>Fixed</span>

            </div>

        `;

    }


    // ==========================================
    // MODAL HTML
    // ==========================================

    function modalHTML() {

        const report = reports.find(function (r) {

            return String(r.id) ===
                String(activeModalId);

        });


        if (!report) {

            return '';

        }


        const photoStyle = report.photo

            ? `background-image:url('${report.photo}')`

            : `background:${
                SEVERITY_COLOR[report.severity] ||
                '#868e96'
            }`;


        return `

            <div
                class="pirm-modal-overlay open"
                id="pirm-modal-overlay"
            >

                <div class="pirm-modal">


                    <!-- CLOSE -->

                    <button
                        class="pirm-modal-close"
                        id="pirm-modal-close"
                        type="button"
                    >
                        ×
                    </button>


                    <!-- REJECTED MESSAGE -->

                    ${
                        report.rejected

                        ?

                        `
                        <div class="pirm-rejected-banner">

                            This report has been rejected

                        </div>
                        `

                        :

                        ''
                    }


                    <!-- PHOTO -->

                    <div
                        class="pirm-modal-photo"
                        style="${photoStyle}"
                    >

                        ${
                            report.photo
                                ? ''
                                : '🕳️'
                        }

                    </div>


                    <!-- TITLE -->

                    <h3>

                        ${report.title}

                    </h3>


                    <!-- DESCRIPTION -->

                    <div class="pirm-modal-desc">

                        ${
                            report.description ||
                            'No description provided.'
                        }

                    </div>


                    <!-- INFORMATION -->

                    <div class="pirm-modal-meta-grid">


                        <div>

                            <b>Report ID</b>

                            #${report.id}

                        </div>


                        <div>

                            <b>Severity</b>

                            ${report.severity}

                        </div>


                        <div>

                            <b>Road Type</b>

                            ${report.roadType || 'Not specified'}

                        </div>


                        <div>

                            <b>Location</b>

                            ${report.location}

                        </div>


                        <div>

                            <b>Date</b>

                            ${report.date}

                        </div>


                        <div>

                            <b>Upvotes</b>

                            ${report.upvotes}

                        </div>


                    </div>


                    <!-- STATUS -->

                    <label for="pirm-modal-status">

                        Update Status

                    </label>


                    <select id="pirm-modal-status">

                        ${STATUSES.map(function (status) {

                            return `

                                <option
                                    value="${status}"
                                    ${
                                        report.status === status
                                            ? 'selected'
                                            : ''
                                    }
                                >

                                    ${status}

                                </option>

                            `;

                        }).join('')}

                    </select>


                    <!-- ACTIONS -->

                    <div class="pirm-modal-actions">


                        <!-- GOVERNMENT -->

                        <button
                            type="button"
                            class="pirm-modal-btn pirm-btn-gov"
                            id="pirm-modal-gov"
                        >

                            Submit to Government

                        </button>


                        <!-- SAVE -->

                        <button
                            type="button"
                            class="pirm-modal-btn pirm-btn-save"
                            id="pirm-modal-save"
                        >

                            Save Changes

                        </button>


                        <!-- REJECT / UNREJECT -->

                        ${
                            report.rejected

                            ?

                            `
                            <button
                                type="button"
                                class="pirm-modal-btn pirm-btn-unreject"
                                id="pirm-modal-unreject"
                            >

                                Un-reject

                            </button>
                            `

                            :

                            `
                            <button
                                type="button"
                                class="pirm-modal-btn pirm-btn-reject"
                                id="pirm-modal-reject"
                            >

                                Reject Report

                            </button>
                            `
                        }


                        <!-- DELETE -->

                        <button
                            type="button"
                            class="pirm-modal-btn pirm-btn-delete"
                            id="pirm-modal-delete"
                        >

                            Delete

                        </button>


                    </div>


                </div>

            </div>

        `;

    }


    // ==========================================
    // RENDER DASHBOARD
    // ==========================================

    function render() {

        const counts = statCounts();


        // ======================================
        // TOTAL UPVOTES
        // ======================================

        const totalUpvotes = reports.reduce(

            function (sum, r) {

                return sum +
                    Number(r.upvotes || 0);

            },

            0

        );


        // ======================================
        // ACTIVE REPORTS
        // ======================================

        const activeCount =
            reports.filter(function (r) {

                return !r.rejected;

            }).length;


        // ======================================
        // FILTER REPORTS
        // ======================================

        const filtered =
            reports.filter(function (r) {


                // STATUS FILTER

                if (

                    filters.status !== 'All' &&

                    (
                        r.rejected ||
                        r.status !== filters.status
                    )

                ) {

                    return false;

                }


                // SEVERITY FILTER

                if (

                    filters.severity !== 'All' &&

                    r.severity !== filters.severity

                ) {

                    return false;

                }


                // SEARCH

                if (filters.search) {

                    const searchText = (

                        r.title +
                        ' ' +
                        r.location +
                        ' ' +
                        r.roadType

                    ).toLowerCase();


                    if (

                        !searchText.includes(
                            filters.search.toLowerCase()
                        )

                    ) {

                        return false;

                    }

                }


                return true;

            });


        // ======================================
        // DASHBOARD
        // ======================================

        root.innerHTML = `


            <!-- HEADER -->

            <div class="pirm-header">


                <div class="pirm-header-left">


                    <div class="pirm-badge-icon">

                        🕳️

                    </div>


                    <div>

                        <p class="pirm-title pirm-display">

                            PIRM Admin — Potholes

                        </p>


                        <p class="pirm-subtitle">

                            Pothole Reporting Map —
                            municipal review panel

                        </p>

                    </div>


                </div>


                <div class="pirm-header-right">


                    <div class="pirm-live-tag">

                        <span class="pirm-live-dot"></span>

                        ${activeCount} active

                    </div>


                    <button
                        type="button"
                        class="pirm-logout-btn"
                        id="pirm-logout"
                    >

                        Log Out

                    </button>


                </div>


            </div>



            <!-- STATISTICS -->

            <div class="pirm-stats">


                <!-- REPORTED -->

                <div
                    class="pirm-stat"
                    style="--stat-color: var(--caution-yellow)"
                >

                    <div class="pirm-stat-num">

                        ${counts.Reported}

                    </div>

                    <div class="pirm-stat-label">

                        Reported

                    </div>

                </div>


                <!-- ACKNOWLEDGED -->

                <div
                    class="pirm-stat"
                    style="--stat-color: var(--route-blue)"
                >

                    <div class="pirm-stat-num">

                        ${counts.Acknowledged}

                    </div>

                    <div class="pirm-stat-label">

                        Acknowledged

                    </div>

                </div>


                <!-- FIXED -->

                <div
                    class="pirm-stat"
                    style="--stat-color: var(--fixed-green)"
                >

                    <div class="pirm-stat-num">

                        ${counts.Fixed}

                    </div>

                    <div class="pirm-stat-label">

                        Fixed

                    </div>

                </div>


                <!-- REJECTED -->

                <div
                    class="pirm-stat"
                    style="--stat-color: var(--rejected-red)"
                >

                    <div class="pirm-stat-num">

                        ${counts.Rejected}

                    </div>

                    <div class="pirm-stat-label">

                        Rejected

                    </div>

                </div>


                <!-- UPVOTES -->

                <div
                    class="pirm-stat"
                    style="--stat-color: var(--safety-orange)"
                >

                    <div class="pirm-stat-num">

                        ${totalUpvotes}

                    </div>

                    <div class="pirm-stat-label">

                        Upvotes

                    </div>

                </div>


                <!-- RESOLVED -->

                <div
                    class="pirm-stat"
                    style="--stat-color: #868e96"
                >

                    <div class="pirm-stat-num">

                        ${
                            activeCount

                                ?

                                Math.round(
                                    (
                                        counts.Fixed /
                                        activeCount
                                    ) * 100
                                )

                                :

                                0
                        }%

                    </div>

                    <div class="pirm-stat-label">

                        Resolved

                    </div>

                </div>


            </div>



            <!-- SEARCH / FILTERS -->

            <div class="pirm-controls">


                <input
                    id="pirm-search"
                    type="text"
                    placeholder="Search by title or location..."
                    value="${filters.search}"
                >


                <!-- STATUS -->

                <select id="pirm-filter-status">

                    <option
                        value="All"
                        ${
                            filters.status === 'All'
                                ? 'selected'
                                : ''
                        }
                    >

                        All

                    </option>


                    ${STATUSES.map(function (s) {

                        return `

                            <option
                                value="${s}"
                                ${
                                    filters.status === s
                                        ? 'selected'
                                        : ''
                                }
                            >

                                ${s}

                            </option>

                        `;

                    }).join('')}

                </select>


                <!-- SEVERITY -->

                <select id="pirm-filter-severity">

                    <option
                        value="All"
                        ${
                            filters.severity === 'All'
                                ? 'selected'
                                : ''
                        }
                    >

                        All

                    </option>


                    ${SEVERITIES.map(function (s) {

                        return `

                            <option
                                value="${s}"
                                ${
                                    filters.severity === s
                                        ? 'selected'
                                        : ''
                                }
                            >

                                ${s}

                            </option>

                        `;

                    }).join('')}

                </select>


                <span class="pirm-result-count">

                    ${filtered.length}
                    of
                    ${reports.length}
                    shown

                </span>


            </div>



            <!-- REPORT LIST -->

            <div class="pirm-list">


                ${
                    filtered.length === 0

                    ?

                    `
                    <div class="pirm-empty">

                        No reports match these filters.

                    </div>
                    `

                    :

                    filtered.map(function (r) {

                        const photoStyle = r.photo

                            ? `background-image:url('${r.photo}')`

                            : `background:${
                                SEVERITY_COLOR[r.severity]
                                || '#868e96'
                            }`;


                        return `

                            <div
                                class="
                                    pirm-card
                                    ${
                                        r.rejected
                                            ? 'pirm-rejected'
                                            : ''
                                    }
                                "
                                data-open-id="${r.id}"
                            >


                                <!-- PHOTO -->

                                <div
                                    class="pirm-photo"
                                    style="${photoStyle}"
                                >

                                    ${
                                        r.photo
                                            ? ''
                                            : '🕳️'
                                    }

                                </div>


                                <!-- MAIN -->

                                <div class="pirm-card-main">


                                    <div class="pirm-card-top">


                                        <span class="pirm-card-title">

                                            ${r.title}

                                        </span>


                                        <!-- SEVERITY -->

                                        <span
                                            class="pirm-tag"
                                            style="
                                                background:
                                                ${
                                                    SEVERITY_COLOR[
                                                        r.severity
                                                    ] || '#868e96'
                                                }
                                            "
                                        >

                                            ${r.severity}

                                        </span>


                                        <!-- ROAD -->

                                        <span
                                            class="pirm-tag"
                                            style="
                                                background:
                                                var(--asphalt-soft)
                                            "
                                        >

                                            ${
                                                r.roadType ||
                                                'Road'
                                            }

                                        </span>


                                        <!-- REJECTED -->

                                        ${
                                            r.rejected

                                            ?

                                            `
                                            <span
                                                class="pirm-tag"
                                                style="
                                                    background:
                                                    var(--rejected-red)
                                                "
                                            >

                                                Rejected

                                            </span>
                                            `

                                            :

                                            ''
                                        }


                                    </div>


                                    <!-- META -->

                                    <div class="pirm-meta">


                                        <span>

                                            📍 ${r.location}

                                        </span>


                                        <span>

                                            🕐 ${r.date}

                                        </span>


                                        <span>

                                            #${r.id}

                                        </span>


                                    </div>


                                    <!-- PROGRESS -->

                                    ${
                                        !r.rejected
                                            ? trackHTML(r.status)
                                            : ''
                                    }


                                </div>


                                <!-- ACTIONS -->

                                <div class="pirm-card-actions">


                                    <!-- UPVOTE -->

                                    <button
                                        type="button"
                                        class="
                                            pirm-upvote
                                            ${
                                                votedIds.has(r.id)
                                                    ? 'voted'
                                                    : ''
                                            }
                                        "
                                        data-vote-id="${r.id}"
                                    >

                                        ▲ ${r.upvotes}

                                    </button>


                                    <!-- VIEW -->

                                    <button
                                        type="button"
                                        class="pirm-view-btn"
                                        data-open-id="${r.id}"
                                    >

                                        View Details

                                    </button>


                                </div>


                            </div>

                        `;

                    }).join('')

                }


            </div>


            <!-- MODAL -->

            ${modalHTML()}


        `;


        attachEvents();

    }


    // ==========================================
    // EVENT LISTENERS
    // ==========================================

    function attachEvents() {


        // ======================================
        // LOGOUT
        // ======================================

        const logoutBtn =
            document.getElementById('pirm-logout');


        if (logoutBtn) {

            logoutBtn.addEventListener(
                'click',
                function () {

                    window.location.href =
                        'logout.php';

                }
            );

        }


        // ======================================
        // SEARCH
        // ======================================

        const search =
            document.getElementById('pirm-search');


        if (search) {

            search.addEventListener(
                'input',
                function (e) {

                    filters.search =
                        e.target.value;

                    render();

                }
            );

        }


        // ======================================
        // STATUS FILTER
        // ======================================

        const statusFilter =
            document.getElementById(
                'pirm-filter-status'
            );


        if (statusFilter) {

            statusFilter.addEventListener(
                'change',
                function (e) {

                    filters.status =
                        e.target.value;

                    render();

                }
            );

        }


        // ======================================
        // SEVERITY FILTER
        // ======================================

        const severityFilter =
            document.getElementById(
                'pirm-filter-severity'
            );


        if (severityFilter) {

            severityFilter.addEventListener(
                'change',
                function (e) {

                    filters.severity =
                        e.target.value;

                    render();

                }
            );

        }


        // ======================================
        // UPVOTE
        // ======================================

        root
            .querySelectorAll('.pirm-upvote')
            .forEach(function (btn) {

                btn.addEventListener(
                    'click',
                    function (e) {

                        e.stopPropagation();


                        const id =
                            String(
                                btn.getAttribute(
                                    'data-vote-id'
                                )
                            );


                        const report =
                            reports.find(
                                function (r) {

                                    return String(r.id) === id;

                                }
                            );


                        if (!report) {

                            return;

                        }


                        if (votedIds.has(id)) {

                            votedIds.delete(id);

                            report.upvotes =
                                Math.max(
                                    0,
                                    report.upvotes - 1
                                );

                        }

                        else {

                            votedIds.add(id);

                            report.upvotes++;

                        }


                        render();

                    }
                );

            });



        // ======================================
        // OPEN REPORT
        // ======================================

        root
            .querySelectorAll('[data-open-id]')
            .forEach(function (element) {

                element.addEventListener(
                    'click',
                    function (e) {


                        if (
                            e.target.closest(
                                '.pirm-upvote'
                            )
                        ) {

                            return;

                        }


                        activeModalId =
                            String(
                                element.getAttribute(
                                    'data-open-id'
                                )
                            );


                        render();

                    }
                );

            });



        // ======================================
        // MODAL
        // ======================================

        const overlay =
            document.getElementById(
                'pirm-modal-overlay'
            );


        if (!overlay) {

            return;

        }


        // ======================================
        // CLOSE OUTSIDE
        // ======================================

        overlay.addEventListener(
            'click',
            function (e) {

                if (e.target === overlay) {

                    activeModalId = null;

                    render();

                }

            }
        );


        // ======================================
        // CLOSE BUTTON
        // ======================================

        const closeBtn =
            document.getElementById(
                'pirm-modal-close'
            );


        if (closeBtn) {

            closeBtn.addEventListener(
                'click',
                function () {

                    activeModalId = null;

                    render();

                }
            );

        }



        // ======================================
        // SUBMIT TO GOVERNMENT
        // ======================================

        const govBtn =
            document.getElementById(
                'pirm-modal-gov'
            );


        if (govBtn) {

            govBtn.addEventListener(
                'click',
                function () {

                    const report =
                        reports.find(
                            function (r) {

                                return String(r.id) ===
                                    String(activeModalId);

                            }
                        );


                    if (!report) {

                        return;

                    }


                    const governmentUrl =
                        'https://iwms.punecorporation.org/site/login';


                    window.open(
                        governmentUrl,
                        '_blank'
                    );

                }
            );

        }



        // ======================================
        // SAVE CHANGES
        // ======================================

        const saveBtn =
            document.getElementById(
                'pirm-modal-save'
            );


        if (saveBtn) {

            saveBtn.addEventListener(
                'click',
                function () {


                    const report =
                        reports.find(
                            function (r) {

                                return String(r.id) ===
                                    String(activeModalId);

                            }
                        );


                    if (!report) {

                        alert('Report not found.');

                        return;

                    }


                    // Get only STATUS

                    const statusSelect =
                        document.getElementById(
                            'pirm-modal-status'
                        );


                    const newStatus =
                        statusSelect
                            ? statusSelect.value
                            : report.status;


                    console.log(
                        'Updating report:',
                        {
                            id: report.id,
                            status: newStatus
                        }
                    );


                    // ==================================
                    // SEND ONLY ID + STATUS
                    // ==================================

                    fetch(
                        'update_report.php',
                        {

                            method: 'POST',

                            headers: {

                                'Content-Type':
                                    'application/x-www-form-urlencoded'

                            },

                            body:
                                'id=' +
                                encodeURIComponent(
                                    report.id
                                ) +

                                '&status=' +
                                encodeURIComponent(
                                    newStatus
                                )

                        }
                    )


                    .then(function (response) {

                        console.log(
                            'HTTP status:',
                            response.status
                        );

                        return response.text();

                    })


                    .then(function (text) {

                        console.log(
                            'Server response:',
                            text
                        );


                        // ==================================
                        // CONVERT RESPONSE TO JSON
                        // ==================================

                        let data;


                        try {

                            data =
                                JSON.parse(text);

                        }

                        catch (error) {

                            console.error(
                                'Server did not return JSON:',
                                text
                            );


                            alert(
                                'update_report.php did not return valid JSON.\n\n' +
                                'Check the browser console for the server response.'
                            );


                            return;

                        }


                        // ==================================
                        // SUCCESS
                        // ==================================

                        if (data.success) {


                            // Update local report

                            report.status =
                                newStatus;


                            alert(
                                'Report updated successfully.'
                            );


                            activeModalId =
                                null;


                            render();

                        }

                        else {


                            alert(
                                'Failed to update report:\n' +
                                (
                                    data.message ||
                                    'Unknown error'
                                )
                            );

                        }

                    })


                    .catch(function (error) {

                        console.error(
                            'Fetch error:',
                            error
                        );


                        alert(
                            'Could not connect to update_report.php.\n\n' +
                            'Check that the file exists and that PHP is running.'
                        );

                    });

                }
            );

        }



        // ======================================
        // REJECT
        // ======================================

        const rejectBtn =
            document.getElementById(
                'pirm-modal-reject'
            );


        if (rejectBtn) {

            rejectBtn.addEventListener(
                'click',
                function () {


                    const report =
                        reports.find(
                            function (r) {

                                return String(r.id) ===
                                    String(activeModalId);

                            }
                        );


                    if (report) {

                        report.rejected = true;

                    }


                    activeModalId = null;

                    render();

                }
            );

        }



        // ======================================
        // UN-REJECT
        // ======================================

        const unrejectBtn =
            document.getElementById(
                'pirm-modal-unreject'
            );


        if (unrejectBtn) {

            unrejectBtn.addEventListener(
                'click',
                function () {


                    const report =
                        reports.find(
                            function (r) {

                                return String(r.id) ===
                                    String(activeModalId);

                            }
                        );


                    if (report) {

                        report.rejected = false;

                    }


                    activeModalId = null;

                    render();

                }
            );

        }



        // ======================================
        // DELETE
        // ======================================
// ==========================================
// DELETE REPORT
// ==========================================

const deleteBtn =
    document.getElementById('pirm-modal-delete');

if (deleteBtn) {

    deleteBtn.addEventListener('click', function () {

        const confirmed = confirm(
            'Are you sure you want to delete this report?'
        );

        if (!confirmed) {
            return;
        }


        // Find report
        const report = reports.find(function (r) {

            return String(r.id) ===
                String(activeModalId);

        });


        if (!report) {

            alert('Report not found.');

            return;

        }


        console.log('Deleting report:', report.id);


        // Send ID to PHP
        fetch('delete_report.php', {

            method: 'POST',

            headers: {
                'Content-Type':
                    'application/x-www-form-urlencoded'
            },

            body:
                'id=' +
                encodeURIComponent(report.id)

        })


        .then(function (response) {

            console.log(
                'Delete HTTP status:',
                response.status
            );

            return response.text();

        })


        .then(function (text) {

            console.log(
                'Delete server response:',
                text
            );


            // Convert response to JSON
            let data;

            try {

                data = JSON.parse(text);

            }

            catch (error) {

                console.error(
                    'Server did not return JSON:',
                    text
                );

                alert(
                    'delete_report.php did not return valid JSON.'
                );

                return;

            }


            // ==================================
            // SUCCESS
            // ==================================

            if (data.success) {


                // Remove from local JavaScript data
                reports =
                    reports.filter(function (r) {

                        return String(r.id) !==
                            String(report.id);

                    });


                alert(
                    'Report deleted successfully.'
                );


                // Close modal
                activeModalId = null;


                // Refresh dashboard
                render();


            }

            // ==================================
            // FAILURE
            // ==================================

            else {

                alert(
                    'Failed to delete report:\n' +
                    (data.message ||
                        'Unknown error')
                );

            }

        })


        .catch(function (error) {

            console.error(
                'Delete fetch error:',
                error
            );

            alert(
                'Could not connect to delete_report.php.'
            );

        });

    });

}
    }
            // ==========================================
    // START DASHBOARD
    // ==========================================

    render();

})();
</script>
</body>
</html>
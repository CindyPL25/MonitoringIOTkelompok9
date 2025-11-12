// Small runtime error overlay to help troubleshoot issues in the browser
(function installErrorOverlay(){
    function showError(text) {
        console.error('Runtime error:', text);
        let existing = document.getElementById('jsErrorOverlay');
        if (!existing) {
            existing = document.createElement('div');
            existing.id = 'jsErrorOverlay';
            existing.style.position = 'fixed';
            existing.style.right = '12px';
            existing.style.bottom = '12px';
            existing.style.zIndex = 99999;
            existing.style.maxWidth = '480px';
            existing.style.background = 'rgba(230,55,87,0.95)';
            existing.style.color = 'white';
            existing.style.padding = '12px 14px';
            existing.style.borderRadius = '8px';
            existing.style.boxShadow = '0 8px 30px rgba(0,0,0,0.3)';
            existing.style.fontSize = '13px';
            existing.style.lineHeight = '1.3';
            existing.style.fontFamily = 'Segoe UI, Tahoma, sans-serif';
            document.body && document.body.appendChild(existing);
        }
        existing.textContent = text;
        existing.style.display = 'block';
    }
    window.addEventListener('error', function(e){
        try { showError((e && e.message) ? e.message : String(e)); } catch(err) { console.error(err); }
    });
    window.addEventListener('unhandledrejection', function(e){
        try { showError('UnhandledRejection: ' + (e.reason && e.reason.message ? e.reason.message : String(e.reason))); } catch(err) { console.error(err); }
    });
})();

// Update tanggal dan waktu
function updateDateTime() {
    const now = new Date();
    
    // Format tanggal
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateString = now.toLocaleDateString('id-ID', options);
    document.getElementById('currentDate').textContent = dateString;
    
    // Format waktu
    const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    document.getElementById('currentTime').textContent = timeString;
    
    // Update waktu terakhir
    document.getElementById('lastUpdateTime').textContent = 'Baru saja';
}

// flag to avoid re-initializing history charts
let historyInitialized = false;

// Inisialisasi grafik
function initCharts() {
    // Data untuk grafik sensor
    const labels = ['00:00', '03:00', '06:00', '09:00', '12:00', '15:00', '18:00', '21:00'];
    
    // Grafik sensor
    const sensorCtx = document.getElementById('sensorChart').getContext('2d');
    const sensorChart = new Chart(sensorCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Curah Hujan (mm)',
                    data: [10, 15, 25, 40, 45, 42, 35, 30],
                    borderColor: '#2c7be5',
                    backgroundColor: 'rgba(44, 123, 229, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2
                },
                {
                    label: 'Kelembaban Tanah (%)',
                    data: [45, 50, 60, 70, 78, 75, 72, 68],
                    borderColor: '#00d97e',
                    backgroundColor: 'rgba(0, 217, 126, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2
                },
                {
                    label: 'Kemiringan Tanah (°)',
                    data: [8, 9, 10, 11, 12, 11, 10, 9],
                    borderColor: '#f6c343',
                    backgroundColor: 'rgba(246, 195, 67, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // Grafik status
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Aman', 'Waspada', 'Bahaya'],
            datasets: [{
                data: [60, 30, 10],
                backgroundColor: [
                    '#00d97e',
                    '#f6c343',
                    '#e63757'
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '70%'
        }
    });
    
    // Grafik risiko
    const riskCtx = document.getElementById('riskChart').getContext('2d');
    const riskChart = new Chart(riskCtx, {
        type: 'bar',
        data: {
            labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            datasets: [{
                label: 'Tingkat Risiko',
                data: [30, 45, 60, 75, 65, 50, 40],
                backgroundColor: [
                    '#00d97e',
                    '#00d97e',
                    '#f6c343',
                    '#e63757',
                    '#f6c343',
                    '#00d97e',
                    '#00d97e'
                ],
                borderRadius: 5,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    return { sensorChart, statusChart, riskChart };

    // NOTE: history charts will be initialized after return in code below (kept simple)
}

// Initialize small history charts per sensor (used in view-history)
function initHistoryCharts() {
    // generate hourly labels for last 8 timepoints (3-hour steps)
    const now = new Date();
    const labels = [];
    for (let i = 7; i >= 0; i--) {
        const d = new Date(now.getTime() - i * 3 * 60 * 60 * 1000);
        labels.push(d.getHours().toString().padStart(2, '0') + ':00');
    }

    // helper to create slightly random sample data based on base array
    const makeSeries = (base, variance = 8) => base.map(v => Math.max(0, Math.round(v + (Math.random() - 0.5) * variance)));

    const baseRain = [8, 12, 18, 30, 38, 35, 28, 22];
    const baseSoil = [40, 48, 55, 60, 72, 68, 64, 59];
    const baseTilt = [6, 7, 9, 11, 12, 11, 10, 8];
    const baseTemp = [24, 24, 25, 26, 27, 26, 25, 24];

    const dataRain = makeSeries(baseRain, 10);
    const dataSoil = makeSeries(baseSoil, 6);
    const dataTilt = makeSeries(baseTilt, 3);
    const dataTemp = makeSeries(baseTemp, 2);

    const createSimple = (canvasId, color, unit, series) => {
        const node = document.getElementById(canvasId);
        if (!node) return null;
        const ctx = node.getContext('2d');
        return new Chart(ctx, {
            type: 'line',
            data: { labels, datasets: [{ label: unit, data: series, borderColor: color, backgroundColor: 'rgba(0,0,0,0)', tension: 0.3, pointRadius: 2 }] },
            options: { responsive: true, plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } }, scales: { y: { beginAtZero: true } } }
        });
    };

    const hRain = createSimple('historyRainChart', '#2c7be5', 'Curah Hujan (mm)', dataRain);
    const hSoil = createSimple('historySoilChart', '#00d97e', 'Kelembaban Tanah (%)', dataSoil);
    const hTilt = createSimple('historyTiltChart', '#f6c343', 'Kemiringan (°)', dataTilt);
    const hTemp = createSimple('historyTempChart', '#e63757', 'Suhu (°C)', dataTemp);

    // Also render a small history table/list so the view feels populated
    renderDummyHistoryList(labels, { rain: dataRain, soil: dataSoil, tilt: dataTilt, temp: dataTemp });

    return { hRain, hSoil, hTilt, hTemp };
}

// Dummy data for locations and notifications
function renderDummyLocations() {
    const locations = [
        { id: 'Node-1', lat: -7.250445, lng: 112.768845, status: 'online', lastSeen: '5m', sensors: { rain: 45, soil: 72, tilt: 9 } },
        { id: 'Node-2', lat: -7.251000, lng: 112.769500, status: 'offline', lastSeen: '2h', sensors: { rain: 60, soil: 85, tilt: 12 } },
        { id: 'Node-3', lat: -7.249500, lng: 112.770200, status: 'online', lastSeen: '1m', sensors: { rain: 30, soil: 55, tilt: 7 } },
        { id: 'Node-4', lat: -7.248800, lng: 112.771000, status: 'online', lastSeen: '10m', sensors: { rain: 12, soil: 48, tilt: 5 } },
        { id: 'Node-5', lat: -7.252200, lng: 112.767900, status: 'offline', lastSeen: '4h', sensors: { rain: 0, soil: 38, tilt: 4 } },
        { id: 'Node-6', lat: -7.253000, lng: 112.770800, status: 'online', lastSeen: '2m', sensors: { rain: 22, soil: 60, tilt: 8 } }
    ];

    const container = document.getElementById('locationsContainer');
    if (!container) return;
    container.innerHTML = '';

    locations.forEach(loc => {
        const card = document.createElement('div');
        card.className = 'location-card';
        const statusColor = loc.status === 'online' ? 'online' : 'offline';
        card.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div class="location-title">${loc.id}</div>
                    <div class="location-meta">Lat: ${loc.lat.toFixed(6)}, Lng: ${loc.lng.toFixed(6)}</div>
                </div>
                <div style="text-align:right;">
                    <div class="device-status ${statusColor}">${loc.status.toUpperCase()}</div>
                    <div class="location-meta" style="font-size:0.85rem;">Terakhir: ${loc.lastSeen}</div>
                </div>
            </div>
            <div style="margin-top:10px;display:flex;gap:12px;align-items:center;">
                <div style="font-size:0.9rem;color:var(--secondary);">Hujan: <strong>${loc.sensors.rain} mm</strong></div>
                <div style="font-size:0.9rem;color:var(--secondary);">Tanah: <strong>${loc.sensors.soil}%</strong></div>
                <div style="font-size:0.9rem;color:var(--secondary);">Tilt: <strong>${loc.sensors.tilt}°</strong></div>
            </div>`;
        container.appendChild(card);
    });
}

function renderDummyNotifications() {
    const items = [
        { level: 'warn', time: '10:15', text: 'Curah hujan meningkat di area Node-1 (45 mm).' },
        { level: 'danger', time: '09:40', text: 'Kelembaban tanah di Node-2 sangat tinggi (85%).' },
        { level: 'safe', time: '08:20', text: 'Node-3 terhubung kembali.' }
    ];

    const container = document.getElementById('notificationsContainer');
    if (!container) return;
    container.innerHTML = '';
    items.forEach(it => {
        const el = document.createElement('div');
        el.className = `notification-item ${it.level}`;
        el.innerHTML = `<div><strong>${it.text}</strong></div><div class="time">${it.time}</div>`;
        container.appendChild(el);
    });
}

// Render a small history summary list under history charts
function renderDummyHistoryList(labels, series) {
    const container = document.getElementById('historyList');
    if (!container) return;
    container.innerHTML = '';

    const lastIndex = labels.length - 1;
    const rows = [
        { name: 'Curah Hujan', unit: 'mm', value: series.rain[lastIndex] },
        { name: 'Kelembaban Tanah', unit: '%', value: series.soil[lastIndex] },
        { name: 'Kemiringan Tanah', unit: '°', value: series.tilt[lastIndex] },
        { name: 'Suhu Udara', unit: '°C', value: series.temp[lastIndex] }
    ];

    rows.forEach(r => {
        const el = document.createElement('div');
        el.className = 'history-row';
        el.innerHTML = `<div class="left"><div class="sensor">${r.name}</div><div class="meta">terakhir: ${labels[lastIndex]}</div></div><div class="value">${r.value} ${r.unit}</div>`;
        container.appendChild(el);
    });
}

// Helper to generate dummy history series and labels (re-usable)
function generateDummyHistoryData() {
    const now = new Date();
    const labels = [];
    for (let i = 7; i >= 0; i--) {
        const d = new Date(now.getTime() - i * 3 * 60 * 60 * 1000);
        labels.push(d.getHours().toString().padStart(2, '0') + ':00');
    }
    const makeSeries = (base, variance = 8) => base.map(v => Math.max(0, Math.round(v + (Math.random() - 0.5) * variance)));
    const baseRain = [8, 12, 18, 30, 38, 35, 28, 22];
    const baseSoil = [40, 48, 55, 60, 72, 68, 64, 59];
    const baseTilt = [6, 7, 9, 11, 12, 11, 10, 8];
    const baseTemp = [24, 24, 25, 26, 27, 26, 25, 24];
    return { labels, series: { rain: makeSeries(baseRain, 10), soil: makeSeries(baseSoil, 6), tilt: makeSeries(baseTilt, 3), temp: makeSeries(baseTemp, 2) } };
}

// Simulasi update data real-time
function updateData() {
    // Simulasi perubahan data
    const rainValue = Math.floor(Math.random() * 10) + 40;
    const soilValue = Math.floor(Math.random() * 10) + 70;
    const tiltValue = Math.floor(Math.random() * 5) + 10;
    const tempValue = Math.floor(Math.random() * 5) + 25;
    
    // Update card values (only number part)
    const cardValueEls = document.querySelectorAll('.card .value');
    if (cardValueEls[0]) cardValueEls[0].textContent = rainValue;
    if (cardValueEls[1]) cardValueEls[1].textContent = soilValue;
    if (cardValueEls[2]) cardValueEls[2].textContent = tiltValue;
    if (cardValueEls[3]) cardValueEls[3].textContent = tempValue;

    // Update progress bars in card area (cards-grid)
    const cardProgressFills = document.querySelectorAll('.cards-grid .progress-fill');
    if (cardProgressFills[0]) cardProgressFills[0].style.width = `${Math.min(rainValue, 100)}%`;
    if (cardProgressFills[1]) cardProgressFills[1].style.width = `${soilValue}%`;
    if (cardProgressFills[2]) cardProgressFills[2].style.width = `${Math.min(tiltValue * 5, 100)}%`;
    if (cardProgressFills[3]) cardProgressFills[3].style.width = `${Math.min(tempValue * 2, 100)}%`;

    // Update alert indicators (separate values)
    const indicatorValueEls = document.querySelectorAll('.alert-indicators .indicator-value .value');
    if (indicatorValueEls[0]) indicatorValueEls[0].textContent = rainValue;
    if (indicatorValueEls[1]) indicatorValueEls[1].textContent = soilValue;
    if (indicatorValueEls[2]) indicatorValueEls[2].textContent = tiltValue;

    // Update progress bars in indicators
    const indicatorProgressFills = document.querySelectorAll('.alert-indicators .progress-fill');
    if (indicatorProgressFills[0]) indicatorProgressFills[0].style.width = `${Math.min(rainValue, 100)}%`;
    if (indicatorProgressFills[1]) indicatorProgressFills[1].style.width = `${soilValue}%`;
    if (indicatorProgressFills[2]) indicatorProgressFills[2].style.width = `${Math.min(tiltValue * 5, 100)}%`;
    
    // Update status berdasarkan nilai
    updateStatus(rainValue, soilValue, tiltValue);
    
    // Update waktu
    document.querySelectorAll('.update-time').forEach(el => {
        el.innerHTML = '<i class="far fa-clock"></i> Update: Baru saja';
    });
}

function updateStatus(rain, soil, tilt) {
    // Logika status berdasarkan nilai sensor
    const rainStatus = rain > 50 ? 'danger' : (rain > 30 ? 'warning' : 'safe');
    const soilStatus = soil > 80 ? 'danger' : (soil > 60 ? 'warning' : 'safe');
    const tiltStatus = tilt > 15 ? 'danger' : (tilt > 10 ? 'warning' : 'safe');
    
    // Update status di cards
    document.querySelectorAll('.status-badge')[0].className = `status-badge ${rainStatus}`;
    document.querySelectorAll('.status-badge')[1].className = `status-badge ${soilStatus}`;
    document.querySelectorAll('.status-badge')[2].className = `status-badge ${tiltStatus}`;
    
    // Update progress bar colors
    document.querySelectorAll('.progress-fill')[0].className = `progress-fill ${rainStatus}`;
    document.querySelectorAll('.progress-fill')[1].className = `progress-fill ${soilStatus}`;
    document.querySelectorAll('.progress-fill')[2].className = `progress-fill ${tiltStatus}`;
    
    // Update progress labels
    const rainLabel = rain > 50 ? 'Sangat Tinggi' : (rain > 30 ? 'Tinggi' : 'Normal');
    const soilLabel = soil > 80 ? 'Sangat Tinggi' : (soil > 60 ? 'Tinggi' : 'Normal');
    const tiltLabel = tilt > 15 ? 'Tinggi' : (tilt > 10 ? 'Sedang' : 'Normal');
    
    document.querySelectorAll('.progress-label')[0].textContent = rainLabel;
    document.querySelectorAll('.progress-label')[1].textContent = soilLabel;
    document.querySelectorAll('.progress-label')[2].textContent = tiltLabel;
    
    // Update status di alert section
    document.querySelectorAll('.indicator-status')[0].className = `indicator-status ${rainStatus}`;
    document.querySelectorAll('.indicator-status')[1].className = `indicator-status ${soilStatus}`;
    document.querySelectorAll('.indicator-status')[2].className = `indicator-status ${tiltStatus}`;
    
    // Update teks status
    const rainText = rain > 50 ? 'BAHAYA' : (rain > 30 ? 'TINGGI' : 'NORMAL');
    const soilText = soil > 80 ? 'BAHAYA' : (soil > 60 ? 'TINGGI' : 'NORMAL');
    const tiltText = tilt > 15 ? 'BAHAYA' : (tilt > 10 ? 'TINGGI' : 'NORMAL');
    
    document.querySelectorAll('.status-badge')[0].innerHTML = `<i class="fas ${rainStatus === 'danger' ? 'fa-exclamation-triangle' : rainStatus === 'warning' ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> ${rainText}`;
    document.querySelectorAll('.status-badge')[1].innerHTML = `<i class="fas ${soilStatus === 'danger' ? 'fa-exclamation-triangle' : soilStatus === 'warning' ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> ${soilText}`;
    document.querySelectorAll('.status-badge')[2].innerHTML = `<i class="fas ${tiltStatus === 'danger' ? 'fa-exclamation-triangle' : tiltStatus === 'warning' ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> ${tiltText}`;
    
    document.querySelectorAll('.indicator-status')[0].innerHTML = `<i class="fas ${rainStatus === 'danger' ? 'fa-exclamation-triangle' : rainStatus === 'warning' ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> ${rainText}`;
    document.querySelectorAll('.indicator-status')[1].innerHTML = `<i class="fas ${soilStatus === 'danger' ? 'fa-exclamation-triangle' : soilStatus === 'warning' ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> ${soilText}`;
    document.querySelectorAll('.indicator-status')[2].innerHTML = `<i class="fas ${tiltStatus === 'danger' ? 'fa-exclamation-triangle' : tiltStatus === 'warning' ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> ${tiltText}`;
    
    // Update alert banner berdasarkan kondisi
    const alertBanner = document.querySelector('.alert-banner');
    if (rainStatus === 'danger' || soilStatus === 'danger') {
        alertBanner.className = 'alert-banner danger';
        alertBanner.querySelector('.alert-icon').innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
        alertBanner.querySelector('.alert-icon').style.color = 'var(--danger)';
        alertBanner.querySelector('h3').textContent = 'Status Bahaya';
        alertBanner.querySelector('p').textContent = 'Kondisi lingkungan sangat berisiko menyebabkan tanah longsor. Segera lakukan tindakan evakuasi.';
    } else if (rainStatus === 'warning' || soilStatus === 'warning') {
        alertBanner.className = 'alert-banner warning';
        alertBanner.querySelector('.alert-icon').innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
        alertBanner.querySelector('.alert-icon').style.color = 'var(--warning)';
        alertBanner.querySelector('h3').textContent = 'Status Waspada';
        alertBanner.querySelector('p').textContent = 'Sistem mendeteksi curah hujan tinggi dan kelembaban tanah meningkat. Waspada potensi tanah longsor.';
    } else {
        alertBanner.className = 'alert-banner safe';
        alertBanner.querySelector('.alert-icon').innerHTML = '<i class="fas fa-check-circle"></i>';
        alertBanner.querySelector('.alert-icon').style.color = 'var(--success)';
        alertBanner.querySelector('h3').textContent = 'Status Aman';
        alertBanner.querySelector('p').textContent = 'Semua parameter dalam kondisi normal. Tidak ada indikasi potensi tanah longsor.';
    }
}

// Event listeners
function setupEventListeners() {
    // Chart period buttons
    document.querySelectorAll('.chart-action-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.chart-action-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Close alert banner
    document.querySelector('.alert-close').addEventListener('click', function() {
        document.querySelector('.alert-banner').style.display = 'none';
    });
    
    // Device refresh buttons
    document.querySelectorAll('.device-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            icon.classList.add('fa-spin');
            setTimeout(() => {
                icon.classList.remove('fa-spin');
            }, 1000);
        });
    });

    // Sidebar toggle (collapse)
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
        });
    }

    // About modal open from sidebar nav
    const aboutNav = document.getElementById('aboutNav');
    if (aboutNav) {
        aboutNav.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('About button clicked');
            openAbout();
        });
    }

    // Fallback quick about button in sidebar
    const aboutNavBtn = document.getElementById('aboutNavBtn');
    if (aboutNavBtn) {
        aboutNavBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('About quick button clicked');
            openAbout();
        });
    }

    // (login removed) no login or logout listeners

    // Nav links that switch views
    document.querySelectorAll('.nav-links a[data-view]').forEach(a => {
        a.addEventListener('click', function(e) {
            e.preventDefault();
            const view = this.dataset.view;
            console.log('nav click ->', view);
            // initialize history charts lazily (only once)
            if (view === 'history' && !historyInitialized) {
                initHistoryCharts();
                historyInitialized = true;
            }
            showView(view);
        });
    });

    // Modal close handlers (overlay and close button)
    document.querySelectorAll('[data-close="true"]').forEach(el => {
        el.addEventListener('click', function(e) {
            e.stopPropagation();
            closeAbout();
        });
    });
    const modalCloseBtn = document.querySelector('.modal-close');
    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeAbout();
        });
    }

    // Close modal with ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeAbout();
    });
}

// Open About modal
function openAbout() {
    const modal = document.getElementById('aboutModal');
    if (!modal) return;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    const firstFocusable = modal.querySelector('.modal-close');
    if (firstFocusable) firstFocusable.focus();
}

// Function alias untuk onclick handler
function openAboutModal() {
    console.log('openAboutModal called');
    const modal = document.getElementById('aboutModal');
    if (modal) {
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    }
}

// Close About modal
function closeAbout() {
    const modal = document.getElementById('aboutModal');
    if (!modal) return;
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
}

// Function alias untuk onclick handler
function closeAboutModal() {
    console.log('closeAboutModal called');
    const modal = document.getElementById('aboutModal');
    if (modal) {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    }
}

// Show a specific view (dashboard, history, map, notifications, settings)
function showView(viewId) {
    const views = document.querySelectorAll('.main-content .view');
    views.forEach(v => v.classList.add('hidden'));
    const target = document.getElementById('view-' + viewId) || document.getElementById(viewId) || document.getElementById('view-' + viewId);
    if (target) target.classList.remove('hidden');

    // update active nav item
    document.querySelectorAll('.nav-links li').forEach(li => li.classList.remove('active'));
    const navAnchor = document.querySelector(`.nav-links a[data-view="${viewId}"]`);
    if (navAnchor && navAnchor.parentElement) navAnchor.parentElement.classList.add('active');

    // update header title for dashboard vs other views
    const headerTitle = document.querySelector('.header-title h2');
    if (headerTitle) {
        if (viewId === 'dashboard') headerTitle.textContent = 'Dashboard Monitoring';
        else if (viewId === 'history') headerTitle.textContent = 'Data Historis';
        else headerTitle.textContent = viewId.charAt(0).toUpperCase() + viewId.slice(1);
    }
}

// Application start
function startApp() {
    updateDateTime();
    const charts = initCharts();
    // default view
    showView('dashboard');
    // Update date time every minute
    setInterval(updateDateTime, 60000);
    // Update data every 10 seconds (simulasi)
    setInterval(updateData, 10000);
    // Initial data update
    updateData();
    // Render dummy content for extra views
    // generate and render dummy history summary (so history view has content)
    const dummyHistory = generateDummyHistoryData();
    renderDummyHistoryList(dummyHistory.labels, dummyHistory.series);
    renderDummyLocations();
    renderDummyNotifications();
}

// Initialize the application
function init() {
    setupEventListeners();
    startApp();
}

// Start the application when DOM is loaded
document.addEventListener('DOMContentLoaded', init);

// Direct event listener for About button - buat pasti bisa diklik
document.addEventListener('DOMContentLoaded', function() {
    const aboutLink = document.getElementById('aboutNav');
    if (aboutLink) {
        aboutLink.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('About clicked - opening modal');
            const modal = document.getElementById('aboutModal');
            if (modal) {
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
            }
        });
    }
});

// Global event listener untuk menutup modal
document.addEventListener('DOMContentLoaded', function() {
    // Close button
    const closeBtn = document.querySelector('.modal-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const modal = document.getElementById('aboutModal');
            if (modal) {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
            }
        });
    }
    
    // Overlay
    const overlay = document.querySelector('.modal-overlay');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            e.stopPropagation();
            const modal = document.getElementById('aboutModal');
            if (modal) {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
            }
        });
    }
    
    // ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('aboutModal');
            if (modal) {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
            }
        }
    });
});
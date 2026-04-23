// JavaScript for the Visitor Management System

// Data storage (in a real application, this would be a database)
const API_BASE_URL = '../integ/Vistor.php';
let hotelVisitors = JSON.parse(localStorage.getItem('hotelVisitors')) || [];
let restaurantVisitors = JSON.parse(localStorage.getItem('restaurantVisitors')) || [];
let settings = JSON.parse(localStorage.getItem('visitorSettings')) || {
    businessName: "Hotel & Restaurant",
    timezone: "UTC",
    dataRetention: 365
};

// Initialize the application
document.addEventListener('DOMContentLoaded', function () {
    // Set up navigation
    setupNavigation();

    // Set up tabs
    setupTabs();

    // Set up form submissions
    setupForms();

    // Load all data - We fetch data first, which will update global arrays and THEN update dashboard
    loadCurrentVisitors().then(() => {
        updateDashboard();
    });
    loadHistory();

    // Load employees for host selection
    loadEmployeesForHosts();
});

// Navigation setup
function setupNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    const sidebarLinks = document.querySelectorAll('.sidebar-link');

    function handleNavigation(pageId) {
        showPage(pageId);

        // Update Nav Links
        navLinks.forEach(l => {
            const navPage = l.getAttribute('data-page');
            const mainPagePrefix = pageId.split('-')[0];
            if (navPage === pageId || navPage === mainPagePrefix || (navPage.includes('-') && navPage.startsWith(mainPagePrefix))) {
                l.classList.add('active');
            } else {
                l.classList.remove('active');
            }
        });

        // Update Sidebar Links
        sidebarLinks.forEach(l => {
            if (l.getAttribute('data-page') === pageId) {
                l.classList.add('active');
            } else {
                l.classList.remove('active');
            }
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            const pageId = this.getAttribute('data-page');
            if (!pageId) return;
            e.preventDefault();
            handleNavigation(pageId);
        });
    });

    sidebarLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            const pageId = this.getAttribute('data-page');
            if (!pageId) return;
            e.preventDefault();
            handleNavigation(pageId);
        });
    });
}

// Tab navigation setup
function setupTabs() {
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const tabId = this.getAttribute('data-tab');
            const parent = this.closest('.page');

            // Update active tab UI
            const siblingTabs = parent.querySelectorAll('.tab');
            siblingTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Show corresponding content tab
            const tabContents = parent.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                if (content.id === `${tabId}-tab`) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });

            // Trigger data refresh if needed for specific tabs
            if (tabId.includes('visitors')) {
                loadCurrentVisitors();
            } else if (tabId.includes('history')) {
                loadHistory();
            }
        });
    });
}

// Show report date range based on selection
const reportType = document.getElementById('report-type');
if (reportType) {
    reportType.addEventListener('change', function () {
        const customRange = document.getElementById('custom-date-range');
        if (this.value === 'custom') {
            customRange.style.display = 'block';
        } else {
            customRange.style.display = 'none';
        }
    });
}

// Show specific page
function showPage(pageId) {
    // Hide all pages
    const pages = document.querySelectorAll('.page');
    pages.forEach(page => page.classList.remove('active'));

    // Show requested page
    const targetPage = document.getElementById(pageId);
    if (targetPage) {
        targetPage.classList.add('active');

        // Update Nav & Sidebar Links state
        document.querySelectorAll('[data-page]').forEach(el => {
            const requested = el.getAttribute('data-page');
            if (requested === pageId || requested === pageId + '-checkin' || requested === pageId + '-visitors') {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });

        // Load data if needed
        if (pageId === 'dashboard') {
            updateDashboard();
        } else if (pageId === 'hotel' || pageId === 'restaurant') {
            loadCurrentVisitors().then(() => {
                updateDashboard();
            });
            loadHistory();
        }
    }
}

// Activate inner tab (programmatic)
function activateTab(tabName) {
    document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));

    const tab = document.querySelector('.tabs .tab[data-tab="' + tabName + '"]');
    if (tab) tab.classList.add('active');

    const tc = document.getElementById(tabName + '-tab');
    if (tc) tc.classList.add('active');

    // Trigger data refresh if needed for specific tabs
    if (tabName.includes('visitors')) {
        loadCurrentVisitors();
    } else if (tabName.includes('history')) {
        loadHistory();
    }
}

// Setup form submissions
function setupForms() {
    // Hotel check-in form
    const hotelCheckinForm = document.getElementById('hotel-checkin-form');
    if (hotelCheckinForm) {
        hotelCheckinForm.addEventListener('submit', function (e) {
            e.preventDefault();
            timeInHotelGuest();
        });
    }

    // Restaurant check-in form
    const restaurantCheckinForm = document.getElementById('restaurant-checkin-form');
    if (restaurantCheckinForm) {
        restaurantCheckinForm.addEventListener('submit', function (e) {
            e.preventDefault();
            timeInRestaurantVisitor();
        });
    }

    // Report form
    const reportForm = document.getElementById('report-form');
    if (reportForm) {
        reportForm.addEventListener('submit', function (e) {
            e.preventDefault();
            generateReport();
        });
    }

}

// Time-in hotel guest
function timeInHotelGuest() {
    const form = document.getElementById('hotel-checkin-form');
    const formData = new FormData(form);

    // Prepare data for API
    const requestData = {
        full_name: formData.get('full_name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        room_number: formData.get('room_number'),
        host_id: formData.get('host_id'),
        time_in: formData.get('time_in'),
        notes: formData.get('notes')
    };

    fetch(API_BASE_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showAlert('Guest time-in recorded successfully!', 'success');
                form.reset();
                loadCurrentVisitors().then(() => {
                    updateDashboard();
                });
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while saving data.', 'error');
        });
}

// Time-in restaurant visitor
function timeInRestaurantVisitor() {
    const form = document.getElementById('restaurant-checkin-form');
    const formData = new FormData(form);

    const visitor = {
        id: Date.now(),
        name: formData.get('visitor-name'),
        phone: formData.get('visitor-phone'),
        partySize: parseInt(formData.get('party-size')),
        table: formData.get('table-number'),
        host: formData.get('restaurant-host'),
        notes: formData.get('restaurant-notes'),
        status: 'timed-in',
        checkinTime: new Date().toISOString(),
        checkoutTime: null
    };

    restaurantVisitors.push(visitor);
    localStorage.setItem('restaurantVisitors', JSON.stringify(restaurantVisitors));

    // Show success message
    showAlert('Visitor time-in recorded successfully!', 'success');

    // Reset form
    form.reset();

    // Update dashboard and tables
    updateDashboard();
    loadCurrentVisitors();
}

// Time-out hotel guest
// Time-out hotel guest
function timeOutHotelGuest(guestId) {
    showConfirmationModal('Are you sure you want to time-out this guest?', function () {
        // Check if it's an external record (starts with 'ext_')
        if (String(guestId).startsWith('ext_')) {
            alert('Cannot time-out external records. Please use the core system.');
            return;
        }

        fetch(API_BASE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'checkout',
                id: guestId
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showAlert('Guest time-out recorded successfully!', 'success');
                    loadCurrentVisitors(); // Refresh table
                } else {
                    showAlert('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred during time-out.', 'error');
            });
    });
}

// Time-out restaurant visitor
function timeOutRestaurantVisitor(visitorId) {
    const visitor = restaurantVisitors.find(v => v.id === visitorId);
    if (visitor) {
        visitor.status = 'timed-out';
        visitor.checkoutTime = new Date().toISOString();
        localStorage.setItem('restaurantVisitors', JSON.stringify(restaurantVisitors));

        showAlert('Visitor time-out recorded successfully!', 'success');
        updateDashboard();
        loadCurrentVisitors();
    }
}

// Update dashboard statistics
function updateDashboard() {
    const today = new Date().toDateString();

    // Hotel statistics
    const hotelToday = hotelVisitors.filter(guest => {
        const checkinDate = new Date(guest.checkinTime).toDateString();
        return checkinDate === today;
    }).length;

    const hotelCurrent = hotelVisitors.filter(guest => guest.status === 'timed-in').length;

    // Restaurant statistics (Sum of party sizes for total guest count)
    const restaurantTodayGuests = restaurantVisitors
        .filter(visitor => new Date(visitor.checkinTime).toDateString() === today)
        .reduce((sum, v) => sum + (parseInt(v.partySize) || 0), 0);

    const restaurantCurrentGuests = restaurantVisitors
        .filter(visitor => visitor.status === 'timed-in')
        .reduce((sum, v) => sum + (parseInt(v.partySize) || 0), 0);

    // Update DOM
    document.getElementById('hotel-today').textContent = hotelToday;
    document.getElementById('hotel-current').textContent = hotelCurrent;
    document.getElementById('restaurant-today').textContent = restaurantTodayGuests;
    document.getElementById('restaurant-current').textContent = restaurantCurrentGuests;

    // Update recent activity
    updateRecentActivity();
}

// Update recent activity list with a premium, dashboard-standard design
function updateRecentActivity() {
    const activityContainer = document.getElementById('recent-activity');
    if (!activityContainer) return;

    // Combine recent activities from both hotel and restaurant with rich metadata
    const allActivities = [
        ...hotelVisitors.map(guest => ({
            type: 'Hotel',
            name: guest.name,
            icon: 'fa-hotel',
            color: '#3b82f6',
            action: guest.status === 'timed-in' ? 'CHECKED IN' : 'CHECKED OUT',
            statusRaw: guest.status === 'timed-in' ? 'in' : 'out',
            time: guest.status === 'timed-in' ? guest.checkinTime : guest.checkoutTime,
            location: `Room ${guest.room}`
        })),
        ...restaurantVisitors.map(v => ({
            type: 'Restaurant',
            name: v.name,
            icon: 'fa-utensils',
            color: '#10b981',
            action: v.status === 'timed-in' ? 'CHECKED IN' : 'CHECKED OUT',
            statusRaw: v.status === 'timed-in' ? 'in' : 'out',
            time: v.status === 'timed-in' ? v.checkinTime : v.checkoutTime,
            location: `Table ${v.table}`
        }))
    ];

    // Filter and sort by newest first
    const recentActivities = allActivities
        .filter(a => a.time && a.time !== 'N/A')
        .sort((a, b) => new Date(b.time) - new Date(a.time))
        .slice(0, 10);

    activityContainer.innerHTML = '';

    if (recentActivities.length === 0) {
        activityContainer.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #94a3b8;">
                <i class="fa-solid fa-clock-rotate-left" style="font-size: 2.5rem; opacity: 0.2; display: block; margin-bottom: 15px;"></i>
                <p style="font-weight: 500;">No recent activity tracked yet</p>
            </div>`;
        return;
    }

    recentActivities.forEach(activity => {
        const item = document.createElement('div');
        item.style.cssText = `
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.2s ease;
        `;
        
        // Add hover effect via JS for immediate result
        item.onmouseenter = () => item.style.backgroundColor = '#f8fafc';
        item.onmouseleave = () => item.style.backgroundColor = 'transparent';

        const statusBg = activity.statusRaw === 'in' ? '#ecfdf5' : '#f8fafc';
        const statusColor = activity.statusRaw === 'in' ? '#059669' : '#64748b';
        const labelBg = activity.type === 'Hotel' ? '#eff6ff' : '#ecfdf5';
        const labelColor = activity.type === 'Hotel' ? '#3b82f6' : '#10b981';

        item.innerHTML = `
            <div style="width: 45px; height: 45px; background: ${labelBg}; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: ${labelColor}; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fas ${activity.icon}"></i>
            </div>
            
            <div style="flex-grow: 1;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                    <span style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">${activity.name}</span>
                    <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 500;">${formatTime(activity.time)}</span>
                </div>
                
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span style="padding: 2px 8px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; background: ${statusBg}; color: ${statusColor};">
                        ${activity.action}
                    </span>
                    <span style="color: #64748b; font-size: 0.8rem; font-weight: 500;">
                        at the <strong style="color: ${labelColor}">${activity.type}</strong> • ${activity.location}
                    </span>
                </div>
            </div>
        `;
        activityContainer.appendChild(item);
    });
}

// Load current visitors into table
function loadCurrentVisitors() {
    const hotelCurrentTable = document.getElementById('hotel-current-table');
    const hotelTbody = hotelCurrentTable ? hotelCurrentTable.querySelector('tbody') : null;
    const restaurantCurrentTable = document.getElementById('restaurant-current-table');
    const restaurantTbody = restaurantCurrentTable ? restaurantCurrentTable.querySelector('tbody') : null;

    return fetch(API_BASE_URL)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data) {
                // Clear global arrays
                hotelVisitors = [];
                restaurantVisitors = [];

                data.data.forEach(item => {
                    const mappedItem = {
                        id: item.id,
                        name: item.full_name,
                        room: item.room_number,
                        checkinTime: item.checkin_date,
                        checkoutTime: item.checkout_date,
                        status: item.status === 'active' ? 'timed-in' : (item.status || 'unknown'),
                        email: item.email,
                        phone: item.phone_number,
                        notes: item.notes,
                        source: item.source,
                        venue: item.venue || 'hotel',
                        partySize: item.party_size || 1,
                        table: item.table_number || 'N/A'
                    };

                    if (mappedItem.venue === 'restaurant') {
                        restaurantVisitors.push(mappedItem);
                    } else {
                        hotelVisitors.push(mappedItem);
                    }
                });

                // Update Hotel Table
                if (hotelTbody) {
                    hotelTbody.innerHTML = '';
                    const activeHotel = hotelVisitors.filter(v => v.status === 'timed-in');
                    if (activeHotel.length > 0) {
                        activeHotel.forEach(guest => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td style="font-weight: 700; color: #1e293b;">${guest.name || 'N/A'}</td>
                                <td style="font-weight: 600; color: #64748b;">${guest.room || 'N/A'}</td>
                                <td style="color: #64748b;">${formatDate(guest.checkinTime)}</td>
                                <td>
                                    <span class="status-badge status-active">CHECKED IN</span>
                                </td>
                                <td style="white-space: nowrap;">
                                    <div style="display: flex; gap: 8px;">
                                        <button class="btn-action-view" onclick="viewVisitorDetails('${guest.id}')" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action-timeout" onclick="timeOutHotelGuest('${guest.id}')" title="Time-out Guest">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            `;
                            hotelTbody.appendChild(row);
                        });
                    } else {
                        hotelTbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No current guests</td></tr>';
                    }
                }

                // Update Restaurant Table
                if (restaurantTbody) {
                    restaurantTbody.innerHTML = '';
                    const activeRest = restaurantVisitors.filter(v => v.status === 'timed-in');
                    if (activeRest.length > 0) {
                        activeRest.forEach(visitor => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td style="font-weight: 700; color: #1e293b;">${visitor.name}</td>
                                <td style="font-weight: 600; color: #64748b; text-align: center;">${visitor.partySize}</td>
                                <td style="font-weight: 600; color: #3b82f6;">${visitor.table}</td>
                                <td style="color: #64748b;">${formatTime(visitor.checkinTime)}</td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 8px; justify-content: center;">
                                        <button class="btn-action-view" onclick="viewVisitorDetails('${visitor.id}')" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action-timeout" onclick="timeOutRestaurantVisitor('${visitor.id}')" title="Time-out Visitor">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            `;
                            restaurantTbody.appendChild(row);
                        });
                    } else {
                        restaurantTbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No current visitors</td></tr>';
                    }
                }
                
                // Update dashboard since we have fresh data
                updateDashboard();
            }
        })
        .catch(error => {
            console.error('Error fetching visitors:', error);
        });
}

function timeOutRestaurantVisitor(visitorId) {
    showConfirmationModal('Are you sure you want to time-out this visitor?', function () {
        fetch(API_BASE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'checkout',
                id: visitorId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showAlert('Visitor time-out recorded successfully!', 'success');
                loadCurrentVisitors();
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred during time-out.', 'error');
        });
    });
}

// Load visitor history into tables
function loadHistory() {
    // 1. Hotel History
    const hotelHistoryTable = document.getElementById('hotel-history-table');
    if (hotelHistoryTable) {
        const tbody = hotelHistoryTable.querySelector('tbody');
        tbody.innerHTML = '';

        if (hotelVisitors.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No history records</td></tr>';
        } else {
            hotelVisitors.forEach(guest => {
                const row = document.createElement('tr');
                const statusLabel = guest.status === 'timed-in' ? 'CHECKED IN' : guest.status;
                const statusClass = guest.status === 'timed-in' ? 'status-timed-in' : 'status-timed-out';
                row.innerHTML = `
                    <td style="font-weight: 700;">${guest.name}</td>
                    <td style="font-weight: 600; color: #64748b;">${guest.room || 'N/A'}</td>
                    <td style="color: #64748b;">${formatDate(guest.checkinTime)}</td>
                    <td style="color: #64748b;">${guest.checkoutTime ? formatDate(guest.checkoutTime) : 'N/A'}</td>
                    <td><span class="status-badge ${statusClass}">${statusLabel}</span></td>
                `;
                tbody.appendChild(row);
            });
        }
    }

    // 2. Restaurant History
    const restaurantHistoryTable = document.getElementById('restaurant-history-table');
    if (restaurantHistoryTable) {
        const tbody = restaurantHistoryTable.querySelector('tbody');
        tbody.innerHTML = '';

        if (restaurantVisitors.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No history records</td></tr>';
        } else {
            restaurantVisitors.forEach(visitor => {
                const row = document.createElement('tr');
                const checkOutTime = visitor.checkoutTime ? formatTime(visitor.checkoutTime) : 'N/A';
                row.innerHTML = `
                    <td style="font-weight: 700;">${visitor.name}</td>
                    <td style="font-weight: 600; color: #64748b; text-align: center;">${visitor.partySize}</td>
                    <td style="font-weight: 600; color: #3b82f6;">${visitor.table}</td>
                    <td style="color: #64748b;">${formatTime(visitor.checkinTime)}</td>
                    <td style="color: #64748b;">${checkOutTime}</td>
                `;
                tbody.appendChild(row);
            });
        }
    }

    // Also update analytics cards if on reports page
    generateAnalytics();
}

// Analytics and Reporting Functions
function generateAnalytics() {
    const totalVisitors = hotelVisitors.length + restaurantVisitors.length;
    const hotelCount = hotelVisitors.length;
    const restaurantCount = restaurantVisitors.reduce((sum, v) => sum + (parseInt(v.partySize) || 1), 0);

    const totalEl = document.getElementById('analytics-total-visitors');
    const hotelEl = document.getElementById('analytics-hotel-guests');
    const restEl = document.getElementById('analytics-restaurant-diners');

    if (totalEl) totalEl.textContent = hotelCount + restaurantVisitors.length;
    if (hotelEl) hotelEl.textContent = hotelCount;
    if (restEl) restEl.textContent = restaurantCount;
}

function handleReportGeneration(event) {
    event.preventDefault();
    const type = document.getElementById('report-type').value;
    const venue = document.getElementById('report-venue').value;
    const format = document.getElementById('report-format').value;

    showAlert(`Generating ${type} report for ${venue} in ${format.toUpperCase()} format...`, 'success');

    // Simulate file download
    setTimeout(() => {
        showAlert('Report generated successfully! Download starting...', 'success');
    }, 1500);
}

function previewReport() {
    const type = document.getElementById('report-type').value;
    const venue = document.getElementById('report-venue').value;
    
    const resultsContainer = document.getElementById('report-results');
    const dataContainer = document.getElementById('report-data');
    
    if (!resultsContainer || !dataContainer) return;

    resultsContainer.style.display = 'block';
    resultsContainer.scrollIntoView({ behavior: 'smooth' });

    let filteredData = [];
    if (venue === 'all' || venue === 'hotel') filteredData = [...filteredData, ...hotelVisitors];
    if (venue === 'all' || venue === 'restaurant') filteredData = [...filteredData, ...restaurantVisitors];

    let html = `
        <div style="padding: 20px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
            <h3 style="margin-top: 0;">Preview: ${type.toUpperCase()} Report</h3>
            <p style="color: #64748b;">Showing data for ${venue === 'all' ? 'All Venues' : venue.charAt(0).toUpperCase() + venue.slice(1)}</p>
            <table class="custom-table" style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Venue</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
    `;

    if (filteredData.length === 0) {
        html += '<tr><td colspan="5" style="text-align: center;">No data found for selected filters</td></tr>';
    } else {
        filteredData.slice(0, 10).forEach(item => {
            html += `
                <tr>
                    <td>${item.name}</td>
                    <td style="text-transform: capitalize;">${item.venue || 'Hotel'}</td>
                    <td>${formatDate(item.checkinTime)}</td>
                    <td>${item.checkoutTime ? formatDate(item.checkoutTime) : '-'}</td>
                    <td>${item.status}</td>
                </tr>
            `;
        });
        if (filteredData.length > 10) {
            html += `<tr><td colspan="5" style="text-align: center; color: #64748b; font-style: italic;">...and ${filteredData.length - 10} more records</td></tr>`;
        }
    }

    html += `
                </tbody>
            </table>
        </div>
    `;

    dataContainer.innerHTML = html;
}

// Function to view visitor details in a modal
function viewVisitorDetails(guestId) {
    fetch(API_BASE_URL)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data) {
                const guest = data.data.find(g => String(g.id) === String(guestId));
                if (guest) {
                    const venue = guest.venue || 'hotel';
                    let detailsHtml = `
                    <div style="text-align: left; line-height: 1.6; color: #1e293b;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                            <div>
                                <p style="margin: 0; font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Name</p>
                                <p style="margin: 0; font-weight: 600;">${guest.full_name}</p>
                            </div>
                            <div>
                                <p style="margin: 0; font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Venue</p>
                                <p style="margin: 0; font-weight: 600; text-transform: capitalize;">${venue}</p>
                            </div>
                            <div>
                                <p style="margin: 0; font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Phone</p>
                                <p style="margin: 0; font-weight: 600;">${guest.phone_number || 'N/A'}</p>
                            </div>
                            <div>
                                <p style="margin: 0; font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Email</p>
                                <p style="margin: 0; font-weight: 600;">${guest.email || 'N/A'}</p>
                            </div>
                    `;

                    if (venue === 'hotel') {
                        detailsHtml += `
                            <div>
                                <p style="margin: 0; font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Room</p>
                                <p style="margin: 0; font-weight: 600;">${guest.room_number || 'N/A'}</p>
                            </div>
                        `;
                    } else {
                        detailsHtml += `
                            <div>
                                <p style="margin: 0; font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Party Size</p>
                                <p style="margin: 0; font-weight: 600;">${guest.party_size || '1'}</p>
                            </div>
                            <div>
                                <p style="margin: 0; font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Table</p>
                                <p style="margin: 0; font-weight: 600;">${guest.table_number || 'N/A'}</p>
                            </div>
                        `;
                    }

                    detailsHtml += `
                            <div>
                                <p style="margin: 0; font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Check-in</p>
                                <p style="margin: 0; font-weight: 600;">${formatDate(guest.checkin_date)}</p>
                            </div>
                            <div>
                                <p style="margin: 0; font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Status</p>
                                <p style="margin: 0; font-weight: 600; text-transform: uppercase;">${guest.status}</p>
                            </div>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <p style="margin: 0; font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Host / Server</p>
                            <p style="margin: 0; font-weight: 600;">${guest.host_id || 'N/A'}</p>
                        </div>
                        <div style="margin-bottom: 25px;">
                            <p style="margin: 0; font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Notes</p>
                            <p style="margin: 0; font-weight: 600;">${guest.notes || 'No notes provided.'}</p>
                        </div>
                        <button onclick="closeDetailsModal(); openEntryModal('${venue}', {id: '${guest.id}', name: '${guest.full_name.replace(/'/g, "\\'")}', email: '${guest.email || ''}', phone: '${guest.phone_number || ''}', room: '${guest.room_number || ''}', host: '${guest.host_id || ''}', notes: '${(guest.notes || '').replace(/'/g, "\\'").replace(/\n/g, "\\n")}', partySize: '${guest.party_size || ''}', table: '${guest.table_number || ''}'})" 
                                style="width: 100%; padding: 12px; background: #3b82f6; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                            <i class="fas fa-edit"></i> Edit Entry
                        </button>
                    </div>
                `;
                    if (typeof showDetailsModal === 'function') {
                        showDetailsModal('Visitor Details', detailsHtml);
                    } else {
                        alert('Visitor: ' + guest.full_name);
                    }
                }
            }
        });
}







// Generate reports
function generateReport() {
    const reportType = document.getElementById('report-type').value;
    const venue = document.getElementById('report-venue').value;
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;

    let reportData = '';

    // Calculate date range based on report type
    let dateRange = { start: new Date(), end: new Date() };

    switch (reportType) {
        case 'daily':
            dateRange.start.setHours(0, 0, 0, 0);
            dateRange.end.setHours(23, 59, 59, 999);
            break;
        case 'weekly':
            // Start of week (Sunday)
            dateRange.start.setDate(dateRange.start.getDate() - dateRange.start.getDay());
            dateRange.start.setHours(0, 0, 0, 0);
            // End of week (Saturday)
            dateRange.end.setDate(dateRange.start.getDate() + 6);
            dateRange.end.setHours(23, 59, 59, 999);
            break;
        case 'monthly':
            // Start of month
            dateRange.start.setDate(1);
            dateRange.start.setHours(0, 0, 0, 0);
            // End of month
            dateRange.end.setMonth(dateRange.end.getMonth() + 1, 0);
            dateRange.end.setHours(23, 59, 59, 999);
            break;
        case 'custom':
            dateRange.start = new Date(startDate);
            dateRange.end = new Date(endDate);
            dateRange.end.setHours(23, 59, 59, 999);
            break;
    }

    // Filter data based on venue and date range
    let hotelData = [];
    let restaurantData = [];

    if (venue === 'all' || venue === 'hotel') {
        hotelData = hotelVisitors.filter(guest => {
            const checkinDate = new Date(guest.checkinTime);
            return checkinDate >= dateRange.start && checkinDate <= dateRange.end;
        });
    }

    if (venue === 'all' || venue === 'restaurant') {
        restaurantData = restaurantVisitors.filter(visitor => {
            const checkinDate = new Date(visitor.checkinTime);
            return checkinDate >= dateRange.start && checkinDate <= dateRange.end;
        });
    }

    // Generate report content

    if (venue === 'all' || venue === 'hotel') {

        if (hotelData.length > 0) {
            reportData += `<table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>`;

            hotelData.forEach(guest => {
                const itemData = JSON.stringify({
                    name: guest.name,
                    venue: 'Hotel',
                    checkin: formatDate(guest.checkinTime),
                    checkout: guest.checkoutTime ? formatDate(guest.checkoutTime) : 'N/A',
                    notes: guest.notes || 'None'
                });
                reportData += `
                            <tr data-item='${itemData.replace(/'/g, "&apos;")}'>
                                <td>${guest.name}</td>
                                <td>${guest.room}</td>
                                <td>${formatDate(guest.checkinTime)}</td>
                                <td>${guest.checkoutTime ? formatDate(guest.checkoutTime) : 'CHECKED IN'}</td>
                                <td style="display:flex; gap:5px;">
                                    <span class="status-badge ${guest.status === 'timed-in' ? 'status-active' : 'status-completed'}">
                                        ${guest.status === 'timed-in' ? 'CHECKED IN' : guest.status}
                                    </span>
                                    <button class="view-btn" style="padding: 2px 8px; font-size: 12px; cursor: pointer;">View</button>
                                </td>
                            </tr>`;
            });

            reportData += `</tbody></table>`;
        }
    }

    if (venue === 'all' || venue === 'restaurant') {

        if (restaurantData.length > 0) {
            reportData += `<table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Party Size</th>
                                <th>Table</th>
                                <th>Time-in</th>
                                <th>Time-out</th>
                            </tr>
                        </thead>
                        <tbody>`;

            restaurantData.forEach(visitor => {
                const itemData = JSON.stringify({
                    name: visitor.name,
                    venue: 'Restaurant',
                    checkin: formatTime(visitor.checkinTime),
                    checkout: visitor.checkoutTime ? formatTime(visitor.checkoutTime) : 'N/A',
                    notes: visitor.notes || 'None'
                });
                reportData += `
                            <tr data-item='${itemData.replace(/'/g, "&apos;")}'>
                                <td>${visitor.name}</td>
                                <td>${visitor.partySize}</td>
                                <td>${visitor.table}</td>
                                <td>${formatTime(visitor.checkinTime)}</td>
                                <td style="display:flex; gap:5px; justify-content:space-between; align-items:center;">
                                    ${visitor.checkoutTime ? formatTime(visitor.checkoutTime) : 'CHECKED IN'}
                                    <button class="view-btn" style="padding: 2px 8px; font-size: 12px; cursor: pointer;">View</button>
                                </td>
                            </tr>`;
            });

            reportData += `</tbody></table>`;
        }
    }

    // Display report
    document.getElementById('report-results').style.display = 'block';
    const reportDisplay = document.getElementById('report-data');
    
    // Summary Data for Cards
    const totalCount = hotelData.length + restaurantData.length;
    const hotelInCount = hotelData.filter(g => g.status === 'timed-in').length;
    const restInCount = restaurantData.filter(v => v.status === 'timed-in').length;

    reportDisplay.innerHTML = `
        <div style="margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #f1f5f9;">
            <h3 style="color: #1e293b; font-size: 1.25rem; margin-bottom: 4px;"><i class="fas fa-chart-line"></i> Summary Report</h3>
            <p style="color: #64748b; font-size: 0.9rem;">Period: <strong>${formatDate(dateRange.start)}</strong> to <strong>${formatDate(dateRange.end)}</strong></p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 30px;">
            <div style="background: #f8fafc; padding: 18px; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <div style="color: #3b82f6; margin-bottom: 10px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;"><i class="fas fa-users-rectangle"></i> Total Visitors</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #0f172a;">${totalCount}</div>
            </div>
            <div style="background: #f0fdf4; padding: 18px; border-radius: 14px; border: 1px solid #dcfce7; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <div style="color: #10b981; margin-bottom: 10px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;"><i class="fas fa-user-check"></i> Hotel Active</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #0f172a;">${hotelInCount}</div>
            </div>
            <div style="background: #fffbeb; padding: 18px; border-radius: 14px; border: 1px solid #fef3c7; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <div style="color: #f59e0b; margin-bottom: 10px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;"><i class="fas fa-utensils"></i> Rest. Dining</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #0f172a;">${restInCount}</div>
            </div>
        </div>

        <div style="padding: 15px; background: #eff6ff; border-radius: 12px; color: #1e40af; font-size: 0.85rem; display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
            <i class="fas fa-file-excel"></i>
            <span>Excel (.csv) report has been successfully generated and downloaded to your computer.</span>
        </div>

        <div class="table-container">
            ${reportData}
        </div>
    `;

    // --- AUTO-DOWNLOAD EXCEL (CSV) LOGIC ---
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "========================================\n";
    csvContent += "VISITOR MANAGEMENT REPORT SUMMARY\n";
    csvContent += "========================================\n";
    csvContent += `Start Date,${formatDate(dateRange.start)},\n`;
    csvContent += `End Date,${formatDate(dateRange.end)},\n`;
    csvContent += `Generated On,${new Date().toLocaleString()},\n\n`;
    
    csvContent += "--- OVERALL METRICS ---\n";
    csvContent += "SECTION,METRIC,VALUE\n";
    csvContent += `OVERALL,Total Visitors,${totalCount}\n`;
    csvContent += `HOTEL,Currently Checked-in,${hotelInCount}\n`;
    csvContent += `RESTAURANT,Currently Dining,${restInCount}\n\n`;
    
    if (hotelData.length > 0) {
        csvContent += "--- HOTEL VISITOR LOGS ---\n";
        csvContent += "Name,Room/Facility,Check-in Date,Check-out Date,Status,Notes\n";
        hotelData.forEach(g => {
            const checkout = g.checkoutTime ? formatDate(g.checkoutTime) : "IN-PROGRESS";
            const note = g.notes ? g.notes.replace(/,/g, " ") : "None";
            csvContent += `"${g.name}","${g.room || g.room_number || 'N/A'}","${formatDate(g.checkinTime)}","${checkout}","${g.status}","${note}"\n`;
        });
        csvContent += "\n";
    }

    if (restaurantData.length > 0) {
        csvContent += "--- RESTAURANT VISITOR LOGS ---\n";
        csvContent += "Name,Table Number,Party Size,Check-in Time,Check-out Time,Notes\n";
        restaurantData.forEach(v => {
            const checkin = formatTime(v.checkinTime);
            const checkout = v.checkoutTime ? formatTime(v.checkoutTime) : 'DINING';
            const note = v.notes ? v.notes.replace(/,/g, " ") : "None";
            csvContent += `"${v.name}","${v.table || v.table_number || 'N/A'}","${v.partySize}","${checkin}","${checkout}","${note}"\n`;
        });
    }

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `Atiera_Visitors_Summary_${reportType}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}



// Load employees for host selection from HR4 API
function loadEmployeesForHosts() {
    const hr4ApiUrl = '../integ/hr4_api.php?limit=10';
    const hostSelects = [
        document.getElementById('host_id'),
        document.getElementById('restaurant-host')
    ];

    fetch(hr4ApiUrl)
        .then(response => response.json())
        .then(res => {
            if (res.success && res.data) {
                hostSelects.forEach(select => {
                    if (!select) return;

                    // Clear existing options except first
                    const firstOption = select.options[0];
                    select.innerHTML = '';
                    select.appendChild(firstOption);

                    res.data.forEach(employee => {
                        const option = document.createElement('option');
                        option.value = employee.employee_id || employee.id;
                        const pos = employee.employment_details ? (employee.employment_details.job_title || 'Employee') : (employee.position || 'Employee');
                        option.textContent = `${employee.first_name} ${employee.last_name} (${pos})`;
                        select.appendChild(option);
                    });
                });
            }
        })
        .catch(error => {
            console.error('Error loading employees for hosts:', error);
        });
}

// Utility functions
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString();
}

function formatTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function showAlert(message, type) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());

    // Create new alert
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;

    // Insert at the top of the current page
    const currentPage = document.querySelector('.page.active');
    if (currentPage) {
        currentPage.insertBefore(alert, currentPage.firstChild);
    }

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 5000);
}

// --- Details Modal ---
function showDetailsModal(title, content) {
    const titleEl = document.getElementById('details-modal-title');
    const bodyEl = document.getElementById('details-modal-body');
    const modalEl = document.getElementById('details-modal');

    if (titleEl) titleEl.innerText = title;
    if (bodyEl) bodyEl.innerHTML = content;
    if (modalEl) modalEl.style.display = 'block';
}

function closeDetailsModal() {
    const modalEl = document.getElementById('details-modal');
    if (modalEl) modalEl.style.display = 'none';
}

// --- Confirmation Modal ---
function showConfirmationModal(message, callback) {
    const msgEl = document.getElementById('confirmation-message');
    const modalEl = document.getElementById('confirmation-modal');
    const confirmBtn = document.getElementById('confirm-btn');

    if (msgEl) msgEl.innerText = message;
    if (modalEl) modalEl.style.display = 'block';

    if (confirmBtn) {
        const newBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);
        newBtn.onclick = function () {
            callback();
            closeConfirmationModal();
        };
    }
}

function closeConfirmationModal() {
    const modalEl = document.getElementById('confirmation-modal');
    if (modalEl) modalEl.style.display = 'none';
}

// --- Window Click Handler ---
window.addEventListener('click', function (event) {
    const confirmModal = document.getElementById('confirmation-modal');
    const detailsModal = document.getElementById('details-modal');

    if (event.target == confirmModal) {
        closeConfirmationModal();
    }
    if (event.target == detailsModal) {
        closeDetailsModal();
    }
});

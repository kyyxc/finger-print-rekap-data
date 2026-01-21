@extends('layouts.sidebar')

@section('title', 'Kelola Jadwal')

@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            min-height: 60px;
        }

        .calendar-day:hover:not(.empty):not(.day-header) {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .calendar-day.empty {
            cursor: default;
            background: transparent;
        }

        .calendar-day.day-header {
            cursor: default;
            font-weight: 600;
            color: #64748b;
            font-size: 0.75rem;
            aspect-ratio: auto;
            min-height: 40px;
        }

        .calendar-day.aktif {
            background: #f1f5f9;
            color: #1e293b;
        }

        .calendar-day.libur {
            background: #fecaca;
            color: #dc2626;
            font-weight: 600;
        }

        .calendar-day.today {
            border: 2px solid #3b82f6;
        }

        .calendar-day.saturday {
            color: #dc2626;
        }

        .calendar-day .day-number {
            font-size: 1rem;
            font-weight: 500;
        }

        .calendar-day .day-label {
            font-size: 0.6rem;
            margin-top: 2px;
            text-transform: uppercase;
            opacity: 0.8;
        }

        .calendar-day .time-label {
            font-size: 0.5rem;
            margin-top: 1px;
            color: #059669;
            font-weight: 500;
        }

        .month-nav {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .month-nav button {
            padding: 0.5rem;
            border-radius: 8px;
            background: #f1f5f9;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .month-nav button:hover {
            background: #e2e8f0;
        }

        /* Modal styles */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 24px;
            max-width: 400px;
            width: 90%;
            transform: scale(0.9);
            transition: all 0.3s ease;
        }

        .modal-overlay.active .modal-content {
            transform: scale(1);
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }

        @media (max-width: 640px) {
            .calendar-grid {
                gap: 2px;
            }

            .calendar-day {
                min-height: 40px;
                aspect-ratio: auto;
                padding: 4px 2px;
            }

            .calendar-day .day-number {
                font-size: 0.75rem;
            }

            .calendar-day .day-label {
                display: none;
            }

            .calendar-day .time-label {
                display: none;
            }

            .calendar-day.day-header {
                min-height: 30px;
                font-size: 0.65rem;
            }

            .month-nav {
                gap: 0.5rem;
            }

            .month-nav h2 {
                font-size: 1rem !important;
                min-width: 150px !important;
            }

            .legend-item {
                font-size: 0.75rem;
            }

            .legend-color {
                width: 16px;
                height: 16px;
            }
        }

        /* Fix untuk beberapa browser mobile */
        @supports not (aspect-ratio: 1) {
            .calendar-day {
                padding-top: 50%;
                position: relative;
            }

            .calendar-day > * {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }
        }
    </style>
@endpush

@section('content')
    <div class="max-w-5xl mx-auto px-2 sm:px-0">
        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4 sm:mb-6">
            <div>
                <h1 class="text-xl sm:text-3xl font-bold text-gray-800">Kelola Jadwal</h1>
                <p class="text-gray-500 mt-1 text-sm sm:text-base">Atur jadwal libur dan hari aktif</p>
            </div>
        </div>

        {{-- NOTIFIKASI --}}
        <div>
            <div id="notification" class="hidden bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-4 sm:mb-6" role="alert">
                <p id="notification-message"></p>
            </div>
        </div>

        {{-- CALENDAR CARD --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 sm:p-6">
            {{-- Calendar Navigation --}}
            <div class="flex flex-col sm:flex-row justify-between items-center mb-4 sm:mb-6 gap-3 sm:gap-4">
                <div class="month-nav">
                    <button onclick="prevMonth()" class="p-2 hover:bg-gray-200 rounded-lg transition-colors">
                        <span class="material-icons">chevron_left</span>
                    </button>
                    <h2 class="text-lg sm:text-xl font-bold text-gray-800 min-w-[160px] sm:min-w-[200px] text-center" id="currentMonth">
                        {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}
                    </h2>
                    <button onclick="nextMonth()" class="p-2 hover:bg-gray-200 rounded-lg transition-colors">
                        <span class="material-icons">chevron_right</span>
                    </button>
                </div>
                <button onclick="goToToday()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors text-sm">
                    Hari Ini
                </button>
            </div>

            {{-- Legend --}}
            <div class="flex flex-wrap gap-3 sm:gap-6 mb-4 sm:mb-6 p-3 sm:p-4 bg-gray-50 rounded-lg">
                <div class="legend-item">
                    <div class="legend-color bg-gray-100 border border-gray-200"></div>
                    <span class="text-gray-600">Aktif</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color bg-red-200"></div>
                    <span class="text-gray-600">Libur</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color bg-white border-2 border-blue-500"></div>
                    <span class="text-gray-600">Hari Ini</span>
                </div>
            </div>

            {{-- Default Time Settings --}}
            <div class="mb-4 p-3 sm:p-4 bg-emerald-50 rounded-lg">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <span class="text-xs sm:text-sm text-emerald-700 font-medium">Jam Default:</span>
                    <button onclick="openDefaultModal()" class="text-xs px-2 sm:px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors">
                        <span class="material-icons text-xs align-middle">settings</span> <span class="hidden sm:inline">Atur Jam Default</span><span class="sm:hidden">Setting</span>
                    </button>
                </div>
                <div class="grid grid-cols-4 sm:grid-cols-4 lg:grid-cols-7 gap-1 sm:gap-2 text-xs" id="defaultTimeDisplay">
                    {{-- Will be rendered by JS --}}
                </div>
            </div>

            {{-- Calendar Grid --}}
            <div>
                <div class="calendar-grid" id="calendarGrid">
                    {{-- Calendar will be rendered here --}}
                </div>
            </div>

            {{-- Instructions --}}
            <div class="mt-4 sm:mt-6 p-3 sm:p-4 bg-blue-50 rounded-lg">
                <p class="text-xs sm:text-sm text-blue-700">
                    <span class="font-semibold">Petunjuk:</span> Klik tanggal untuk atur jadwal. Merah = libur.
                </p>
            </div>
        </div>
    </div>

    {{-- MODAL for editing default schedules --}}
    <div id="modal-default-schedules" class="modal-overlay">
        <div class="modal-content max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800">Atur Jam Default Per Hari</h3>
                <button onclick="closeDefaultModal()" class="p-1 hover:bg-gray-100 rounded-lg transition-colors">
                    <span class="material-icons text-gray-500">close</span>
                </button>
            </div>

            <p class="text-sm text-gray-500 mb-4">Atur jam datang dan pulang default untuk setiap hari dalam seminggu.</p>

            <div class="space-y-3" id="defaultSchedulesList">
                {{-- Will be rendered by JS --}}
            </div>

            <div class="mt-6 flex gap-3">
                <button onclick="closeDefaultModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL for editing date --}}
    <div id="modal-edit-date" class="modal-overlay">
        <div class="modal-content max-w-md">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800">Edit Jadwal</h3>
                <button onclick="closeModal()" class="p-1 hover:bg-gray-100 rounded-lg transition-colors">
                    <span class="material-icons text-gray-500">close</span>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-500">Tanggal</p>
                <p class="text-lg font-semibold text-gray-800" id="modal-date-display">-</p>
                <p class="text-xs text-gray-400" id="modal-day-name">-</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Jadwal</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="schedule-type" value="aktif" class="w-4 h-4 text-blue-600" checked onchange="toggleTimeFields()">
                        <span class="text-gray-700">Hari Aktif</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="schedule-type" value="libur" class="w-4 h-4 text-red-600" onchange="toggleTimeFields()">
                        <span class="text-gray-700">Hari Libur</span>
                    </label>
                </div>
            </div>

            {{-- Time Fields (only show for aktif) --}}
            <div id="time-fields" class="mb-4 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm font-medium text-gray-700 mb-3">Jam Kerja Khusus (Opsional)</p>
                <p id="default-time-hint" class="text-xs text-gray-500 mb-3">Kosongkan untuk menggunakan jam default</p>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            <span class="material-icons text-xs align-middle">login</span> Jam Datang
                        </label>
                        <input type="time" id="schedule-jam-datang"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            <span class="material-icons text-xs align-middle">logout</span> Jam Pulang
                        </label>
                        <input type="time" id="schedule-jam-pulang"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan (Opsional)</label>
                <input type="text" id="schedule-description" placeholder="Contoh: Libur Tahun Baru"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="flex gap-3">
                <button onclick="closeModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Batal
                </button>
                <button onclick="saveSchedule()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    Simpan
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // State
    let currentYear = {{ $year }};
    let currentMonth = {{ $month }};
    let selectedDate = null;
    let selectedDayOfWeek = null;
    let schedules = @json($schedules);
    let defaultSchedulesRaw = @json($defaultSchedules);

    // Convert defaultSchedules to proper format (indexed by day_of_week)
    let defaultSchedules = {};
    if (Array.isArray(defaultSchedulesRaw)) {
        defaultSchedulesRaw.forEach(item => {
            defaultSchedules[item.day_of_week] = item;
        });
    } else if (typeof defaultSchedulesRaw === 'object') {
        defaultSchedules = defaultSchedulesRaw;
    }

    console.log('Default Schedules:', defaultSchedules);

    const monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    const dayNamesFull = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Loaded, rendering...');
        renderDefaultTimeDisplay();
        renderCalendar();
    });

    function getDefaultSchedule(dayOfWeek) {
        return defaultSchedules[dayOfWeek] || defaultSchedules[String(dayOfWeek)] || null;
    }

    function renderDefaultTimeDisplay() {
        const container = document.getElementById('defaultTimeDisplay');
        if (!container) {
            console.error('defaultTimeDisplay container not found');
            return;
        }
        container.innerHTML = '';

        dayNamesFull.forEach((dayName, index) => {
            const defaultSchedule = getDefaultSchedule(index);
            const isHoliday = defaultSchedule && (defaultSchedule.is_holiday === true || defaultSchedule.is_holiday === 1);

            const div = document.createElement('div');
            div.className = `p-2 rounded text-center ${isHoliday ? 'bg-red-100 text-red-600' : 'bg-white text-emerald-700'}`;

            if (isHoliday) {
                div.innerHTML = `<div class="font-semibold">${dayName.substring(0, 3)}</div><div class="text-xs">Libur</div>`;
            } else {
                const jamDatang = defaultSchedule?.jam_datang ? defaultSchedule.jam_datang.substring(0, 5) : '-';
                const jamPulang = defaultSchedule?.jam_pulang ? defaultSchedule.jam_pulang.substring(0, 5) : '-';
                div.innerHTML = `<div class="font-semibold">${dayName.substring(0, 3)}</div><div class="text-xs">${jamDatang}</div><div class="text-xs">${jamPulang}</div>`;
            }

            container.appendChild(div);
        });
    }

    function renderDefaultSchedulesList() {
        const container = document.getElementById('defaultSchedulesList');
        if (!container) {
            console.error('defaultSchedulesList container not found');
            return;
        }
        container.innerHTML = '';

        dayNamesFull.forEach((dayName, index) => {
            const defaultSchedule = getDefaultSchedule(index);
            const isHoliday = defaultSchedule ? (defaultSchedule.is_holiday === true || defaultSchedule.is_holiday === 1) : false;
            const jamDatang = defaultSchedule?.jam_datang ? defaultSchedule.jam_datang.substring(0, 5) : '';
            const jamPulang = defaultSchedule?.jam_pulang ? defaultSchedule.jam_pulang.substring(0, 5) : '';

            const div = document.createElement('div');
            div.className = 'p-3 bg-gray-50 rounded-lg';
            div.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <span class="font-medium text-gray-800">${dayName}</span>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="default-holiday-${index}" ${isHoliday ? 'checked' : ''}
                            onchange="toggleDefaultHoliday(${index})" class="w-4 h-4 text-red-600 rounded">
                        <span class="text-sm text-gray-600">Libur</span>
                    </label>
                </div>
                <div id="default-time-${index}" class="grid grid-cols-2 gap-2 ${isHoliday ? 'hidden' : ''}">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Jam Datang</label>
                        <input type="time" id="default-datang-${index}" value="${jamDatang}"
                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Jam Pulang</label>
                        <input type="time" id="default-pulang-${index}" value="${jamPulang}"
                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>
                <button onclick="saveDefaultSchedule(${index})" class="mt-2 w-full px-3 py-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded transition-colors">
                    Simpan
                </button>
            `;
            container.appendChild(div);
        });
    }

    function toggleDefaultHoliday(dayOfWeek) {
        const isHoliday = document.getElementById(`default-holiday-${dayOfWeek}`).checked;
        const timeContainer = document.getElementById(`default-time-${dayOfWeek}`);

        if (isHoliday) {
            timeContainer.classList.add('hidden');
        } else {
            timeContainer.classList.remove('hidden');
        }
    }

    function saveDefaultSchedule(dayOfWeek) {
        const isHoliday = document.getElementById(`default-holiday-${dayOfWeek}`).checked;
        const jamDatang = document.getElementById(`default-datang-${dayOfWeek}`).value;
        const jamPulang = document.getElementById(`default-pulang-${dayOfWeek}`).value;

        fetch('{{ route('admin.schedules.defaults.update') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                day_of_week: dayOfWeek,
                jam_datang: jamDatang || null,
                jam_pulang: jamPulang || null,
                is_holiday: isHoliday
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                defaultSchedules[dayOfWeek] = data.default;
                renderDefaultTimeDisplay();
                renderCalendar();
            } else {
                showNotification(data.message || 'Terjadi kesalahan', 'error');
            }
        })
        .catch(error => {
            console.error('Error saving default schedule:', error);
            showNotification('Terjadi kesalahan saat menyimpan', 'error');
        });
    }

    function openDefaultModal() {
        console.log('Opening default modal...');
        renderDefaultSchedulesList();
        document.getElementById('modal-default-schedules').classList.add('active');
    }

    function closeDefaultModal() {
        document.getElementById('modal-default-schedules').classList.remove('active');
    }

    function renderCalendar() {
        const grid = document.getElementById('calendarGrid');
        if (!grid) {
            console.error('calendarGrid container not found');
            return;
        }
        grid.innerHTML = '';

        // Render day headers
        dayNames.forEach((day, index) => {
            const defaultSchedule = getDefaultSchedule(index);
            const isDefaultHoliday = defaultSchedule && (defaultSchedule.is_holiday === true || defaultSchedule.is_holiday === 1);

            const header = document.createElement('div');
            header.className = `calendar-day day-header ${isDefaultHoliday ? 'text-red-500' : ''}`;
            header.textContent = day;
            grid.appendChild(header);
        });

        // Get first day and total days in month
        const firstDay = new Date(currentYear, currentMonth - 1, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();

        // Get today's date
        const today = new Date();
        const isCurrentMonth = today.getFullYear() === currentYear && (today.getMonth() + 1) === currentMonth;

        // Empty cells before first day
        for (let i = 0; i < firstDay; i++) {
            const empty = document.createElement('div');
            empty.className = 'calendar-day empty';
            grid.appendChild(empty);
        }

        // Render days
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dayOfWeek = new Date(currentYear, currentMonth - 1, day).getDay();
            const isToday = isCurrentMonth && today.getDate() === day;

            const defaultSchedule = getDefaultSchedule(dayOfWeek);
            const isDefaultHoliday = defaultSchedule && (defaultSchedule.is_holiday === true || defaultSchedule.is_holiday === 1);

            const schedule = schedules[dateStr];
            // Check if holiday: either explicit holiday in schedule, or default holiday without override
            const isHoliday = (schedule && schedule.type === 'libur') || (isDefaultHoliday && (!schedule || schedule.type !== 'aktif'));

            const dayElement = document.createElement('div');
            dayElement.className = `calendar-day ${isHoliday ? 'libur' : 'aktif'} ${isToday ? 'today' : ''}`;
            dayElement.onclick = () => openModal(dateStr, day, dayOfWeek);

            const numberEl = document.createElement('span');
            numberEl.className = 'day-number';
            numberEl.textContent = day;
            dayElement.appendChild(numberEl);

            // Show description or holiday label
            if (schedule && schedule.description) {
                const labelEl = document.createElement('span');
                labelEl.className = 'day-label';
                labelEl.textContent = schedule.description.substring(0, 8);
                dayElement.appendChild(labelEl);
            } else if (isHoliday && isDefaultHoliday) {
                const labelEl = document.createElement('span');
                labelEl.className = 'day-label';
                labelEl.textContent = dayNamesFull[dayOfWeek].substring(0, 3);
                dayElement.appendChild(labelEl);
            } else if (isHoliday) {
                const labelEl = document.createElement('span');
                labelEl.className = 'day-label';
                labelEl.textContent = 'Libur';
                dayElement.appendChild(labelEl);
            }

            // Show custom time if exists, or default time
            if (!isHoliday) {
                const timeEl = document.createElement('span');
                timeEl.className = 'time-label';

                let jamDatang, jamPulang;
                if (schedule && (schedule.jam_datang || schedule.jam_pulang)) {
                    jamDatang = schedule.jam_datang ? schedule.jam_datang.substring(0, 5) : (defaultSchedule?.jam_datang?.substring(0, 5) || '-');
                    jamPulang = schedule.jam_pulang ? schedule.jam_pulang.substring(0, 5) : (defaultSchedule?.jam_pulang?.substring(0, 5) || '-');
                } else if (defaultSchedule && !isDefaultHoliday) {
                    jamDatang = defaultSchedule.jam_datang ? defaultSchedule.jam_datang.substring(0, 5) : '-';
                    jamPulang = defaultSchedule.jam_pulang ? defaultSchedule.jam_pulang.substring(0, 5) : '-';
                }

                if (jamDatang && jamPulang) {
                    timeEl.textContent = `${jamDatang}-${jamPulang}`;
                    dayElement.appendChild(timeEl);
                }
            }

            grid.appendChild(dayElement);
        }

        // Update month display
        document.getElementById('currentMonth').textContent = `${monthNames[currentMonth - 1]} ${currentYear}`;
    }

    function prevMonth() {
        currentMonth--;
        if (currentMonth < 1) {
            currentMonth = 12;
            currentYear--;
        }
        fetchSchedules();
    }

    function nextMonth() {
        currentMonth++;
        if (currentMonth > 12) {
            currentMonth = 1;
            currentYear++;
        }
        fetchSchedules();
    }

    function goToToday() {
        const today = new Date();
        currentYear = today.getFullYear();
        currentMonth = today.getMonth() + 1;
        fetchSchedules();
    }

    function fetchSchedules() {
        fetch(`{{ route('admin.schedules.get') }}?year=${currentYear}&month=${currentMonth}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    schedules = data.schedules;
                    if (data.defaultSchedules) {
                        // Convert defaultSchedules to proper format
                        let rawDefaults = data.defaultSchedules;
                        defaultSchedules = {};
                        if (Array.isArray(rawDefaults)) {
                            rawDefaults.forEach(item => {
                                defaultSchedules[item.day_of_week] = item;
                            });
                        } else if (typeof rawDefaults === 'object') {
                            defaultSchedules = rawDefaults;
                        }
                        renderDefaultTimeDisplay();
                    }
                    renderCalendar();
                }
            })
            .catch(error => {
                console.error('Error fetching schedules:', error);
            });
    }

    function openModal(dateStr, day, dayOfWeek) {
        selectedDate = dateStr;
        selectedDayOfWeek = dayOfWeek;

        const formattedDate = `${day} ${monthNames[currentMonth - 1]} ${currentYear}`;
        document.getElementById('modal-date-display').textContent = formattedDate;
        document.getElementById('modal-day-name').textContent = dayNamesFull[dayOfWeek];

        const defaultSchedule = getDefaultSchedule(dayOfWeek);
        const isDefaultHoliday = defaultSchedule && (defaultSchedule.is_holiday === true || defaultSchedule.is_holiday === 1);

        // Check if schedule exists
        const schedule = schedules[dateStr];
        if (schedule) {
            document.querySelector(`input[name="schedule-type"][value="${schedule.type}"]`).checked = true;
            document.getElementById('schedule-description').value = schedule.description || '';
            document.getElementById('schedule-jam-datang').value = schedule.jam_datang ? schedule.jam_datang.substring(0, 5) : '';
            document.getElementById('schedule-jam-pulang').value = schedule.jam_pulang ? schedule.jam_pulang.substring(0, 5) : '';
        } else {
            // Default based on default schedule settings
            if (isDefaultHoliday) {
                document.querySelector('input[name="schedule-type"][value="libur"]').checked = true;
            } else {
                document.querySelector('input[name="schedule-type"][value="aktif"]').checked = true;
            }
            document.getElementById('schedule-description').value = '';
            document.getElementById('schedule-jam-datang').value = '';
            document.getElementById('schedule-jam-pulang').value = '';
        }

        // Update placeholder with default time
        const defaultHint = document.getElementById('default-time-hint');
        if (defaultHint && defaultSchedule && !isDefaultHoliday) {
            const jamDatang = defaultSchedule.jam_datang ? defaultSchedule.jam_datang.substring(0, 5) : '-';
            const jamPulang = defaultSchedule.jam_pulang ? defaultSchedule.jam_pulang.substring(0, 5) : '-';
            defaultHint.textContent = `Kosongkan untuk menggunakan jam default (${jamDatang} - ${jamPulang})`;
        }

        toggleTimeFields();
        document.getElementById('modal-edit-date').classList.add('active');
    }

    function closeModal() {
        document.getElementById('modal-edit-date').classList.remove('active');
        selectedDate = null;
        selectedDayOfWeek = null;
    }

    function toggleTimeFields() {
        const type = document.querySelector('input[name="schedule-type"]:checked').value;
        const timeFields = document.getElementById('time-fields');

        if (type === 'aktif') {
            timeFields.style.display = 'block';
        } else {
            timeFields.style.display = 'none';
            // Clear time fields when switching to libur
            document.getElementById('schedule-jam-datang').value = '';
            document.getElementById('schedule-jam-pulang').value = '';
        }
    }

    function saveSchedule() {
        const type = document.querySelector('input[name="schedule-type"]:checked').value;
        const description = document.getElementById('schedule-description').value;
        const jamDatang = document.getElementById('schedule-jam-datang').value;
        const jamPulang = document.getElementById('schedule-jam-pulang').value;

        fetch('{{ route('admin.schedules.toggle') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                date: selectedDate,
                type: type,
                description: description,
                jam_datang: jamDatang || null,
                jam_pulang: jamPulang || null
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');

                if (data.deleted) {
                    delete schedules[selectedDate];
                } else {
                    schedules[selectedDate] = data.schedule;
                }

                renderCalendar();
                closeModal();
            } else {
                showNotification(data.message || 'Terjadi kesalahan', 'error');
            }
        })
        .catch(error => {
            console.error('Error saving schedule:', error);
            showNotification('Terjadi kesalahan saat menyimpan', 'error');
        });
    }

    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        const messageEl = document.getElementById('notification-message');

        notification.classList.remove('hidden', 'bg-green-100', 'border-green-500', 'text-green-700', 'bg-red-100', 'border-red-500', 'text-red-700');

        if (type === 'success') {
            notification.classList.add('bg-green-100', 'border-green-500', 'text-green-700');
        } else {
            notification.classList.add('bg-red-100', 'border-red-500', 'text-red-700');
        }

        messageEl.textContent = message;
        notification.classList.remove('hidden');

        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }
</script>
@endpush

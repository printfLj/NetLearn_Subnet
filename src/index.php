<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetLearn | IP & Subnetting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        input[readonly] { background-color: #1e293b; color: #94a3b8; cursor: not-allowed; }

        /* Smooth fade-in for results */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        .fade-in { animation: fadeIn 0.25s ease both; }

        /* Table: always scrollable, name column sticky on mobile */
        .subnet-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .subnet-table { min-width: 640px; }
        @media (max-width: 639px) {
            .subnet-table th:first-child,
            .subnet-table td:first-child {
                position: sticky;
                left: 0;
                background: #0f172a;
                z-index: 1;
            }
        }

        /* IPv6 address wraps instead of overflowing */
        #ipv6-full-result { word-break: break-all; }

        /* Numeric keyboard on mobile for number inputs */
        input[type=number] { -moz-appearance: textfield; }
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 min-h-screen p-3 sm:p-6 md:p-10">
    <div class="max-w-5xl mx-auto">

        <!-- Toggle Header -->
        <div class="flex justify-center mb-6 sm:mb-8">
            <div class="inline-flex rounded-lg bg-slate-800 p-1 w-full sm:w-auto">
                <button onclick="switchMode('ipv4')" id="btn-ipv4"
                    class="flex-1 sm:flex-none px-4 sm:px-6 py-2 rounded-md bg-blue-600 text-white font-bold text-sm transition">
                    IPv4 Subnetting
                </button>
                <button onclick="switchMode('ipv6')" id="btn-ipv6"
                    class="flex-1 sm:flex-none px-4 sm:px-6 py-2 rounded-md text-slate-400 hover:text-white text-sm transition">
                    IPv6 Explorer
                </button>
            </div>
        </div>

        <!-- ── IPv4 SECTION ── -->
        <div id="section-ipv4" class="space-y-4 sm:space-y-6">
            <div class="bg-slate-900 p-4 sm:p-6 rounded-xl border border-slate-800 shadow-2xl">

                <!-- Network Address + Prefix -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-2">
                            Network Address <span class="text-slate-600 normal-case">(auto-generated)</span>
                        </label>
                        <div class="flex gap-2">
                            <input type="text" id="ipv4_addr" readonly
                                class="w-full p-3 rounded-lg bg-slate-800 border border-slate-700 font-mono text-sm">
                            <button onclick="randomizeIP()" title="Randomize"
                                class="bg-blue-600 hover:bg-blue-500 active:bg-blue-700 px-4 rounded-lg transition text-lg shrink-0">🎲</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Prefix (1 – 30)</label>
                        <input type="number" id="ipv4_prefix" value="24" min="1" max="30" step="1"
                            inputmode="numeric"
                            onkeydown="if(!/[0-9]|Backspace|ArrowLeft|ArrowRight|ArrowUp|ArrowDown|Tab|Delete/.test(event.key)) event.preventDefault()"
                            onblur="sanitizePrefix(this); randomizeIP()"
                            class="w-full p-3 rounded-lg bg-slate-800 border border-slate-700 font-mono text-sm focus:border-blue-500 outline-none">
                        <p id="prefix-error" class="text-xs text-red-400 mt-1 hidden"></p>
                    </div>
                </div>

                <!-- Host Requirements -->
                <div class="mb-5">
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-3">Host Requirements</label>
                    <div id="host-rows" class="space-y-2 mb-3">
                        <div class="host-row grid grid-cols-[1fr_auto_auto] sm:grid-cols-[1fr_120px_auto] gap-2 items-center">
                            <input type="text" placeholder="Network name (e.g. Sales)"
                                class="h-name w-full p-2.5 rounded-lg bg-slate-800 border border-slate-700 text-sm">
                            <input type="number" placeholder="Hosts" min="1" step="1" inputmode="numeric"
                                onkeydown="if(!/[0-9]|Backspace|ArrowLeft|ArrowRight|ArrowUp|ArrowDown|Tab|Delete/.test(event.key)) event.preventDefault()"
                                class="h-count w-full p-2.5 rounded-lg bg-slate-800 border border-slate-700 text-sm">
                            <button onclick="this.closest('.host-row').remove()"
                                class="text-red-500 hover:text-red-400 active:text-red-600 px-2 py-2 rounded-lg hover:bg-slate-800 transition text-lg leading-none">✕</button>
                        </div>
                    </div>
                    <button onclick="addHostRow()"
                        class="text-sm text-blue-400 hover:text-blue-300 font-semibold py-1">+ Add Network</button>
                </div>

                <button onclick="calculateIPv4()"
                    class="w-full bg-blue-600 hover:bg-blue-500 active:bg-blue-700 p-3.5 sm:p-4 rounded-lg font-bold text-base sm:text-lg shadow-lg transition">
                    Generate Subnet Table
                </button>
            </div>

            <!-- IPv4 Results -->
            <div id="ipv4-results" class="hidden space-y-4 sm:space-y-6 fade-in">
                <div class="flex flex-wrap justify-between items-center gap-2">
                    <h2 class="text-lg sm:text-xl font-bold">Subnet Table</h2>
                    <span id="lsm-indicator"
                        class="px-3 py-1 bg-blue-900/50 text-blue-300 rounded-full text-xs font-bold border border-blue-700"></span>
                </div>

                <!-- Table — scrollable, name column sticky on mobile -->
                <div class="subnet-table-wrap rounded-lg border border-slate-800">
                    <table class="subnet-table w-full text-left bg-slate-900 text-sm">
                        <thead class="bg-slate-800 text-slate-400 text-xs uppercase">
                            <tr>
                                <th class="p-3 sm:p-4 whitespace-nowrap">Name</th>
                                <th class="p-3 sm:p-4 whitespace-nowrap">Req.</th>
                                <th class="p-3 sm:p-4 whitespace-nowrap">Network ID</th>
                                <th class="p-3 sm:p-4 whitespace-nowrap">Usable Range</th>
                                <th class="p-3 sm:p-4 whitespace-nowrap">Broadcast</th>
                                <th class="p-3 sm:p-4 whitespace-nowrap">Mask</th>
                                <th class="p-3 sm:p-4 whitespace-nowrap">Hosts</th>
                            </tr>
                        </thead>
                        <tbody id="ipv4-tbody"></tbody>
                    </table>
                </div>

                <!-- Steps -->
                <div class="bg-slate-900 p-4 sm:p-6 rounded-xl border border-slate-800">
                    <h3 class="text-xs font-bold uppercase text-slate-500 mb-3 tracking-widest">Calculation Steps</h3>
                    <ul id="steps-list" class="space-y-2 text-sm text-slate-400 list-disc list-inside"></ul>
                </div>
            </div>
        </div>

        <!-- ── IPv6 SECTION ── -->
        <div id="section-ipv6" class="hidden space-y-4 sm:space-y-6">
            <div class="bg-slate-900 p-4 sm:p-8 rounded-2xl border border-slate-800 shadow-2xl">
                <h3 class="text-center text-slate-500 text-xs sm:text-sm mb-5 sm:mb-6 font-mono">
                    128-bit IPv6 address structure
                </h3>

                <!-- IPv6 blocks — stack on very small screens -->
                <div class="flex flex-col sm:grid sm:grid-cols-12 gap-2 mb-6 sm:mb-8 text-center font-mono">
                    <div class="sm:col-span-5 bg-blue-900/40 p-3 sm:p-4 rounded-lg border border-blue-700">
                        <p class="text-[10px] text-blue-400 mb-1">/48 Network Prefix</p>
                        <p class="text-base sm:text-lg break-all">2001:0db8:aaaa</p>
                    </div>
                    <div class="sm:col-span-2 bg-yellow-900/40 p-3 sm:p-4 rounded-lg border border-yellow-700">
                        <p class="text-[10px] text-yellow-400 mb-1">SLA ID</p>
                        <p id="ipv6-sla-display" class="text-base sm:text-lg text-yellow-300 font-bold">0000</p>
                    </div>
                    <div class="sm:col-span-5 bg-slate-800 p-3 sm:p-4 rounded-lg border border-slate-700">
                        <p class="text-[10px] text-slate-500 mb-1">/64 Interface ID</p>
                        <p class="text-base sm:text-lg text-slate-500 break-all">0000:0000:0000:0000</p>
                    </div>
                </div>

                <div class="bg-slate-950 p-4 sm:p-6 rounded-xl text-center border border-slate-800 mb-6 sm:mb-8">
                    <p class="text-xs text-slate-500 mb-2 uppercase">Current /64 Subnet</p>
                    <p id="ipv6-full-result"
                        class="text-lg sm:text-2xl font-bold text-white tracking-wider break-all leading-snug">
                        2001:0db8:aaaa:0000::/64
                    </p>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between text-xs font-bold text-slate-500 uppercase gap-2 flex-wrap">
                        <span>Explore SLA ID (0 – 65535)</span>
                        <span id="sla-counter">Subnet 0 of 65,535</span>
                    </div>
                    <input type="range" id="sla-slider" min="0" max="65535" value="0"
                        class="w-full h-3 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-blue-600"
                        oninput="updateIPv6(this.value)">
                </div>
            </div>
        </div>

    </div><!-- /max-w -->

    <script>
        // --- Navigation ---
        function switchMode(mode) {
            document.getElementById('section-ipv4').classList.toggle('hidden', mode === 'ipv6');
            document.getElementById('section-ipv6').classList.toggle('hidden', mode === 'ipv4');
            document.getElementById('btn-ipv4').className = (mode === 'ipv4')
                ? 'flex-1 sm:flex-none px-4 sm:px-6 py-2 rounded-md bg-blue-600 text-white font-bold text-sm transition'
                : 'flex-1 sm:flex-none px-4 sm:px-6 py-2 rounded-md text-slate-400 hover:text-white text-sm transition';
            document.getElementById('btn-ipv6').className = (mode === 'ipv6')
                ? 'flex-1 sm:flex-none px-4 sm:px-6 py-2 rounded-md bg-blue-600 text-white font-bold text-sm transition'
                : 'flex-1 sm:flex-none px-4 sm:px-6 py-2 rounded-md text-slate-400 hover:text-white text-sm transition';
        }

        // --- Prefix sanitization (onblur only — doesn't interrupt mid-typing) ---
        function sanitizePrefix(el) {
            let val = parseInt(el.value) || 24;
            if (val < 1)  val = 1;
            if (val > 30) val = 30;
            el.value = val;
        }

        // --- Network Address ---
        async function randomizeIP() {
            const prefix = parseInt(document.getElementById('ipv4_prefix').value) || 24;
            const res  = await fetch(`api.php?action=generate&prefix=${prefix}`);
            const data = await res.json();
            document.getElementById('ipv4_addr').value = data.ip;
        }

        // --- Host rows ---
        function makeHostRowHTML() {
            return `
                <input type="text" placeholder="Network name (e.g. Sales)"
                    class="h-name w-full p-2.5 rounded-lg bg-slate-800 border border-slate-700 text-sm">
                <input type="number" placeholder="Hosts" min="1" step="1" inputmode="numeric"
                    onkeydown="if(!/[0-9]|Backspace|ArrowLeft|ArrowRight|ArrowUp|ArrowDown|Tab|Delete/.test(event.key)) event.preventDefault()"
                    class="h-count w-full p-2.5 rounded-lg bg-slate-800 border border-slate-700 text-sm">
                <button onclick="this.closest('.host-row').remove()"
                    class="text-red-500 hover:text-red-400 px-2 py-2 rounded-lg hover:bg-slate-800 transition text-lg leading-none">✕</button>`;
        }

        function addHostRow() {
            const div = document.createElement('div');
            div.className = 'host-row grid grid-cols-[1fr_auto_auto] sm:grid-cols-[1fr_120px_auto] gap-2 items-center';
            div.innerHTML = makeHostRowHTML();
            document.getElementById('host-rows').appendChild(div);
        }

        // --- IPv4 Calculate ---
        async function calculateIPv4() {
            const errEl  = document.getElementById('prefix-error');
            const prefix = parseInt(document.getElementById('ipv4_prefix').value);
            errEl.classList.add('hidden');

            if (isNaN(prefix) || prefix < 1 || prefix > 30) {
                errEl.innerText = 'Prefix must be between 1 and 30.';
                errEl.classList.remove('hidden');
                return;
            }

            const hosts = Array.from(document.querySelectorAll('.host-row'))
                .map(row => ({
                    name:  row.querySelector('.h-name').value.trim() || 'Unnamed',
                    count: parseInt(row.querySelector('.h-count').value) || 0
                }))
                .filter(h => h.count > 0);

            if (hosts.length === 0) {
                errEl.innerText = 'Please add at least one network with a valid host count.';
                errEl.classList.remove('hidden');
                return;
            }

            const res  = await fetch('api.php?action=calculate', {
                method:  'POST',
                headers: {'Content-Type': 'application/json'},
                body:    JSON.stringify({
                    base_ip: document.getElementById('ipv4_addr').value,
                    prefix,
                    hosts
                })
            });

            const data      = await res.json();
            const resultsEl = document.getElementById('ipv4-results');

            if (data.status === 'success') {
                resultsEl.classList.remove('hidden');
                resultsEl.classList.add('fade-in');
                document.getElementById('lsm-indicator').innerText = data.mode;
                document.getElementById('ipv4-tbody').innerHTML = data.subnets.map(s => `
                    <tr class="border-t border-slate-800 hover:bg-slate-800/40 transition">
                        <td class="p-3 sm:p-4 font-bold text-blue-400 whitespace-nowrap">
                            ${s.name} <span class="text-[10px] text-slate-500 font-normal">${s.prefix}</span>
                        </td>
                        <td class="p-3 sm:p-4 text-center text-yellow-300">${s.hosts_required}</td>
                        <td class="p-3 sm:p-4 font-mono whitespace-nowrap">${s.network_address}</td>
                        <td class="p-3 sm:p-4 font-mono text-xs whitespace-nowrap">${s.first_usable} – ${s.last_usable}</td>
                        <td class="p-3 sm:p-4 font-mono text-red-400 whitespace-nowrap">${s.broadcast}</td>
                        <td class="p-3 sm:p-4 text-xs text-slate-500 font-mono whitespace-nowrap">${s.subnet_mask}</td>
                        <td class="p-3 sm:p-4 text-center text-green-400">${s.total_hosts}</td>
                    </tr>
                `).join('');
                document.getElementById('steps-list').innerHTML = data.steps
                    .map(s => `<li>${s}</li>`).join('');
                resultsEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                errEl.innerText = data.suggestion
                    ? `Hindi Kasya! Use a /${data.suggestion} prefix or larger.`
                    : (data.message || 'An error occurred.');
                errEl.classList.remove('hidden');
                resultsEl.classList.add('hidden');
            }
        }

        // --- IPv6 Explorer ---
        function updateIPv6(val) {
            const hex = parseInt(val).toString(16).padStart(4, '0').toUpperCase();
            document.getElementById('ipv6-sla-display').innerText = hex;
            document.getElementById('ipv6-full-result').innerText = `2001:0db8:aaaa:${hex}::/64`;
            document.getElementById('sla-counter').innerText = `Subnet ${parseInt(val).toLocaleString()} of 65,535`;
        }

        window.onload = randomizeIP;
    </script>
</body>
</html>
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

                    </div>
                </div>

                <!-- Host Requirements -->
                <div class="mb-5">
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-3">Host Requirements</label>
                    <div id="host-rows" class="space-y-2 mb-3">
                        <div class="host-row grid grid-cols-[2fr_1fr_auto] sm:grid-cols-[1fr_120px_auto] gap-2 items-center">
                            <input type="text" placeholder="Network name (e.g. Sales)"
                                class="h-name w-full p-2.5 rounded-lg bg-slate-800 border border-slate-700 text-sm">
                            <input type="number" placeholder="Hosts" min="1" step="1" inputmode="numeric"
                                onkeydown="if(!/[0-9]|Backspace|ArrowLeft|ArrowRight|ArrowUp|ArrowDown|Tab|Delete/.test(event.key)) event.preventDefault()"
                                class="h-count w-full p-2.5 rounded-lg bg-slate-800 border border-slate-700 text-sm">
                            <button onclick="this.closest('.host-row').remove()"
                                class="text-red-500 hover:text-red-400 active:text-red-600 px-2 py-2 rounded-lg hover:bg-slate-800 transition text-lg leading-none">✕</button>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <button onclick="addHostRow()"
                            class="text-sm text-blue-400 hover:text-blue-300 font-semibold py-1">+ Add Network</button>
                        <button onclick="autoFillHosts()"
                            class="text-sm text-slate-400 hover:text-slate-200 font-semibold py-1 border border-slate-700 hover:border-slate-500 px-3 rounded-lg transition">Auto-fill</button>
                    </div>
                </div>

                <button onclick="calculateIPv4()"
                    class="w-full bg-blue-600 hover:bg-blue-500 active:bg-blue-700 p-3.5 sm:p-4 rounded-lg font-bold text-base sm:text-lg shadow-lg transition">
                    Generate Subnet Table
                </button>

                <!-- Detailed Error Explanation Box -->
                <div id="error-explanation" class="hidden mt-4 bg-red-950/60 border border-red-700 rounded-xl p-4 sm:p-5 text-sm space-y-3 fade-in">
                    <div class="flex items-center gap-2 text-red-400 font-bold text-base">
                        <span id="error-title">Error</span>
                    </div>
                    <p id="error-summary" class="text-red-300 font-semibold"></p>
                    <div id="error-body" class="space-y-2 text-slate-300 leading-relaxed"></div>
                    <div id="error-suggestion-box" class="hidden bg-slate-900 border border-blue-700 rounded-lg p-3 text-blue-300 text-xs">
                        <span class="font-bold text-blue-400 uppercase tracking-wide">Suggestion: </span>
                        <span id="error-suggestion-text"></span>
                    </div>
                </div>
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

                <!-- Explanation -->
                <div class="bg-slate-900 rounded-xl border border-slate-800 overflow-hidden">
                    <!-- Header / toggle -->
                    <button onclick="toggleExplanation()"
                        class="w-full flex justify-between items-center p-4 sm:p-5 text-left hover:bg-slate-800/50 transition">
                        <span class="text-xs font-bold uppercase text-slate-500 tracking-widest">How the system got this answer</span>
                        <span id="expl-chevron" class="text-slate-500 text-lg transition-transform">▼</span>
                    </button>

                    <div id="expl-body" class="hidden px-4 sm:px-6 pb-5 space-y-5 text-sm">

                        <!-- 1. Overview -->
                        <div id="expl-overview" class="bg-slate-950 rounded-lg p-4 border border-slate-800 space-y-1"></div>

                        <!-- 2. Per-subnet breakdown -->
                        <div>
                            <p class="text-xs font-bold uppercase text-slate-500 mb-3 tracking-widest">Step-by-step subnet allocation</p>
                            <div id="expl-steps" class="space-y-3"></div>
                        </div>

                        <!-- 3. Address space bar -->
                        <div>
                            <p class="text-xs font-bold uppercase text-slate-500 mb-2 tracking-widest">Address space usage</p>
                            <div class="flex rounded-lg overflow-hidden h-6 text-[10px] font-bold" id="expl-bar"></div>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2" id="expl-legend"></div>
                        </div>

                        <!-- 4. Key concepts -->
                        <div class="border-t border-slate-800 pt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-slate-400">
                            <div class="bg-slate-950 rounded-lg p-3 border border-slate-800">
                                <p class="font-bold text-slate-300 mb-1">Why powers of 2?</p>
                                <p>Subnet blocks must be a power of 2 so addresses align on binary boundaries, preventing overlaps.</p>
                            </div>
                            <div class="bg-slate-950 rounded-lg p-3 border border-slate-800">
                                <p class="font-bold text-slate-300 mb-1">Why subtract 2?</p>
                                <p>The first address is the Network ID and the last is the Broadcast — both are reserved and cannot be assigned to devices.</p>
                            </div>
                            <div id="expl-mode-card" class="bg-slate-950 rounded-lg p-3 border border-slate-800">
                                <p class="font-bold text-slate-300 mb-1">VLSM vs FLSM</p>
                                <p id="expl-mode-text"></p>
                            </div>
                        </div>
                    </div>
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

        // --- Network Address (pure JS, no server needed) ---
        function randomizeIP() {
            const prefix = parseInt(document.getElementById('ipv4_prefix').value) || 24;
            const hostBits = 32 - prefix;
            const blockSize = Math.pow(2, hostBits);

            // Pick a random aligned block from a private range
            const privateRanges = [
                { base: 10 * 16777216,                size: 16777216 }, // 10.0.0.0/8
                { base: 172 * 16777216 + 16 * 65536,  size: 1048576  }, // 172.16.0.0/12
                { base: 192 * 16777216 + 168 * 65536, size: 65536    }  // 192.168.0.0/16
            ].filter(r => r.size >= blockSize);

            const range = privateRanges[Math.floor(Math.random() * privateRanges.length)];
            const slots = Math.floor(range.size / blockSize);
            const slot  = Math.floor(Math.random() * slots);
            const addr  = range.base + slot * blockSize;

            const o1 = (addr >>> 24) & 0xff;
            const o2 = (addr >>> 16) & 0xff;
            const o3 = (addr >>>  8) & 0xff;
            const o4 =  addr         & 0xff;
            document.getElementById('ipv4_addr').value = `${o1}.${o2}.${o3}.${o4}`;
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
            div.className = 'host-row grid grid-cols-[2fr_1fr_auto] sm:grid-cols-[1fr_120px_auto] gap-2 items-center';
            div.innerHTML = makeHostRowHTML();
            document.getElementById('host-rows').appendChild(div);
        }

        // --- Auto-fill host rows from prefix ---
        function autoFillHosts() {
            const prefix    = parseInt(document.getElementById('ipv4_prefix').value) || 24;
            const hostBits  = 32 - prefix;
            const totalAddrs = Math.pow(2, hostBits); // total addresses in this network

            // How many subnets to generate — cap at 5
            // Use one subnet per available bit, up to 5
            // e.g. /24 → 8 host bits → we can make up to 5 subnets sensibly
            const subnetBits = Math.min(Math.floor(hostBits / 2), Math.floor(Math.log2(5)));
            const count      = Math.min(Math.pow(2, subnetBits), 5);

            // Spread host counts: divide remaining space roughly evenly, each as a random-ish realistic count
            // Each subnet gets a block size = totalAddrs / count, usable = blockSize - 2
            const blockSize  = Math.floor(totalAddrs / count);
            const maxUsable  = Math.max(blockSize - 2, 1);

            // Clear existing rows
            document.getElementById('host-rows').innerHTML = '';

            for (let i = 0; i < count; i++) {
                const hosts = Math.max(1, Math.floor(Math.random() * maxUsable) + 1);
                const name  = 'Host ' + (i + 1);

                const div = document.createElement('div');
                div.className = 'host-row grid grid-cols-[2fr_1fr_auto] sm:grid-cols-[1fr_120px_auto] gap-2 items-center';
                div.innerHTML = makeHostRowHTML();
                document.getElementById('host-rows').appendChild(div);

                div.querySelector('.h-name').value  = name;
                div.querySelector('.h-count').value = hosts;
            }
        }

        // --- Error explanation helpers ---
        function showError({ title, summary, body, suggestion }) {
            const box = document.getElementById('error-explanation');
            document.getElementById('error-title').innerText = title;
            document.getElementById('error-summary').innerText = summary;
            document.getElementById('error-body').innerHTML = body
                .map(line => `<p class="text-slate-300 leading-relaxed">${line}</p>`).join('');
            const sugBox  = document.getElementById('error-suggestion-box');
            const sugText = document.getElementById('error-suggestion-text');
            if (suggestion) {
                sugText.innerText = suggestion;
                sugBox.classList.remove('hidden');
            } else {
                sugBox.classList.add('hidden');
            }
            box.classList.remove('hidden');
            box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function hideError() {
            document.getElementById('error-explanation').classList.add('hidden');
        }

        // --- Pure-JS subnet calculator (mirrors SubnetCalculator.php) ---
        function longToIP(n) {
            return [(n >>> 24) & 0xff, (n >>> 16) & 0xff, (n >>> 8) & 0xff, n & 0xff].join('.');
        }

        function ipToLong(ip) {
            return ip.split('.').reduce((acc, o) => (acc << 8) | parseInt(o), 0) >>> 0;
        }

        function bitsNeeded(count) {
            let bits = 1;
            while (Math.pow(2, bits) - 2 < count) bits++;
            return bits;
        }

        function getSuggestion(hostRequests) {
            let total = 0;
            for (const req of hostRequests) {
                total += Math.pow(2, bitsNeeded(req.count));
            }
            return 32 - Math.ceil(Math.log2(total));
        }

        function calculateSubnets(baseIP, basePrefix, hostRequests) {
            // Sort descending by count (VLSM: largest first)
            const sorted = [...hostRequests].sort((a, b) => b.count - a.count);

            let currentAddr = ipToLong(baseIP) >>> 0;
            const maxAddr   = (currentAddr + Math.pow(2, 32 - basePrefix) - 1) >>> 0;

            const results   = [];
            const steps     = [];
            const masksUsed = [];

            for (const req of sorted) {
                const needed    = req.count;
                const name      = req.name || 'Unnamed';
                const bits      = bitsNeeded(needed);
                const newPrefix = 32 - bits;
                const blockSize = Math.pow(2, bits);
                const networkAddr   = currentAddr >>> 0;
                const broadcastAddr = (currentAddr + blockSize - 1) >>> 0;
                masksUsed.push(newPrefix);

                if (broadcastAddr > maxAddr) {
                    return { status: 'error', suggestion: getSuggestion(hostRequests) };
                }

                // Build subnet mask from prefix
                const maskLong = newPrefix === 0 ? 0 : (~0 << (32 - newPrefix)) >>> 0;

                results.push({
                    name,
                    hosts_required: needed,
                    prefix:          '/' + newPrefix,
                    network_address: longToIP(networkAddr),
                    first_usable:    longToIP(networkAddr + 1),
                    last_usable:     longToIP(broadcastAddr - 1),
                    broadcast:       longToIP(broadcastAddr),
                    subnet_mask:     longToIP(maskLong),
                    total_hosts:     blockSize - 2
                });

                steps.push(
                    `<strong>${name}</strong>: Needed ${needed} hosts → block size ${blockSize} (/${newPrefix}). ` +
                    `Network: ${longToIP(networkAddr)}, Broadcast: ${longToIP(broadcastAddr)}`
                );

                currentAddr = (broadcastAddr + 1) >>> 0;
            }

            const unique = [...new Set(masksUsed)];
            const mode   = unique.length === 1 ? 'FLSM (Fixed Length)' : 'VLSM (Variable Length)';

            return { status: 'success', subnets: results, steps, mode };
        }

        // --- IPv4 Calculate ---
        function calculateIPv4() {
            const prefix = parseInt(document.getElementById('ipv4_prefix').value);

            hideError();

            if (isNaN(prefix) || prefix < 1 || prefix > 30) {
                showError({
                    title: 'Invalid Prefix',
                    summary: `The prefix "/${prefix}" is out of the allowed range.`,
                    body: [
                        `This tool supports prefixes from <strong>/1</strong> to <strong>/30</strong> only.`,
                        `A <strong>/31</strong> leaves only 2 addresses (no usable hosts after removing network and broadcast), and a <strong>/32</strong> is a single host address — neither is useful for subnetting.`,
                        `A <strong>/0</strong> would represent the entire Internet address space, which is not practical here.`,
                        `Please enter a prefix value between <strong>1</strong> and <strong>30</strong>.`
                    ]
                });
                return;
            }

            const hosts = Array.from(document.querySelectorAll('.host-row'))
                .map(row => ({
                    name:  row.querySelector('.h-name').value.trim() || 'Unnamed',
                    count: parseInt(row.querySelector('.h-count').value) || 0
                }))
                .filter(h => h.count > 0);

            if (hosts.length === 0) {
                showError({
                    title: 'No Host Requirements Entered',
                    summary: 'You need to add at least one network with a valid host count.',
                    body: [
                        `The subnet calculator needs to know <strong>how many hosts</strong> each of your networks requires before it can assign subnets.`,
                        `Click <strong>"+ Add Network"</strong> and fill in a host count (e.g. <em>50</em> for a network needing 50 devices).`,
                        `The calculator will then find the smallest subnet block that fits your requirement and allocate it using VLSM.`
                    ]
                });
                return;
            }

            const data      = calculateSubnets(document.getElementById('ipv4_addr').value, prefix, hosts);
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
                buildExplanation(data, prefix);
                resultsEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                resultsEl.classList.add('hidden');
                // Build detailed overflow explanation
                const usable = Math.pow(2, 32 - prefix) - 2;
                const totalRequested = hosts.reduce((sum, h) => {
                    let bits = 1;
                    while (Math.pow(2, bits) - 2 < h.count) bits++;
                    return sum + Math.pow(2, bits);
                }, 0);

                const hostLines = hosts.map(h => {
                    let bits = 1;
                    while (Math.pow(2, bits) - 2 < h.count) bits++;
                    const block = Math.pow(2, bits);
                    return `<li><strong>${h.name}</strong>: ${h.count} hosts → needs a /${32-bits} block = <strong>${block} addresses</strong></li>`;
                }).join('');

                showError({
                    title: 'Subnet Space Exceeded',
                    summary: `Your /${prefix} network only provides ${usable.toLocaleString()} usable host addresses, but your combined subnet requirements need ${(totalRequested - 2).toLocaleString()} addresses.`,
                    body: [
                        `<strong>How subnetting works:</strong> A <strong>/${prefix}</strong> network has <strong>2<sup>${32 - prefix}</sup> = ${Math.pow(2, 32 - prefix).toLocaleString()}</strong> total addresses.
                         After subtracting the <strong>Network ID</strong> (first address) and <strong>Broadcast</strong> (last address), you get
                         <strong>${usable.toLocaleString()} usable host addresses</strong>.`,

                        `<strong>Why your subnets don't fit:</strong> VLSM rounds each subnet up to the next power of 2 to create aligned blocks.
                         Here's what each of your networks requires:
                         <ul class="list-disc list-inside mt-1 ml-2 space-y-0.5 text-slate-400">${hostLines}</ul>
                         <span class="mt-1 block">Combined block space needed: <strong>${totalRequested.toLocaleString()} addresses</strong>, but your /${prefix} only has <strong>${Math.pow(2, 32-prefix).toLocaleString()}</strong> total.</span>`,

                        data.suggestion
                            ? `<strong>The fix:</strong> Use a wider prefix — a <strong>/${data.suggestion}</strong> gives you <strong>${(Math.pow(2, 32 - data.suggestion)).toLocaleString()}</strong> total addresses, which is enough to fit all your subnets.`
                            : `<strong>The fix:</strong> Reduce the number of hosts per network, or split your design across multiple parent networks.`
                    ],
                    suggestion: data.suggestion
                        ? `Change your prefix from /${prefix} to /${data.suggestion} (or smaller). A /${data.suggestion} provides ${Math.pow(2, 32 - data.suggestion).toLocaleString()} total addresses — enough to accommodate all your requested subnets.`
                        : null
                });
            }
        }

        // --- Explanation panel ---
        function toggleExplanation() {
            const body    = document.getElementById('expl-body');
            const chevron = document.getElementById('expl-chevron');
            const hidden  = body.classList.toggle('hidden');
            chevron.style.transform = hidden ? '' : 'rotate(180deg)';
        }

        function buildExplanation(data, basePrefix) {
            const COLORS = ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899', '#84cc16', '#f97316', '#6366f1'];
            const totalAddrs = Math.pow(2, 32 - basePrefix);
            const usedAddrs  = data.subnets.reduce((s, sub) => s + (sub.total_hosts + 2), 0);
            const freeAddrs  = totalAddrs - usedAddrs;

            // 1. Overview
            document.getElementById('expl-overview').innerHTML = `
                <p class="text-slate-300 font-semibold mb-2">Overview</p>
                <p class="text-slate-400">
                    A <strong class="text-white">/${basePrefix}</strong> network has
                    <strong class="text-white">2<sup>${32 - basePrefix}</sup> = ${totalAddrs.toLocaleString()}</strong> total addresses.
                    Networks are sorted largest to smallest, then each gets the smallest block that fits its host count.
                </p>
                <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                    <div class="bg-slate-900 rounded-lg p-2 border border-slate-700">
                        <p class="text-[10px] text-slate-500 uppercase mb-1">Total Addresses</p>
                        <p class="text-white font-bold font-mono">${totalAddrs.toLocaleString()}</p>
                    </div>
                    <div class="bg-slate-900 rounded-lg p-2 border border-slate-700">
                        <p class="text-[10px] text-slate-500 uppercase mb-1">Addresses Used</p>
                        <p class="text-green-400 font-bold font-mono">${usedAddrs.toLocaleString()}</p>
                    </div>
                    <div class="bg-slate-900 rounded-lg p-2 border border-slate-700">
                        <p class="text-[10px] text-slate-500 uppercase mb-1">Addresses Free</p>
                        <p class="text-yellow-400 font-bold font-mono">${freeAddrs.toLocaleString()}</p>
                    </div>
                </div>
            `;

            // 2. Per-subnet step cards
            document.getElementById('expl-steps').innerHTML = data.subnets.map((s, i) => {
                const bits      = 32 - parseInt(s.prefix.replace('/',''));
                const blockSize = s.total_hosts + 2;
                const color     = COLORS[i % COLORS.length];
                return `
                <div class="rounded-lg border border-slate-700 overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-2" style="background:${color}22; border-bottom:1px solid ${color}44">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold text-white shrink-0"
                              style="background:${color}">${i+1}</span>
                        <span class="font-bold text-white">${s.name}</span>
                        <span class="ml-auto font-mono text-xs" style="color:${color}">${s.prefix}</span>
                    </div>
                    <div class="px-4 py-3 bg-slate-950 text-xs text-slate-400 space-y-1.5">
                        <p><strong class="text-slate-200">Required:</strong> ${s.hosts_required} hosts</p>
                        <p><strong class="text-slate-200">Block chosen:</strong>
                           2<sup>${bits}</sup> − 2 = <strong class="text-white">${s.total_hosts} usable</strong>
                           (fits ${s.hosts_required})</p>
                        <p><strong class="text-slate-200">Size:</strong> ${blockSize} addresses &mdash; ${s.prefix}, mask ${s.subnet_mask}</p>
                        <div class="mt-2 grid grid-cols-3 gap-2 text-center">
                            <div class="bg-slate-900 rounded p-1.5 border border-slate-800">
                                <p class="text-[9px] text-slate-500 mb-0.5">Network ID</p>
                                <p class="font-mono text-slate-200">${s.network_address}</p>
                            </div>
                            <div class="bg-slate-900 rounded p-1.5 border border-slate-800">
                                <p class="text-[9px] text-slate-500 mb-0.5">Usable Range</p>
                                <p class="font-mono text-green-400 text-[10px]">${s.first_usable} – ${s.last_usable}</p>
                            </div>
                            <div class="bg-slate-900 rounded p-1.5 border border-slate-800">
                                <p class="text-[9px] text-slate-500 mb-0.5">Broadcast</p>
                                <p class="font-mono text-red-400">${s.broadcast}</p>
                            </div>
                        </div>
                        <p class="text-slate-500 pt-1">Next subnet starts at <strong class="text-slate-300 font-mono">${nextAddr(s.broadcast)}</strong></p>
                    </div>
                </div>`;
            }).join('');

            // 3. Address space bar
            const barEl    = document.getElementById('expl-bar');
            const legendEl = document.getElementById('expl-legend');
            let barHTML = '', legendHTML = '';
            data.subnets.forEach((s, i) => {
                const blockSize = s.total_hosts + 2;
                const pct       = (blockSize / totalAddrs * 100).toFixed(2);
                const color     = COLORS[i % COLORS.length];
                barHTML   += `<div style="width:${pct}%;background:${color}" title="${s.name}: ${blockSize} addresses (${pct}%)"></div>`;
                legendHTML += `<span class="flex items-center gap-1 text-[10px] text-slate-400">
                    <span class="w-2.5 h-2.5 rounded-sm inline-block shrink-0" style="background:${color}"></span>${s.name} (${pct}%)</span>`;
            });
            if (freeAddrs > 0) {
                const pct = (freeAddrs / totalAddrs * 100).toFixed(2);
                barHTML   += `<div style="width:${pct}%;background:#1e293b" title="Free: ${freeAddrs} addresses"></div>`;
                legendHTML += `<span class="flex items-center gap-1 text-[10px] text-slate-500">
                    <span class="w-2.5 h-2.5 rounded-sm inline-block bg-slate-700 shrink-0"></span>Free (${pct}%)</span>`;
            }
            barEl.innerHTML    = barHTML;
            legendEl.innerHTML = legendHTML;

            // 4. Mode card
            const isVLSM = data.mode.includes('Variable');
            document.getElementById('expl-mode-text').innerHTML = isVLSM
                ? `<strong class="text-white">VLSM</strong> — each subnet has a different block size matched to its host count, so fewer addresses are wasted.`
                : `<strong class="text-white">FLSM</strong> — all subnets use the same block size because they all need the same number of hosts.`;

            // Open the panel automatically
            const body = document.getElementById('expl-body');
            if (body.classList.contains('hidden')) toggleExplanation();
        }

        function nextAddr(broadcastIP) {
            const parts = broadcastIP.split('.').map(Number);
            for (let i = 3; i >= 0; i--) {
                if (parts[i] < 255) { parts[i]++; break; }
                parts[i] = 0;
            }
            return parts.join('.');
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
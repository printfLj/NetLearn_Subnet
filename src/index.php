<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NetLearn | IP & Subnetting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        input[readonly] { background-color: #1e293b; color: #94a3b8; cursor: not-allowed; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 min-h-screen p-4 md:p-10">
    <div class="max-w-5xl mx-auto">

        <!-- Toggle Header -->
        <div class="flex justify-center mb-8">
            <div class="inline-flex rounded-md shadow-sm bg-slate-800 p-1">
                <button onclick="switchMode('ipv4')" id="btn-ipv4" class="px-6 py-2 rounded-md bg-blue-600 text-white font-bold transition">IPv4 Subnetting</button>
                <button onclick="switchMode('ipv6')" id="btn-ipv6" class="px-6 py-2 rounded-md text-slate-400 hover:text-white transition">IPv6 Explorer</button>
            </div>
        </div>

        <!-- IPv4 SECTION -->
        <div id="section-ipv4" class="space-y-6">
            <div class="bg-slate-900 p-6 rounded-xl border border-slate-800 shadow-2xl">
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Network Address (Auto-generated)</label>
                        <div class="flex gap-2">
                            <input type="text" id="ipv4_addr" readonly
                                class="w-full p-3 rounded bg-slate-800 border border-slate-700 font-mono">
                            <button onclick="randomizeIP()" title="Generate new address"
                                class="bg-blue-600 hover:bg-blue-500 px-4 rounded transition">🎲</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Prefix (1–30)</label>
                        <input type="number" id="ipv4_prefix" value="24" min="1" max="30" step="1"
                            onkeydown="if(!/[0-9]|Backspace|ArrowLeft|ArrowRight|ArrowUp|ArrowDown|Tab|Delete/.test(event.key)) event.preventDefault()"
                            onblur="sanitizePrefix(this); randomizeIP()"
                            class="w-full p-3 rounded bg-slate-800 border border-slate-700 font-mono focus:border-blue-500 outline-none">
                        <p id="prefix-error" class="text-xs text-red-400 mt-1 hidden"></p>
                    </div>
                </div>

                <!-- Dynamic Host Requirements -->
                <div class="mb-6">
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-3">Host Requirements</label>
                    <div id="host-rows" class="space-y-2 mb-4">
                        <div class="flex gap-2 host-row">
                            <input type="text" placeholder="Network Name (e.g. Sales)"
                                class="h-name w-1/2 p-2 rounded bg-slate-800 border border-slate-700">
                            <input type="number" placeholder="Hosts" min="1" step="1"
                                onkeydown="if(!/[0-9]|Backspace|ArrowLeft|ArrowRight|ArrowUp|ArrowDown|Tab|Delete/.test(event.key)) event.preventDefault()"
                                class="h-count w-1/4 p-2 rounded bg-slate-800 border border-slate-700">
                            <button onclick="this.parentElement.remove()" class="text-red-500 px-2">✕</button>
                        </div>
                    </div>
                    <button onclick="addHostRow()" class="text-sm text-blue-400 hover:text-blue-300 font-semibold">+ Add Network</button>
                </div>

                <button onclick="calculateIPv4()"
                    class="w-full bg-blue-600 hover:bg-blue-500 p-4 rounded-lg font-bold text-lg shadow-lg transition">
                    Generate Subnet Table
                </button>
            </div>

            <!-- IPv4 Results -->
            <div id="ipv4-results" class="hidden space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold">Subnet Table</h2>
                    <span id="lsm-indicator" class="px-3 py-1 bg-blue-900/50 text-blue-300 rounded-full text-xs font-bold border border-blue-700"></span>
                </div>
                <div class="overflow-x-auto rounded-lg border border-slate-800">
                    <table class="w-full text-left bg-slate-900">
                        <thead class="bg-slate-800 text-slate-400 text-xs uppercase">
                            <tr>
                                <th class="p-4">Name</th>
                                <th class="p-4">Hosts Req.</th>
                                <th class="p-4">Network ID</th>
                                <th class="p-4">Usable Range</th>
                                <th class="p-4">Broadcast</th>
                                <th class="p-4">Mask</th>
                                <th class="p-4">Total Hosts</th>
                            </tr>
                        </thead>
                        <tbody id="ipv4-tbody"></tbody>
                    </table>
                </div>
                <div class="bg-slate-900 p-6 rounded-xl border border-slate-800">
                    <h3 class="text-xs font-bold uppercase text-slate-500 mb-4 tracking-widest">Calculation Steps</h3>
                    <ul id="steps-list" class="space-y-2 text-sm text-slate-400 list-disc list-inside"></ul>
                </div>
            </div>
        </div>

        <!-- IPv6 SECTION -->
        <div id="section-ipv6" class="hidden space-y-6">
            <div class="bg-slate-900 p-8 rounded-2xl border border-slate-800 shadow-2xl">
                <h3 class="text-center text-slate-500 text-sm mb-6 font-mono">128-bit IPv6 address structure</h3>

                <div class="grid grid-cols-12 gap-2 mb-8 text-center font-mono">
                    <div class="col-span-5 bg-blue-900/40 p-4 rounded border border-blue-700">
                        <p class="text-[10px] text-blue-400 mb-1">/48 Network Prefix</p>
                        <p class="text-lg">2001:0db8:aaaa</p>
                    </div>
                    <div class="col-span-2 bg-yellow-900/40 p-4 rounded border border-yellow-700">
                        <p class="text-[10px] text-yellow-400 mb-1">SLA ID</p>
                        <p id="ipv6-sla-display" class="text-lg text-yellow-300 font-bold">0000</p>
                    </div>
                    <div class="col-span-5 bg-slate-800 p-4 rounded border border-slate-700">
                        <p class="text-[10px] text-slate-500 mb-1">/64 Interface ID</p>
                        <p class="text-lg text-slate-500">0000:0000:0000:0000</p>
                    </div>
                </div>

                <div class="bg-slate-950 p-6 rounded-xl text-center border border-slate-800 mb-8">
                    <p class="text-xs text-slate-500 mb-2 uppercase">Current /64 Subnet</p>
                    <p id="ipv6-full-result" class="text-2xl font-bold text-white tracking-wider">2001:0db8:aaaa:0000::/64</p>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between text-xs font-bold text-slate-500 uppercase">
                        <span>Explore SLA ID (0 – 65535)</span>
                        <span id="sla-counter">Subnet 0 of 65,535</span>
                    </div>
                    <input type="range" id="sla-slider" min="0" max="65535" value="0"
                        class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-blue-600"
                        oninput="updateIPv6(this.value)">
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- Navigation ---
        function switchMode(mode) {
            document.getElementById('section-ipv4').classList.toggle('hidden', mode === 'ipv6');
            document.getElementById('section-ipv6').classList.toggle('hidden', mode === 'ipv4');
            document.getElementById('btn-ipv4').className = mode === 'ipv4'
                ? 'px-6 py-2 rounded-md bg-blue-600 text-white font-bold'
                : 'px-6 py-2 rounded-md text-slate-400 hover:text-white transition';
            document.getElementById('btn-ipv6').className = mode === 'ipv6'
                ? 'px-6 py-2 rounded-md bg-blue-600 text-white font-bold'
                : 'px-6 py-2 rounded-md text-slate-400 hover:text-white transition';
        }

        // --- Prefix sanitization (fires on blur only, so typing isn't interrupted) ---
        function sanitizePrefix(el) {
            let val = parseInt(el.value) || 24;
            if (val < 1)  val = 1;
            if (val > 30) val = 30;
            el.value = val;
        }

        // --- Network Address (auto-generated from prefix, read-only) ---
        async function randomizeIP() {
            const prefix = parseInt(document.getElementById('ipv4_prefix').value) || 24;
            const res  = await fetch(`api.php?action=generate&prefix=${prefix}`);
            const data = await res.json();
            document.getElementById('ipv4_addr').value = data.ip;
        }

        // --- Host rows ---
        function addHostRow() {
            const div = document.createElement('div');
            div.className = 'flex gap-2 host-row';
            div.innerHTML = `
                <input type="text" placeholder="Network Name"
                    class="h-name w-1/2 p-2 rounded bg-slate-800 border border-slate-700">
                <input type="number" placeholder="Hosts" min="1" step="1"
                    onkeydown="if(!/[0-9]|Backspace|ArrowLeft|ArrowRight|ArrowUp|ArrowDown|Tab|Delete/.test(event.key)) event.preventDefault()"
                    class="h-count w-1/4 p-2 rounded bg-slate-800 border border-slate-700">
                <button onclick="this.parentElement.remove()" class="text-red-500 px-2">✕</button>`;
            document.getElementById('host-rows').appendChild(div);
        }

        // --- IPv4 Calculate ---
        async function calculateIPv4() {
            const prefixEl  = document.getElementById('ipv4_prefix');
            const errEl     = document.getElementById('prefix-error');
            const prefix    = parseInt(prefixEl.value);
            errEl.classList.add('hidden');

            if (isNaN(prefix) || prefix < 1 || prefix > 30) {
                errEl.innerText = 'Prefix must be between 1 and 30.';
                errEl.classList.remove('hidden');
                return;
            }

            // Collect host rows, filter out empty/zero counts
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

            const data = await res.json();
            const resultsEl = document.getElementById('ipv4-results');

            if (data.status === 'success') {
                resultsEl.classList.remove('hidden');
                document.getElementById('lsm-indicator').innerText = data.mode;
                document.getElementById('ipv4-tbody').innerHTML = data.subnets.map(s => `
                    <tr class="border-t border-slate-800 hover:bg-slate-800/40">
                        <td class="p-4 font-bold text-blue-400">${s.name} <span class="text-[10px] text-slate-500">${s.prefix}</span></td>
                        <td class="p-4 text-center text-yellow-300">${s.hosts_required}</td>
                        <td class="p-4 font-mono">${s.network_address}</td>
                        <td class="p-4 font-mono text-sm">${s.first_usable} – ${s.last_usable}</td>
                        <td class="p-4 font-mono text-red-400">${s.broadcast}</td>
                        <td class="p-4 text-xs text-slate-500 font-mono">${s.subnet_mask}</td>
                        <td class="p-4 text-center text-green-400">${s.total_hosts}</td>
                    </tr>
                `).join('');
                document.getElementById('steps-list').innerHTML = data.steps
                    .map(s => `<li>${s}</li>`).join('');
            } else {
                errEl.innerText = data.suggestion
                    ? `Hindi Kasya! Based on your requirements, use a /${data.suggestion} prefix.`
                    : data.message;
                errEl.classList.remove('hidden');
                resultsEl.classList.add('hidden');
            }
        }

        // --- IPv6 Explorer ---
        function updateIPv6(val) {
            const hex = parseInt(val).toString(16).padStart(4, '0').toUpperCase();
            document.getElementById('ipv6-sla-display').innerText = hex;
            document.getElementById('ipv6-full-result').innerText = `2001:0db8:aaaa:${hex}::/64`;
            document.getElementById('sla-counter').innerText = `Subnet ${val} of 65,535`;
        }

        // On load: generate initial network address
        window.onload = randomizeIP;
    </script>
</body>
</html>
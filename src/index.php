<!DOCTYPE html>
<html lang="en">
<head>
    <title>NetLearn | Subnetting Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-white p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6 text-blue-400">IP & Subnetting Lab</h1>

        <div class="bg-slate-800 p-6 rounded-lg shadow-xl mb-6">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm mb-1">Network Address
                        <span class="text-slate-500 text-xs ml-1">(auto-generated)</span>
                    </label>
                    <div class="flex gap-2">
                        <input type="text" id="base_ip" readonly
                            class="bg-slate-600 text-slate-300 p-2 rounded w-full border border-slate-500 cursor-not-allowed select-all font-mono">
                        <button onclick="regenerateIP()" title="Generate new address"
                            class="bg-slate-600 hover:bg-slate-500 px-3 rounded transition">🎲</button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm mb-1">Prefix Length (0–30)</label>
                    <input type="number" id="prefix" value="24" min="0" max="30" step="1"
                        onkeydown="if(!/[0-9]|Backspace|ArrowLeft|ArrowRight|ArrowUp|ArrowDown|Tab|Delete/.test(event.key)) event.preventDefault()"
                        onblur="sanitizePrefix(this); regenerateIP()"
                        class="bg-slate-700 p-2 rounded w-full border border-slate-600">
                </div>
            </div>

            <label class="block text-sm mb-1">Host Requirements (comma separated: e.g. 50, 20, 10)</label>
            <input type="text" id="hosts" placeholder="50, 20, 10" inputmode="numeric"
                oninput="this.value=this.value.replace(/[^0-9,]/g,'')"
                class="bg-slate-700 p-2 rounded w-full border border-slate-600 mb-4">

            <button onclick="calculate()"
                class="w-full bg-blue-600 hover:bg-blue-500 p-3 rounded font-bold transition">
                Generate Subnetting Table
            </button>
        </div>

        <div id="alert-box" class="hidden p-4 rounded mb-6 border"></div>

        <div id="results-area" class="hidden overflow-x-auto">
            <table class="w-full text-left bg-slate-800 rounded-lg overflow-hidden">
                <thead class="bg-slate-700 text-blue-300">
                    <tr>
                        <th class="p-3">Subnet</th>
                        <th class="p-3">Hosts Req.</th>
                        <th class="p-3">Network ID</th>
                        <th class="p-3">Usable Range</th>
                        <th class="p-3">Broadcast</th>
                        <th class="p-3">Mask</th>
                        <th class="p-3">Total Hosts</th>
                    </tr>
                </thead>
                <tbody id="table-body"></tbody>
            </table>
        </div>
    </div>

    <script>
        function sanitizePrefix(el) {
            // Strip non-digits, clamp to 1–30
            let val = el.value.replace(/[^0-9]/g, '');
            val = (val === '') ? 0 : parseInt(val);
            if (val < 0)  val = 0;
            if (val > 30) val = 30;
            el.value = val;
        }

        async function regenerateIP() {
            const prefix = parseInt(document.getElementById('prefix').value) || 24;
            const res  = await fetch(`api.php?action=generate_for_prefix&prefix=${prefix}`);
            const data = await res.json();
            document.getElementById('base_ip').value = data.ip;
        }

        async function calculate() {
            const hosts = document.getElementById('hosts').value
                .split(',')
                .map(n => parseInt(n.trim()))
                .filter(n => !isNaN(n) && n > 0);

            const alertBox   = document.getElementById('alert-box');
            const resultsArea = document.getElementById('results-area');

            if (hosts.length === 0) {
                alertBox.className = "p-4 rounded mb-6 border border-yellow-500 bg-yellow-900/30 text-yellow-200";
                alertBox.innerHTML = `<strong>Walang input!</strong> Please enter at least one valid host requirement.`;
                alertBox.classList.remove('hidden');
                resultsArea.classList.add('hidden');
                return;
            }

            const payload = {
                base_ip: document.getElementById('base_ip').value,
                prefix:  parseInt(document.getElementById('prefix').value),
                hosts:   hosts
            };

            const res  = await fetch('api.php?action=calculate', {
                method:  'POST',
                headers: {'Content-Type': 'application/json'},
                body:    JSON.stringify(payload)
            });

            const data = await res.json();

            if (data.status === 'error') {
                alertBox.className = "p-4 rounded mb-6 border border-red-500 bg-red-900/30 text-red-200";
                alertBox.innerHTML = data.suggestion
                    ? `<strong>Hindi Kasya!</strong> Your requirements need a /${data.suggestion} or larger.`
                    : `<strong>Error:</strong> ${data.message}`;
                alertBox.classList.remove('hidden');
                resultsArea.classList.add('hidden');
            } else {
                alertBox.classList.add('hidden');
                resultsArea.classList.remove('hidden');
                document.getElementById('table-body').innerHTML = data.subnets.map(s => `
                    <tr class="border-t border-slate-700 hover:bg-slate-750">
                        <td class="p-3">${s.name} <span class="text-xs text-slate-400">${s.prefix}</span></td>
                        <td class="p-3 text-center text-yellow-300">${s.hosts_required}</td>
                        <td class="p-3 font-mono text-blue-300">${s.network_address}</td>
                        <td class="p-3 font-mono text-sm">${s.first_usable} – ${s.last_usable}</td>
                        <td class="p-3 font-mono text-red-400">${s.broadcast}</td>
                        <td class="p-3 font-mono text-xs text-slate-400">${s.subnet_mask}</td>
                        <td class="p-3 text-center text-green-400">${s.total_hosts}</td>
                    </tr>
                `).join('');
            }
        }

        // On page load: generate the initial network address for prefix 24
        regenerateIP();
    </script>
</body>
</html>
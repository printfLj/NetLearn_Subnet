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
                    <label class="block text-sm mb-1">Network Address</label>
                    <div class="flex gap-2">
                        <input type="text" id="base_ip" value="192.168.1.0" class="bg-slate-700 p-2 rounded w-full border border-slate-600">
                        <button onclick="randomizeIP()" class="bg-slate-600 px-3 rounded">🎲</button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm mb-1">Prefix (e.g. 24)</label>
                    <input type="number" id="prefix" value="24" class="bg-slate-700 p-2 rounded w-full border border-slate-600">
                </div>
            </div>

            <label class="block text-sm mb-1">Host Requirements (comma separated: e.g. 50, 20, 10)</label>
            <input type="text" id="hosts" placeholder="50, 20, 10" class="bg-slate-700 p-2 rounded w-full border border-slate-600 mb-4">
            
            <button onclick="calculate()" class="w-full bg-blue-600 hover:bg-blue-500 p-3 rounded font-bold transition">Generate Subnetting Table</button>
        </div>

        <div id="alert-box" class="hidden p-4 rounded mb-6 border"></div>

        <div id="results-area" class="hidden overflow-x-auto">
            <table class="w-full text-left bg-slate-800 rounded-lg overflow-hidden">
                <thead class="bg-slate-700 text-blue-300">
                    <tr>
                        <th class="p-3">Subnet</th>
                        <th class="p-3">Network ID</th>
                        <th class="p-3">Usable Range</th>
                        <th class="p-3">Mask</th>
                    </tr>
                </thead>
                <tbody id="table-body"></tbody>
            </table>
        </div>
    </div>

    <script>
        async function randomizeIP() {
            const res = await fetch('api.php?action=generate&class=C');
            const data = await res.json();
            document.getElementById('base_ip').value = data.ip;
        }

        async function calculate() {
            const payload = {
                base_ip: document.getElementById('base_ip').value,
                prefix: document.getElementById('prefix').value,
                hosts: document.getElementById('hosts').value.split(',').map(n => parseInt(n.trim()))
            };

            const res = await fetch('api.php?action=calculate', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            
            const data = await res.json();
            const alertBox = document.getElementById('alert-box');
            const resultsArea = document.getElementById('results-area');

            if (data.status === 'error') {
                alertBox.className = "p-4 rounded mb-6 border border-red-500 bg-red-900/30 text-red-200";
                alertBox.innerHTML = `<strong>Hindi Kasya!</strong> Your requirements need a /${data.suggestion} or larger.`;
                alertBox.classList.remove('hidden');
                resultsArea.classList.add('hidden');
            } else {
                alertBox.classList.add('hidden');
                resultsArea.classList.remove('hidden');
                const tbody = document.getElementById('table-body');
                tbody.innerHTML = data.subnets.map(s => `
                    <tr class="border-t border-slate-700 hover:bg-slate-750">
                        <td class="p-3">${s.name} <span class="text-xs text-slate-400">${s.prefix}</span></td>
                        <td class="p-3 font-mono text-blue-300">${s.network_address}</td>
                        <td class="p-3 font-mono text-sm">${s.first_usable} - ${s.last_usable}</td>
                        <td class="p-3 font-mono text-xs text-slate-400">${s.subnet_mask}</td>
                    </tr>
                `).join('');
            }
        }
    </script>
</body>
</html>
<?php 
session_start(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Speed MikroTik Connector System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; color: #1e293b; font-family: 'Inter', sans-serif; overflow-x: hidden; }
        .glass-light { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(0, 0, 0, 0.05); }
        .status-dot { height: 10px; width: 10px; border-radius: 50%; display: inline-block; transition: all 0.3s ease; }
        /* Smooth transition for the management area */
        #mgmt-area { transition: opacity 0.5s ease, transform 0.5s ease; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="p-2 md:p-6 lg:p-10">
    <div class="max-w-7xl mx-auto">
        
        <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="mb-6 p-4 bg-green-500 text-white rounded-2xl text-xs font-bold uppercase tracking-widest text-center shadow-lg animate-pulse">
                ✅ User Provisioned Successfully
            </div>
        <?php endif; ?>

        <header class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-black text-slate-800 uppercase tracking-tighter">
                    Speed <span class="text-cyan-600">MikroTik</span> Connector
                </h1>
                <p id="active-router" class="text-[10px] md:text-xs text-slate-400 font-mono uppercase tracking-widest">NO ACTIVE SESSION</p>
            </div>
            <div id="connection-badge" class="px-4 py-2 rounded-full text-[10px] font-bold border border-slate-200 bg-white shadow-sm transition-all uppercase flex items-center">
                <span id="badge-dot" class="status-dot bg-slate-300 mr-2"></span> 
                <span id="badge-text">Offline</span>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8">
            
            <!-- Sidebar: Auth -->
            <div class="lg:col-span-4 xl:col-span-3 space-y-6">
                <div class="glass-light p-5 md:p-6 rounded-3xl shadow-xl border-t-4 border-slate-800">
                    <h3 class="text-xs font-bold text-slate-800 mb-6 uppercase tracking-widest flex items-center">
                        <span class="mr-2">🔌</span> Authentication
                    </h3>
                    <form id="connection-form" class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">IP Address</label>
                            <input type="text" name="ip" value="192.168.56.10" required
                                class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-cyan-500 outline-none transition font-mono text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">User</label>
                                <input type="text" name="user" value="admin" required
                                    class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-cyan-500 outline-none transition text-sm">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Port</label>
                                <input type="number" name="port" value="8728" required
                                    class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-cyan-500 outline-none transition text-sm">
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Password</label>
                            <input type="password" name="pass" placeholder="••••••••"
                                class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-cyan-500 outline-none transition text-sm">
                        </div>
                        
                        <div class="flex gap-2 pt-2">
                            <button type="button" onclick="handleConnect()" id="btn-connect"
                                class="flex-1 bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-xl transition-all shadow-lg active:scale-95 text-xs uppercase tracking-widest">
                                Connect
                            </button>
                            <button type="button" onclick="handleDisconnect()" id="btn-disconnect"
                                class="hidden flex-1 bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 font-bold py-3 rounded-xl transition-all text-xs uppercase tracking-widest">
                                Terminate
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main Panel -->
            <div id="mgmt-area" class="lg:col-span-8 xl:col-span-9 opacity-40 pointer-events-none transition-all duration-500">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Add User Form -->
                    <div class="glass-light p-6 md:p-8 rounded-3xl shadow-lg border-b-4 border-cyan-500">
                        <div class="mb-6">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Generate Access</h3>
                            <p class="text-[10px] text-slate-400 font-medium uppercase">Provision New Hotspot Account</p>
                        </div>
                        <form action="actions/add_user.php" method="POST" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <input type="text" name="username" placeholder="Username / Voucher" required 
                                    class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl outline-none focus:border-cyan-400 transition text-sm">
                                <input type="password" name="password" placeholder="Password" required 
                                    class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl outline-none focus:border-cyan-400 transition text-sm">
                            </div>
                            <select name="profile" class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl outline-none focus:border-cyan-400 transition text-sm appearance-none cursor-pointer">
                                <option value="default">Standard Profile</option>
                                <option value="Trial">Free Trial (1 Hour)</option>
                                <option value="Unlimited">VIP Unlimited</option>
                            </select>
                            <button type="submit" class="w-full bg-cyan-600 text-white font-black py-4 rounded-xl hover:bg-cyan-700 transition-all shadow-md uppercase tracking-widest text-[10px]">
                                Deploy to Router
                            </button>
                        </form>
                    </div>

                    <!-- Live Stats -->
                    <div class="glass-light p-6 md:p-8 rounded-3xl shadow-lg">
                        <h3 class="text-sm font-bold text-slate-800 mb-6 uppercase tracking-widest">Router Performance</h3>
                        <div class="space-y-6">
                            <div class="flex justify-between items-end border-b border-slate-100 pb-3">
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase block">CPU Load</span>
                                    <span id="cpu" class="font-mono font-bold text-xl text-slate-700 leading-none">0%</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Uptime</span>
                                    <span id="uptime" class="text-sm font-mono font-bold text-cyan-600 leading-none">--:--:--</span>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-4 rounded-2xl flex justify-between items-center">
                                <span class="text-[10px] font-black text-slate-500 uppercase">Live Connections</span>
                                <span id="users" class="font-mono font-black text-2xl text-slate-800">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Table -->
                <div class="glass-light p-4 md:p-8 rounded-3xl shadow-lg mt-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-2">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Active Hotspot Users</h3>
                        <div class="px-3 py-1 bg-cyan-100 text-cyan-700 rounded-lg text-[10px] font-black font-mono">
                            COUNT: <span id="user-count-list">0</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left text-xs font-mono min-w-[500px]">
                            <thead>
                                <tr class="text-slate-400 border-b border-slate-100 uppercase tracking-tighter">
                                    <th class="pb-3 px-2 font-black">User Identity</th>
                                    <th class="pb-3 text-center font-black">Connected Time</th>
                                    <th class="pb-3 text-right font-black">Local IP</th>
                                </tr>
                            </thead>
                            <tbody id="user-list-table" class="divide-y divide-slate-50">
                                <!-- Data injected via JS -->
                                <tr><td colspan="3" class="py-10 text-center text-slate-400">Waiting for connection...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let pollInterval;

        function handleConnect() {
            const form = document.getElementById('connection-form');
            const formData = new FormData(form);
            const btn = document.getElementById('btn-connect');
            
            btn.innerText = "Processing...";
            btn.classList.add('animate-pulse');
            btn.disabled = true;

            fetch('actions/test_link.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    setUIState(true, formData.get('ip'));
                    startPolling();
                } else {
                    alert("❌ Connection Failed: " + data.error);
                    resetBtn(btn);
                }
            })
            .catch(err => {
                alert("❌ Critical Error: Path actions/test_link.php not found.");
                resetBtn(btn);
            });
        }

        function resetBtn(btn) {
            btn.innerText = "Connect";
            btn.classList.remove('animate-pulse');
            btn.disabled = false;
        }

        function setUIState(connected, ip = '') {
            const badge = document.getElementById('connection-badge');
            const dot = document.getElementById('badge-dot');
            const bText = document.getElementById('badge-text');
            const mgmt = document.getElementById('mgmt-area');
            const btnConn = document.getElementById('btn-connect');
            const btnDisc = document.getElementById('btn-disconnect');
            const routerLabel = document.getElementById('active-router');

            if(connected) {
                dot.className = "status-dot bg-green-500 animate-pulse mr-2";
                badge.className = "px-4 py-2 rounded-full text-[10px] font-bold border border-green-200 bg-green-50 text-green-700 shadow-sm transition-all uppercase flex items-center";
                bText.innerText = "Connected";
                mgmt.style.opacity = "1";
                mgmt.style.pointerEvents = "auto";
                btnConn.classList.add('hidden');
                btnDisc.classList.remove('hidden');
                routerLabel.innerText = "LINK ACTIVE: " + ip;
            } else {
                window.location.href = 'actions/logout.php';
            }
        }

        function startPolling() {
            updateStats();
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(updateStats, 5000); 
        }

        function updateStats() {
            fetch('actions/fetch_stats.php')
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'connected') {
                        document.getElementById('cpu').innerText = data.cpu + "%";
                        document.getElementById('users').innerText = data.users;
                        document.getElementById('uptime').innerText = data.uptime;
                        document.getElementById('user-count-list').innerText = data.users;

                        const table = document.getElementById('user-list-table');
                        table.innerHTML = "";
                        
                        if(data.userList && data.userList.length > 0) {
                            data.userList.forEach(u => {
                                table.innerHTML += `
                                    <tr class="hover:bg-slate-50 transition-colors group">
                                        <td class="py-4 px-2">
                                            <span class="font-bold text-slate-700 block">${u.user || 'Unknown'}</span>
                                            <span class="text-[9px] text-slate-400 block">HOTSPOT CLIENT</span>
                                        </td>
                                        <td class="py-4 text-slate-500 text-center">${u.uptime || '--'}</td>
                                        <td class="py-4 text-cyan-600 text-right font-bold">${u.address || 'N/A'}</td>
                                    </tr>`;
                            });
                        } else {
                            table.innerHTML = `<tr><td colspan="3" class="py-10 text-center text-slate-400">No active sessions found</td></tr>`;
                        }
                    }
                })
                .catch(err => console.error("Stats fetch failure."));
        }

        function handleDisconnect() {
            if(confirm("Are you sure you want to terminate this router session?")) { setUIState(false); }
        }

        window.onload = () => {
            <?php if(isset($_SESSION['router_ip'])): ?>
                setUIState(true, '<?php echo $_SESSION['router_ip']; ?>');
                startPolling();
            <?php endif; ?>
        };
    </script>
</body>
</html>
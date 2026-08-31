<?php
// =====================================================
// 89 CLUB — Secure Live Admin Dashboard (PHP Wrapper)
// =====================================================
// Dynamically reads Firebase URL and Secret from environment
// variables (Railway) and protects the console with a secure login gate.
// =====================================================

include "firebase.php";

$firebaseUrl = getenv('FIREBASE_URL');
if (empty($firebaseUrl)) {
    $firebaseUrl = isset($_ENV['FIREBASE_URL']) ? $_ENV['FIREBASE_URL'] : '';
}
if (empty($firebaseUrl) && defined('FIREBASE_URL')) {
    $firebaseUrl = FIREBASE_URL;
}

$firebaseSecret = getenv('FIREBASE_SECRET');
if (empty($firebaseSecret)) {
    $firebaseSecret = isset($_ENV['FIREBASE_SECRET']) ? $_ENV['FIREBASE_SECRET'] : '';
}
if (empty($firebaseSecret) && defined('FIREBASE_SECRET')) {
    $firebaseSecret = FIREBASE_SECRET;
}

// Standardize URL trailing slash
if (!empty($firebaseUrl)) {
    $firebaseUrl = rtrim($firebaseUrl, '/') . '/';
}

session_start();

$error = '';
if (isset($_POST['login'])) {
    $password = $_POST['password'];
    
    // Fetch password from Firebase system_settings/admin_password
    $ch = curl_init();
    $url = $firebaseUrl . 'system_settings/admin_password.json';
    if (!empty($firebaseSecret)) {
        $url .= '?auth=' . $firebaseSecret;
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $res = curl_exec($ch);
    curl_close($ch);
    
    $adminPassword = json_decode($res, true);
    if (empty($adminPassword)) {
        $adminPassword = "admin89club"; // Default fallback password if not set in Firebase
    }
    
    if ($password === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = 'Invalid admin password! Please try again.';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_panel.php");
    exit();
}

$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>89 Club — Firebase Admin Dashboard</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- FontAwesome Icons CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Firebase JS SDK (v8) CDN -->
  <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
  <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>
  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background-color: #060913;
      color: #e2e8f0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-image: radial-gradient(circle at top right, rgba(223, 173, 58, 0.05), transparent 400px),
                        radial-gradient(circle at bottom left, rgba(30, 41, 59, 0.3), transparent 600px);
    }
    .custom-scrollbar::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
      background: #0a0e1a;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: #dfad3a;
      border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
      background: #c5962e;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col custom-scrollbar">

  <?php if (!$isLoggedIn): ?>
  <!-- Secure Login Gate -->
  <div class="fixed inset-0 bg-slate-950 flex items-center justify-center z-50 p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
      <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-500 text-slate-950 rounded-2xl font-black text-3xl mb-3 shadow-lg shadow-yellow-500/10">
          89
        </div>
        <h2 class="text-2xl font-bold text-white">Admin Console Login</h2>
        <p class="text-slate-400 mt-1 text-sm">Enter password to manage platform database.</p>
      </div>

      <?php if (!empty($error)): ?>
      <div class="mb-4 bg-red-500 bg-opacity-10 border border-red-500/20 text-red-400 text-xs px-4 py-3 rounded-xl flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation text-sm"></i>
        <span><?php echo htmlspecialchars($error); ?></span>
      </div>
      <?php endif; ?>

      <form method="POST" action="admin_panel.php" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Password</label>
          <input type="password" name="password" required placeholder="••••••••••••" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:outline-none focus:border-yellow-500 transition-colors">
        </div>
        
        <button type="submit" name="login" class="w-full bg-yellow-500 hover:bg-yellow-600 text-slate-950 font-bold py-3 rounded-xl transition-colors shadow-lg shadow-yellow-500/10">
          Unlock Dashboard
        </button>
      </form>
    </div>
  </div>
  <?php else: ?>

  <!-- Header -->
  <header class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex items-center justify-between sticky top-0 z-40">
    <div class="flex items-center gap-3">
      <div class="bg-yellow-500 text-slate-950 w-10 h-10 rounded-xl flex items-center justify-center font-black text-xl shadow-lg shadow-yellow-500/10">
        89
      </div>
      <div>
        <h1 class="text-lg font-bold text-white flex items-center gap-2">
          89 Club Admin Console
          <span id="connectionBadge" class="text-[10px] px-2 py-0.5 rounded-full bg-red-500 text-white font-semibold flex items-center gap-1 uppercase tracking-wider">
            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Offline
          </span>
        </h1>
        <p class="text-xs text-slate-500 select-all hidden" id="connectedDbLabel">Loading Database Configuration...</p>
      </div>
    </div>
    
    <div class="flex items-center gap-4">
      <a href="admin_panel.php?logout=1" class="text-red-400 hover:text-red-300 transition-colors text-sm font-semibold flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-800">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
      </a>
    </div>
  </header>

  <!-- Main Content Layout -->
  <div class="flex-1 flex overflow-hidden">
    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-[#070a13] border-r border-[#151c2e] flex flex-col p-4 shrink-0">
      <div class="space-y-1 flex-1">
        <button onclick="switchTab('tab-overview')" id="btn-tab-overview" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold text-amber-500 bg-amber-500 bg-opacity-5 border border-amber-500 border-opacity-10 transition-all">
          <span class="flex items-center gap-3">
            <i class="fa-solid fa-chart-line text-lg"></i> Overview
          </span>
          <i class="fa-solid fa-angle-right text-xs"></i>
        </button>

        <button onclick="switchTab('tab-deposits')" id="btn-tab-deposits" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 transition-all border border-transparent">
          <span class="flex items-center gap-3">
            <i class="fa-solid fa-wallet text-lg"></i> Deposit Requests
          </span>
          <span id="badge-deposits-count" class="bg-red-500 text-white font-black text-[10px] px-2 py-0.5 rounded-full hidden">0</span>
        </button>

        <button onclick="switchTab('tab-withdrawals')" id="btn-tab-withdrawals" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 transition-all border border-transparent">
          <span class="flex items-center gap-3">
            <i class="fa-solid fa-money-bill-transfer text-lg"></i> Withdrawals
          </span>
          <span id="badge-withdrawals-count" class="bg-red-500 text-white font-black text-[10px] px-2 py-0.5 rounded-full hidden">0</span>
        </button>

        <button onclick="switchTab('tab-users')" id="btn-tab-users" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 transition-all border border-transparent">
          <span class="flex items-center gap-3">
            <i class="fa-solid fa-users text-lg"></i> Users & Players
          </span>
          <i class="fa-solid fa-angle-right text-xs"></i>
        </button>

        <button onclick="switchTab('tab-gifts')" id="btn-tab-gifts" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 transition-all border border-transparent">
          <span class="flex items-center gap-3">
            <i class="fa-solid fa-gift text-lg"></i> Gift Codes
          </span>
          <i class="fa-solid fa-angle-right text-xs"></i>
        </button>

        <button onclick="switchTab('tab-games')" id="btn-tab-games" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 transition-all border border-transparent">
          <span class="flex items-center gap-3">
            <i class="fa-solid fa-gamepad text-lg"></i> Game Controller
          </span>
          <i class="fa-solid fa-angle-right text-xs"></i>
        </button>

        <button onclick="switchTab('tab-settings')" id="btn-tab-settings" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold text-slate-400 hover:text-slate-200 hover:bg-slate-900/40 transition-all border border-transparent">
          <span class="flex items-center gap-3">
            <i class="fa-solid fa-sliders text-lg"></i> Site Settings
          </span>
          <i class="fa-solid fa-angle-right text-xs"></i>
        </button>
      </div>

      <div class="border-t border-slate-900 pt-4 text-center">
        <p class="text-[10px] text-slate-600">89 Club Secure Dashboard</p>
        <p class="text-[9px] text-slate-700 mt-0.5">V3.2.0 • Server Config Connected</p>
      </div>
    </aside>

    <!-- Main Content Panel Area -->
    <main class="flex-1 overflow-y-auto p-6 custom-scrollbar bg-slate-950 bg-opacity-40">
      
      <!-- TAB: Overview -->
      <section id="tab-overview" class="tab-panel space-y-6">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
          <i class="fa-solid fa-chart-line text-yellow-500"></i> Platform Statistics
        </h2>
        
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden group">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Users</p>
                <h3 id="stat-total-users" class="text-2xl font-black text-white mt-1">0</h3>
              </div>
              <div class="text-yellow-500 bg-yellow-500 bg-opacity-5 p-3 rounded-xl">
                <i class="fa-solid fa-users text-2xl"></i>
              </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-yellow-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
          </div>

          <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden group">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pending Deposits</p>
                <h3 id="stat-pending-deposits" class="text-2xl font-black text-red-400 mt-1">0</h3>
              </div>
              <div class="text-red-400 bg-red-400 bg-opacity-5 p-3 rounded-xl">
                <i class="fa-solid fa-wallet text-2xl"></i>
              </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-red-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
          </div>

          <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden group">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pending Withdrawals</p>
                <h3 id="stat-pending-withdrawals" class="text-2xl font-black text-blue-400 mt-1">0</h3>
              </div>
              <div class="text-blue-400 bg-blue-400 bg-opacity-5 p-3 rounded-xl">
                <i class="fa-solid fa-money-bill-transfer text-2xl"></i>
              </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-blue-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
          </div>

          <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden group">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Player Balances</p>
                <h3 id="stat-total-wallet" class="text-2xl font-black text-emerald-400 mt-1">₹0.00</h3>
              </div>
              <div class="text-emerald-400 bg-emerald-400 bg-opacity-5 p-3 rounded-xl">
                <i class="fa-solid fa-indian-rupee-sign text-2xl"></i>
              </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-emerald-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
          </div>
        </div>

        <!-- Live Analytics Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
          <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg space-y-4">
            <h3 class="font-bold text-white text-sm flex items-center gap-2 border-b border-slate-850 pb-2">
              <i class="fa-solid fa-chart-area text-amber-500 animate-pulse"></i> Deposit Volume Trends
            </h3>
            <div class="h-64 relative">
              <canvas id="depositChart"></canvas>
            </div>
          </div>
          <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg space-y-4">
            <h3 class="font-bold text-white text-sm flex items-center gap-2 border-b border-slate-850 pb-2">
              <i class="fa-solid fa-gamepad text-amber-500 animate-pulse"></i> Live Game Bets Volume
            </h3>
            <div class="h-64 relative">
              <canvas id="wagerChart"></canvas>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Pending Activities List -->
          <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
              <h3 class="font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-bell text-yellow-500"></i> Recent Pending Transactions
              </h3>
              <span class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-800 text-slate-400">Realtime Stream</span>
            </div>
            <div id="recent-pending-container" class="divide-y divide-slate-800 max-h-[350px] overflow-y-auto custom-scrollbar pr-2 space-y-2">
              <!-- Dynamically populated -->
              <p class="text-slate-500 text-sm py-4 text-center">No pending transactions at the moment.</p>
            </div>
          </div>

          <!-- Quick Setup Card -->
          <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg space-y-4">
            <div class="border-b border-slate-800 pb-3">
              <h3 class="font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-bolt text-yellow-500"></i> Quick Actions
              </h3>
            </div>
            <div class="space-y-3">
              <button onclick="switchTab('tab-deposits')" class="w-full flex items-center justify-between px-4 py-3 bg-slate-950 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 rounded-xl transition-all group">
                <span class="text-sm font-semibold flex items-center gap-3">
                  <i class="fa-solid fa-wallet text-yellow-500"></i> Process Deposits
                </span>
                <i class="fa-solid fa-arrow-right text-slate-500 group-hover:translate-x-1 transition-transform"></i>
              </button>
              <button onclick="switchTab('tab-withdrawals')" class="w-full flex items-center justify-between px-4 py-3 bg-slate-950 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 rounded-xl transition-all group">
                <span class="text-sm font-semibold flex items-center gap-3">
                  <i class="fa-solid fa-money-bill-transfer text-blue-400"></i> Process Withdrawals
                </span>
                <i class="fa-solid fa-arrow-right text-slate-500 group-hover:translate-x-1 transition-transform"></i>
              </button>
              <button onclick="switchTab('tab-users')" class="w-full flex items-center justify-between px-4 py-3 bg-slate-950 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 rounded-xl transition-all group">
                <span class="text-sm font-semibold flex items-center gap-3">
                  <i class="fa-solid fa-users text-emerald-400"></i> Manage User Balances
                </span>
                <i class="fa-solid fa-arrow-right text-slate-500 group-hover:translate-x-1 transition-transform"></i>
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- TAB: Deposits -->
      <section id="tab-deposits" class="tab-panel space-y-6 hidden">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
          <div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
              <i class="fa-solid fa-wallet text-yellow-500"></i> Manual Deposit Requests
            </h2>
            <p class="text-xs text-slate-500">Approve or deny manual deposit requests here. Approvals immediately update user wallets.</p>
          </div>
          <span id="pending-deposit-badge" class="bg-yellow-500 bg-opacity-10 text-yellow-500 text-xs px-3 py-1 rounded-full font-bold">0 Pending</span>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-lg">
          <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                  <th class="px-6 py-4 font-bold">Player Mobile</th>
                  <th class="px-6 py-4 font-bold">Amount</th>
                  <th class="px-6 py-4 font-bold">UTR / TxID</th>
                  <th class="px-6 py-4 font-bold">Method</th>
                  <th class="px-6 py-4 font-bold">Screenshot</th>
                  <th class="px-6 py-4 font-bold">Created Date</th>
                  <th class="px-6 py-4 font-bold text-center">Actions</th>
                </tr>
              </thead>
              <tbody id="deposit-requests-table-body" class="divide-y divide-slate-800 text-sm">
                <!-- Dynamically populated -->
                <tr>
                  <td colspan="7" class="px-6 py-8 text-center text-slate-500">No deposit requests loaded yet. Wait for sync.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- TAB: Withdrawals -->
      <section id="tab-withdrawals" class="tab-panel space-y-6 hidden">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
          <div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
              <i class="fa-solid fa-money-bill-transfer text-blue-400"></i> Withdrawal Requests
            </h2>
            <p class="text-xs text-slate-500">Manage withdrawals. Rejections will immediately refund the withdrawal amount back to the player's wallet.</p>
          </div>
          <span id="pending-withdrawal-badge" class="bg-blue-400 bg-opacity-10 text-blue-400 text-xs px-3 py-1 rounded-full font-bold">0 Pending</span>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-lg">
          <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                  <th class="px-6 py-4 font-bold">Player Mobile</th>
                  <th class="px-6 py-4 font-bold">Amount</th>
                  <th class="px-6 py-4 font-bold">Method</th>
                  <th class="px-6 py-4 font-bold">Account / Wallet Info</th>
                  <th class="px-6 py-4 font-bold">Created Date</th>
                  <th class="px-6 py-4 font-bold text-center">Actions</th>
                </tr>
              </thead>
              <tbody id="withdrawal-requests-table-body" class="divide-y divide-slate-800 text-sm">
                <!-- Dynamically populated -->
                <tr>
                  <td colspan="6" class="px-6 py-8 text-center text-slate-500">No withdrawal requests loaded yet. Wait for sync.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- TAB: Users -->
      <section id="tab-users" class="tab-panel space-y-6 hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800 pb-4">
          <div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
              <i class="fa-solid fa-users text-emerald-400"></i> User Management
            </h2>
            <p class="text-xs text-slate-500">Search and adjust user balances, resets passwords, toggle demo status, and ban/unban.</p>
          </div>
          <div class="flex gap-2">
            <input type="text" autocomplete="off" id="userSearchInput" oninput="filterUsers()" placeholder="Search by Mobile..." class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 text-sm transition-colors w-64" value="">
          </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-lg">
          <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                  <th class="px-6 py-4 font-bold">Player Mobile</th>
                  <th class="px-6 py-4 font-bold">Balance</th>
                  <th class="px-6 py-4 font-bold">Total deposit</th>
                  <th class="px-6 py-4 font-bold">Total Bet</th>
                  <th class="px-6 py-4 font-bold">Type</th>
                  <th class="px-6 py-4 font-bold">Status</th>
                  <th class="px-6 py-4 font-bold text-center">Manage Balance</th>
                  <th class="px-6 py-4 font-bold text-center">Security</th>
                  <th class="px-6 py-4 font-bold text-center">Actions</th>
                </tr>
              </thead>
              <tbody id="users-table-body" class="divide-y divide-slate-800 text-sm">
                <!-- Dynamically populated -->
                <tr>
                  <td colspan="9" class="px-6 py-8 text-center text-slate-500">No users found. Wait for sync.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- TAB: Gift Codes -->
      <section id="tab-gifts" class="tab-panel space-y-6 hidden">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
          <div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
              <i class="fa-solid fa-gift text-yellow-500"></i> Gift Codes Management
            </h2>
            <p class="text-xs text-slate-500">Generate, view, and disable gift codes. Players can claim these codes in the game for direct balance credit.</p>
          </div>
        </div>

        <!-- Generate Gift Code Form -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg space-y-4">
          <h3 class="font-bold text-white text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-plus text-yellow-500"></i> Create New Gift Code
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Code</label>
              <div class="flex gap-2">
                <input type="text" id="giftCodeInput" placeholder="e.g. GIFT89CLUB" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white placeholder-slate-600 focus:outline-none focus:border-yellow-500 text-sm">
                <button onclick="generateRandomGiftCode()" class="bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center justify-center">
                  <i class="fa-solid fa-arrows-rotate"></i>
                </button>
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Reward Amount</label>
              <input type="number" id="giftAmountInput" placeholder="e.g. 500" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white placeholder-slate-600 focus:outline-none focus:border-yellow-500 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Wagering Requirement (Turnover)</label>
              <input type="number" id="giftTurnoverInput" placeholder="e.g. 500 (Optional)" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white placeholder-slate-600 focus:outline-none focus:border-yellow-500 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Deposit Requirement</label>
              <input type="number" id="giftMinDepositInput" placeholder="e.g. 500 (Optional)" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white placeholder-slate-600 focus:outline-none focus:border-yellow-500 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Usage Limit (Max Users)</label>
              <input type="number" id="giftMaxUsersInput" placeholder="e.g. 100" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white placeholder-slate-600 focus:outline-none focus:border-yellow-500 text-sm">
            </div>
          </div>
          <div class="flex justify-end pt-2">
            <button onclick="createGiftCode()" class="bg-yellow-500 hover:bg-yellow-600 text-slate-950 font-bold px-6 py-2.5 rounded-xl text-sm transition-all shadow-lg shadow-yellow-500/10">
              Generate & Save Code
            </button>
          </div>
        </div>

        <!-- Gift Codes Table -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-lg">
          <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-950 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                  <th class="px-6 py-4 font-bold">Gift Code</th>
                  <th class="px-6 py-4 font-bold">Reward</th>
                  <th class="px-6 py-4 font-bold">Turnover Req.</th>
                  <th class="px-6 py-4 font-bold">Min Deposit</th>
                  <th class="px-6 py-4 font-bold">Usage</th>
                  <th class="px-6 py-4 font-bold">Status</th>
                  <th class="px-6 py-4 font-bold text-center">Action</th>
                </tr>
              </thead>
              <tbody id="gift-codes-table-body" class="divide-y divide-slate-800 text-sm">
                <tr>
                  <td colspan="7" class="px-6 py-8 text-center text-slate-500">No gift codes found. Generate one above!</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- TAB: Game Controller -->
      <section id="tab-games" class="tab-panel space-y-6 hidden">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
          <div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
              <i class="fa-solid fa-gamepad text-amber-500"></i> Game Controller & Overrides
            </h2>
            <p class="text-xs text-slate-500">Control active betting periods, toggle auto-profit optimization, or manually force results globally.</p>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Game Select Sidebar Card -->
          <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg space-y-4">
            <h3 class="font-bold text-white text-md flex items-center gap-2 border-b border-slate-800 pb-2">
              <i class="fa-solid fa-play text-amber-500"></i> Select Game
            </h3>
            
            <div class="space-y-2 max-h-[400px] overflow-y-auto pr-1 custom-scrollbar" id="game-controller-selector">
              <!-- WinGo Games -->
              <div class="text-xs uppercase font-bold text-slate-500 mt-2">WinGo Lotteries</div>
              <button onclick="selectControllerGame('wingo', 1, 'WinGo 1 Min')" id="btn-gc-wingo-1" class="w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-slate-950 hover:bg-slate-800 border border-slate-800 transition-colors flex justify-between items-center">
                <span>WinGo 1 Min</span>
                <span class="text-[10px] text-slate-500 font-semibold" id="timer-gc-wingo-1">--s</span>
              </button>
              <button onclick="selectControllerGame('wingo', 2, 'WinGo 3 Min')" id="btn-gc-wingo-2" class="w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold text-slate-400 bg-slate-950/40 hover:bg-slate-800 border border-slate-900 hover:border-slate-800 transition-colors flex justify-between items-center">
                <span>WinGo 3 Min</span>
                <span class="text-[10px] text-slate-600 font-semibold" id="timer-gc-wingo-2">--s</span>
              </button>
              <button onclick="selectControllerGame('wingo', 3, 'WinGo 5 Min')" id="btn-gc-wingo-3" class="w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold text-slate-400 bg-slate-950/40 hover:bg-slate-800 border border-slate-900 hover:border-slate-800 transition-colors flex justify-between items-center">
                <span>WinGo 5 Min</span>
                <span class="text-[10px] text-slate-600 font-semibold" id="timer-gc-wingo-3">--s</span>
              </button>
              <button onclick="selectControllerGame('wingo', 5, 'WinGo 30 Sec')" id="btn-gc-wingo-5" class="w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold text-slate-400 bg-slate-950/40 hover:bg-slate-800 border border-slate-900 hover:border-slate-800 transition-colors flex justify-between items-center">
                <span>WinGo 30 Sec</span>
                <span class="text-[10px] text-slate-600 font-semibold" id="timer-gc-wingo-5">--s</span>
              </button>

              <!-- TRX Games -->
              <div class="text-xs uppercase font-bold text-slate-500 mt-4">TRX Hash Lotteries</div>
              <button onclick="selectControllerGame('trx', 13, 'TRX 1 Min')" id="btn-gc-trx-13" class="w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold text-slate-400 bg-slate-950/40 hover:bg-slate-800 border border-slate-900 hover:border-slate-800 transition-colors flex justify-between items-center">
                <span>TRX 1 Min</span>
                <span class="text-[10px] text-slate-600 font-semibold" id="timer-gc-trx-13">--s</span>
              </button>
              <button onclick="selectControllerGame('trx', 14, 'TRX 3 Min')" id="btn-gc-trx-14" class="w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold text-slate-400 bg-slate-950/40 hover:bg-slate-800 border border-slate-900 hover:border-slate-800 transition-colors flex justify-between items-center">
                <span>TRX 3 Min</span>
                <span class="text-[10px] text-slate-600 font-semibold" id="timer-gc-trx-14">--s</span>
              </button>

              <!-- K3 Games -->
              <div class="text-xs uppercase font-bold text-slate-500 mt-4">K3 Dice Lotteries</div>
              <button onclick="selectControllerGame('k3', 9, 'K3 1 Min')" id="btn-gc-k3-9" class="w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold text-slate-400 bg-slate-950/40 hover:bg-slate-800 border border-slate-900 hover:border-slate-800 transition-colors flex justify-between items-center">
                <span>K3 1 Min</span>
                <span class="text-[10px] text-slate-600 font-semibold" id="timer-gc-k3-9">--s</span>
              </button>

              <!-- 5D Games -->
              <div class="text-xs uppercase font-bold text-slate-500 mt-4">5D Digit Lotteries</div>
              <button onclick="selectControllerGame('d5', 5, '5D 1 Min')" id="btn-gc-d5-5" class="w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold text-slate-400 bg-slate-950/40 hover:bg-slate-800 border border-slate-900 hover:border-slate-800 transition-colors flex justify-between items-center">
                <span>5D 1 Min</span>
                <span class="text-[10px] text-slate-600 font-semibold" id="timer-gc-d5-5">--s</span>
              </button>
            </div>
          </div>

          <!-- Active Panel Controls (Middle + Right) -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Active Game Overview Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Selected Game</span>
                <span class="text-lg font-black text-amber-500 mt-1 block" id="gc-active-title">WinGo 1 Min</span>
              </div>
              <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Current Period ID</span>
                <span class="text-lg font-black text-white mt-1 block select-all" id="gc-active-period">--</span>
              </div>
              <div class="relative">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Time Remaining</span>
                <span class="text-2xl font-black text-red-500 mt-1 block animate-pulse" id="gc-active-timer">--s</span>
              </div>
            </div>

            <!-- Optimizer Mode Switch -->
            <div class="bg-slate-900 border border-[#1e273a] rounded-2xl p-6 shadow-lg flex items-center justify-between border-l-4 border-l-amber-500">
              <div>
                <h3 class="font-bold text-white text-md flex items-center gap-2">
                  <i class="fa-solid fa-robot text-amber-500"></i> House-Optimal Profit Mode
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">When active, the system draws the outcome with the minimum payout (maximizing house winnings).</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="gc-auto-profit-toggle" onchange="toggleGcAutoProfit()" class="sr-only peer">
                <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
              </label>
            </div>

            <!-- MANUAL RESULT OVERRIDE MODULE -->
            <div class="bg-slate-900 border border-[#1e273a] rounded-2xl p-6 shadow-lg space-y-4">
              <h3 class="font-bold text-white text-md flex items-center gap-2 border-b border-slate-800 pb-2">
                <i class="fa-solid fa-bolt-lightning text-amber-500"></i> Manual Result Override
              </h3>
              
              <!-- Active Override Display -->
              <div id="active-override-status" class="bg-amber-500 bg-opacity-5 border border-amber-500/10 text-amber-400 text-xs px-4 py-2.5 rounded-xl flex items-center gap-2 hidden">
                <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                <span>Override active: Forced outcome will be <strong class="text-white select-all" id="override-label-status">N/A</strong>.</span>
                <button onclick="cancelActiveOverride()" class="ml-auto text-red-400 hover:text-red-300 font-bold">Clear</button>
              </div>

              <!-- WinGo / TRX Pad -->
              <div id="gc-pad-wingo" class="space-y-4">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Select forced winning color or size:</div>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
                  <button onclick="setResultOverride('red')" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs transition-all shadow-md">Forced RED (0,2,4,6,8)</button>
                  <button onclick="setResultOverride('green')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-all shadow-md">Forced GREEN (1,3,5,7,9)</button>
                  <button onclick="setResultOverride('violet')" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-xs transition-all shadow-md">Forced VIOLET (0,5)</button>
                  <button onclick="setResultOverride('big')" class="px-4 py-2 bg-slate-950 hover:bg-slate-800 text-white border border-slate-800 font-bold rounded-xl text-xs transition-all">Forced BIG (5-9)</button>
                  <button onclick="setResultOverride('small')" class="px-4 py-2 bg-slate-950 hover:bg-slate-800 text-white border border-slate-800 font-bold rounded-xl text-xs transition-all">Forced SMALL (0-4)</button>
                </div>
                
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider pt-2">Or select forced winning digit (0 to 9):</div>
                <div class="grid grid-cols-5 md:grid-cols-10 gap-2">
                  <!-- Generate numbers 0 to 9 -->
                  <button onclick="setResultOverride(0)" class="aspect-square bg-slate-950 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-black rounded-xl border border-slate-800 text-lg transition-all flex items-center justify-center">0</button>
                  <button onclick="setResultOverride(1)" class="aspect-square bg-slate-950 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-black rounded-xl border border-slate-800 text-lg transition-all flex items-center justify-center">1</button>
                  <button onclick="setResultOverride(2)" class="aspect-square bg-slate-950 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-black rounded-xl border border-slate-800 text-lg transition-all flex items-center justify-center">2</button>
                  <button onclick="setResultOverride(3)" class="aspect-square bg-slate-950 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-black rounded-xl border border-slate-800 text-lg transition-all flex items-center justify-center">3</button>
                  <button onclick="setResultOverride(4)" class="aspect-square bg-slate-950 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-black rounded-xl border border-slate-800 text-lg transition-all flex items-center justify-center">4</button>
                  <button onclick="setResultOverride(5)" class="aspect-square bg-slate-950 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-black rounded-xl border border-slate-800 text-lg transition-all flex items-center justify-center">5</button>
                  <button onclick="setResultOverride(6)" class="aspect-square bg-slate-950 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-black rounded-xl border border-slate-800 text-lg transition-all flex items-center justify-center">6</button>
                  <button onclick="setResultOverride(7)" class="aspect-square bg-slate-950 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-black rounded-xl border border-slate-800 text-lg transition-all flex items-center justify-center">7</button>
                  <button onclick="setResultOverride(8)" class="aspect-square bg-slate-950 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-black rounded-xl border border-slate-800 text-lg transition-all flex items-center justify-center">8</button>
                  <button onclick="setResultOverride(9)" class="aspect-square bg-slate-950 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-black rounded-xl border border-slate-800 text-lg transition-all flex items-center justify-center">9</button>
                </div>
              </div>

              <!-- K3 Override Pad -->
              <div id="gc-pad-k3" class="space-y-4 hidden">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Select Forced Roll for 3 Dice:</div>
                <div class="flex items-center gap-3">
                  <div>
                    <label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Dice 1</label>
                    <select id="k3-override-d1" class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-amber-500">
                      <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Dice 2</label>
                    <select id="k3-override-d2" class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-amber-500">
                      <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Dice 3</label>
                    <select id="k3-override-d3" class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-amber-500">
                      <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option>
                    </select>
                  </div>
                  <button onclick="setK3ResultOverride()" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-2 rounded-xl text-xs transition-colors self-end h-[38px]">Force Dice Roll</button>
                </div>
              </div>

              <!-- 5D Override Pad -->
              <div id="gc-pad-d5" class="space-y-4 hidden">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Type Forced 5-Digit Sequence:</div>
                <div class="flex items-center gap-3">
                  <input type="text" id="d5-override-input" maxlength="5" placeholder="e.g. 57291" class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-amber-500 text-sm w-48">
                  <button onclick="setD5ResultOverride()" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-2 rounded-xl text-xs transition-colors h-[38px]">Force 5D Sequence</button>
                </div>
              </div>
            </div>

            <!-- Active Bets List inside Game Controller -->
            <div class="bg-slate-900 border border-[#1e273a] rounded-2xl p-6 shadow-lg space-y-4">
              <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                <h3 class="font-bold text-white text-md flex items-center gap-2">
                  <i class="fa-solid fa-coins text-amber-500"></i> Active Bets placed on this Period
                </h3>
                <span id="gc-bets-total" class="bg-amber-500 bg-opacity-10 text-amber-400 text-xs px-2.5 py-0.5 rounded-full font-bold">Total Bet: ₹0.00</span>
              </div>

              <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs">
                  <thead>
                    <tr class="bg-slate-950 text-slate-400 uppercase tracking-wider border-b border-slate-800">
                      <th class="px-4 py-3 font-bold">Mobile</th>
                      <th class="px-4 py-3 font-bold text-center">Option Selected</th>
                      <th class="px-4 py-3 font-bold text-center">Multiplier</th>
                      <th class="px-4 py-3 font-bold text-right">Bet Amount</th>
                    </tr>
                  </thead>
                  <tbody id="gc-bets-table-body" class="divide-y divide-slate-800">
                    <tr>
                      <td colspan="4" class="px-4 py-6 text-center text-slate-500">No bets placed on this period yet.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- TAB: Settings -->
      <section id="tab-settings" class="tab-panel space-y-6 hidden">
        <h2 class="text-xl font-bold text-white flex items-center gap-2 border-b border-slate-800 pb-4">
          <i class="fa-solid fa-sliders text-yellow-500"></i> Global Site Settings
        </h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Payment Settings -->
          <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg space-y-4">
            <h3 class="font-bold text-white text-md flex items-center gap-2">
              <i class="fa-solid fa-credit-card text-yellow-500"></i> Gateway & Payment Settings
            </h3>
            
            <div class="space-y-4 pt-2">
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Active Deposit UPI ID</label>
                <input type="text" id="setting-upi-id" placeholder="example@upi" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors">
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Deposit UPI QR Image URL</label>
                <input type="text" id="setting-upi-qr" placeholder="https://imgur.com/your-qr-code.png" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors">
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">USDT Deposit TRC20 Address</label>
                <input type="text" id="setting-usdt-address" placeholder="Txxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors">
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">USDT Conversion Rate (INR per 1 USDT)</label>
                <input type="number" step="0.01" id="setting-usdt-rate" placeholder="90" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors">
              </div>
              <div class="hidden">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Active bKash Wallet Number (Bangladesh BDT)</label>
                <input type="text" id="setting-bkash-wallet" placeholder="01354743800" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors">
              </div>
              <div class="hidden">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Active Nagad Wallet Number (Bangladesh BDT)</label>
                <input type="text" id="setting-nagad-wallet" placeholder="01942136883" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors">
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">XSBDWIN Gateway Base URL</label>
                <input type="text" id="setting-gateway-base-url" placeholder="https://xswallet.cyou/api" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors">
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">XSBDWIN Gateway App ID</label>
                <input type="text" id="setting-gateway-app-id" placeholder="GP_SUB_43366914" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors">
              </div>
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">XSBDWIN Gateway Secret Key</label>
                <input type="text" id="setting-gateway-secret-key" placeholder="f4445014c07a8b4a9e9d62234c80d128" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors">
              </div>
              <button onclick="savePaymentSettings()" class="bg-yellow-500 hover:bg-yellow-600 text-slate-950 font-bold px-6 py-3 rounded-xl transition-all shadow-lg shadow-yellow-500/10">
                Save Payment Settings
              </button>
            </div>
          </div>

          <!-- Configuration Controls -->
          <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg space-y-4">
            <h3 class="font-bold text-white text-md flex items-center gap-2">
              <i class="fa-solid fa-gamepad text-yellow-500"></i> Platform Configuration
            </h3>
            
            <div class="space-y-4 pt-2">
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Default Signup Wallet Balance</label>
                <input type="number" step="0.01" id="setting-signup-balance" placeholder="0" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors">
                <span class="text-[10px] text-slate-500 mt-1 block">New registered players will start with this balance.</span>
              </div>

              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Min Deposit Required to Withdraw</label>
                <input type="number" step="1" id="setting-min-deposit-withdraw" placeholder="250" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors">
                <span class="text-[10px] text-slate-500 mt-1 block">Players must have deposited at least this much before withdrawing.</span>
              </div>

              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Change Admin Panel Password</label>
                <input type="text" id="setting-admin-password" placeholder="New dashboard login password" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors">
                <span class="text-[10px] text-slate-500 mt-1 block">Leave empty to keep your current password.</span>
              </div>
              
              <div class="border-t border-slate-800 pt-4">
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-sm font-bold text-white">Maintenance Mode</h4>
                    <p class="text-xs text-slate-500">Toggle this to restrict users from accessing the app front-end.</p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="setting-maintenance" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
                  </label>
                </div>
              </div>
              
              <button onclick="savePlatformSettings()" class="bg-yellow-500 hover:bg-yellow-600 text-slate-950 font-bold px-6 py-3 rounded-xl transition-all shadow-lg shadow-yellow-500/10">
                Save Platform Settings
              </button>
            </div>
          </div>
        </div>
      </section>

    </main>
  </div>

  <!-- Global Modal Popup (Approve/Reject confirmation or text prompts) -->
  <div id="actionModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden transition-opacity">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl mx-4">
      <h3 id="modalTitle" class="text-xl font-bold text-white mb-2">Perform Action</h3>
      <p id="modalDesc" class="text-slate-400 text-sm mb-4">Are you sure you want to proceed?</p>
      
      <div id="modalInputContainer" class="hidden mb-4">
        <!-- Input injected dynamically if needed -->
      </div>
      
      <div class="flex justify-end gap-3">
        <button onclick="closeActionModal()" class="px-5 py-2.5 rounded-xl border border-slate-800 hover:bg-slate-800 text-slate-300 text-sm transition-colors">Cancel</button>
        <button id="modalConfirmBtn" class="px-5 py-2.5 rounded-xl bg-yellow-500 hover:bg-yellow-600 text-slate-950 font-bold text-sm transition-colors">Confirm</button>
      </div>
    </div>
  </div>

  <!-- Firebase Integration Logic Script -->
  <script>
    // Server-injected Firebase configurations
    const fbUrl = <?php echo json_encode($firebaseUrl); ?>;
    const fbSecret = <?php echo json_encode($firebaseSecret); ?>;

    let db;
    let usersData = {};
    let depositsData = {};
    let withdrawalsData = {};
    let settingsData = {};
    const tempInputs = {};
    function storeTempInput(key, value) {
      tempInputs[key] = value;
    }

    let giftCodesData = {};
    let gcActiveGameType = 'wingo';
    let gcActiveGameTypeId = 1;
    let gcActiveGameTitle = 'WinGo 1 Min';
    let gcActivePeriodId = '';
    let gcTimerInterval = null;
    let gcBetsData = {};
    let gcActiveOverrides = {};

    window.addEventListener('DOMContentLoaded', () => {
      if (!fbUrl) {
        alert('FIREBASE_URL environment variable is missing on the server! Cannot connect.');
        return;
      }
      const searchBox = document.getElementById('userSearchInput');
      if (searchBox) searchBox.value = '';
      initFirebase(fbUrl, fbSecret);
      
      // Start Game Controller timer sync
      setInterval(tickController, 1000);
    });

    function initFirebase(url, secret) {
      document.getElementById('connectionBadge').innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Connecting...`;
      document.getElementById('connectionBadge').className = "text-[10px] px-2 py-0.5 rounded-full bg-yellow-600 text-white font-semibold flex items-center gap-1 uppercase tracking-wider";
      document.getElementById('connectedDbLabel').innerText = url;

      try {
        const config = { databaseURL: url };
        firebase.initializeApp(config);
        
        db = firebase.database();
        
        // Sync connection state
        db.ref('.info/connected').on('value', (snap) => {
          if (snap.val() === true) {
            updateConnectionBadge(true);
            syncData();
          } else {
            updateConnectionBadge(false);
          }
        });
      } catch (err) {
        console.error(err);
        alert('Failed to connect to Firebase database: ' + err.message);
        updateConnectionBadge(false);
      }
    }

    function updateConnectionBadge(connected) {
      const badge = document.getElementById('connectionBadge');
      if (connected) {
        badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Connected`;
        badge.className = "text-[10px] px-2 py-0.5 rounded-full bg-emerald-500 text-white font-semibold flex items-center gap-1 uppercase tracking-wider";
      } else {
        badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-white"></span> Offline`;
        badge.className = "text-[10px] px-2 py-0.5 rounded-full bg-red-500 text-white font-semibold flex items-center gap-1 uppercase tracking-wider";
      }
    }

    // Sync Data Hooks
    function syncData() {
      db.ref('deposit_settings').on('value', snap => {
        settingsData.deposit_settings = snap.val() || {};
        updateSettingsUI();
      });

      db.ref('system_settings').on('value', snap => {
        settingsData.system_settings = snap.val() || {};
        updateSettingsUI();
        updatePlatformSettingsUI();
      });

      db.ref('users').on('value', snap => {
        usersData = snap.val() || {};
        updateDashboardStats();
        updateUsersTable();
      });

      db.ref('deposits').on('value', snap => {
        depositsData = snap.val() || {};
        updateDashboardStats();
        updateDepositsTable();
        updateRecentPendingList();
      });

      db.ref('withdrawals').on('value', snap => {
        withdrawalsData = snap.val() || {};
        updateDashboardStats();
        updateWithdrawalsTable();
        updateRecentPendingList();
      });

      db.ref('gift_codes').on('value', snap => {
        giftCodesData = snap.val() || {};
        updateGiftCodesTable();
      });

      // Sync Game Controller overrides
      syncGcOverrides();
      selectControllerGame('wingo', 1, 'WinGo 1 Min');
    }

    // UI Tab Navigation Switching
    function switchTab(tabId) {
      document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.add('hidden'));
      document.getElementById(tabId).classList.remove('hidden');

      const menuButtons = [
        { id: 'btn-tab-overview', tab: 'tab-overview', activeColor: 'text-amber-500 bg-amber-500 bg-opacity-5 border-amber-500/25' },
        { id: 'btn-tab-deposits', tab: 'tab-deposits', activeColor: 'text-amber-500 bg-amber-500 bg-opacity-5 border-amber-500/25' },
        { id: 'btn-tab-withdrawals', tab: 'tab-withdrawals', activeColor: 'text-amber-500 bg-amber-500 bg-opacity-5 border-amber-500/25' },
        { id: 'btn-tab-users', tab: 'tab-users', activeColor: 'text-amber-500 bg-amber-500 bg-opacity-5 border-amber-500/25' },
        { id: 'btn-tab-gifts', tab: 'tab-gifts', activeColor: 'text-amber-500 bg-amber-500 bg-opacity-5 border-amber-500/25' },
        { id: 'btn-tab-games', tab: 'tab-games', activeColor: 'text-amber-500 bg-amber-500 bg-opacity-5 border-amber-500/25' },
        { id: 'btn-tab-settings', tab: 'tab-settings', activeColor: 'text-amber-500 bg-amber-500 bg-opacity-5 border-amber-500/25' }
      ];

      menuButtons.forEach(btn => {
        const el = document.getElementById(btn.id);
        if (el) {
          if (btn.tab === tabId) {
            el.className = `w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold border ${btn.activeColor} transition-all`;
          } else {
            el.className = "w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold text-slate-400 hover:text-slate-200 hover:bg-slate-900 transition-all border border-transparent";
          }
        }
      });
    }

    // Dashboard Calculations
    function updateDashboardStats() {
      const totalUsers = Object.keys(usersData).length;
      document.getElementById('stat-total-users').innerText = totalUsers;

      let totalWallet = 0;
      Object.values(usersData).forEach(u => {
        totalWallet += parseFloat(u.motta || 0);
      });
      document.getElementById('stat-total-wallet').innerText = '₹' + totalWallet.toFixed(2);

      let pendingDeposits = 0;
      Object.values(depositsData).forEach(d => {
        if (d.status === 'pending') pendingDeposits++;
      });
      document.getElementById('stat-pending-deposits').innerText = pendingDeposits;
      
      const depBadge = document.getElementById('badge-deposits-count');
      const depBadge2 = document.getElementById('pending-deposit-badge');
      if (pendingDeposits > 0) {
        depBadge.innerText = pendingDeposits;
        depBadge.classList.remove('hidden');
        depBadge2.innerText = `${pendingDeposits} Pending`;
        depBadge2.className = "bg-red-500 bg-opacity-10 text-red-500 text-xs px-3 py-1 rounded-full font-bold";
      } else {
        depBadge.classList.add('hidden');
        depBadge2.innerText = `0 Pending`;
        depBadge2.className = "bg-slate-800 text-slate-500 text-xs px-3 py-1 rounded-full font-bold";
      }

      let pendingWithdrawals = 0;
      Object.values(withdrawalsData).forEach(w => {
        if (w.status === 'pending') pendingWithdrawals++;
      });
      document.getElementById('stat-pending-withdrawals').innerText = pendingWithdrawals;
      
      const wdBadge = document.getElementById('badge-withdrawals-count');
      const wdBadge2 = document.getElementById('pending-withdrawal-badge');
      if (pendingWithdrawals > 0) {
        wdBadge.innerText = pendingWithdrawals;
        wdBadge.classList.remove('hidden');
        wdBadge2.innerText = `${pendingWithdrawals} Pending`;
        wdBadge2.className = "bg-red-500 bg-opacity-10 text-red-500 text-xs px-3 py-1 rounded-full font-bold";
      } else {
        wdBadge.classList.add('hidden');
        wdBadge2.innerText = `0 Pending`;
        wdBadge2.className = "bg-slate-800 text-slate-500 text-xs px-3 py-1 rounded-full font-bold";
      }

      // Update Live Chart.js analytics graphs
      updateCharts();
    }

    let depositChartInstance = null;
    let wagerChartInstance = null;

    function updateCharts() {
      // 1. Update Deposits Chart
      const deposits = Object.values(depositsData);
      const dateMap = {};
      
      // Get last 7 days of dates
      for (let i = 6; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        const dateStr = d.toISOString().split('T')[0];
        dateMap[dateStr] = 0;
      }
      
      deposits.forEach(d => {
        if (d.status === 'success' && d.createdAt) {
          const dateStr = d.createdAt.split(' ')[0] || d.createdAt.split('T')[0];
          if (dateMap[dateStr] !== undefined) {
            dateMap[dateStr] += parseFloat(d.amount || 0);
          }
        }
      });
      
      const depositLabels = Object.keys(dateMap);
      const depositValues = Object.values(dateMap);
      
      const canvasDep = document.getElementById('depositChart');
      if (canvasDep) {
        const ctxDep = canvasDep.getContext('2d');
        if (depositChartInstance) {
          depositChartInstance.data.labels = depositLabels;
          depositChartInstance.data.datasets[0].data = depositValues;
          depositChartInstance.update();
        } else {
          depositChartInstance = new Chart(ctxDep, {
            type: 'line',
            data: {
              labels: depositLabels,
              datasets: [{
                label: 'Approved Deposits (₹)',
                data: depositValues,
                borderColor: '#dfad3a',
                backgroundColor: 'rgba(223, 173, 58, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { display: false } },
              scales: {
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#64748b' } },
                x: { grid: { display: false }, ticks: { color: '#64748b' } }
              }
            }
          });
        }
      }

      // 2. Update Wager Chart (Distribution)
      const wagersMap = { 'WinGo': 0, 'TRX': 0, 'K3': 0, '5D': 0 };
      Object.values(usersData).forEach(u => {
        const betVol = parseFloat(u.total_bet || 0);
        if (betVol > 0) {
          wagersMap['WinGo'] += betVol * 0.45;
          wagersMap['TRX'] += betVol * 0.30;
          wagersMap['K3'] += betVol * 0.15;
          wagersMap['5D'] += betVol * 0.10;
        }
      });

      const wagerLabels = Object.keys(wagersMap);
      const wagerValues = Object.values(wagersMap).map(v => Math.round(v));

      const canvasWager = document.getElementById('wagerChart');
      if (canvasWager) {
        const ctxWager = canvasWager.getContext('2d');
        if (wagerChartInstance) {
          wagerChartInstance.data.labels = wagerLabels;
          wagerChartInstance.data.datasets[0].data = wagerValues;
          wagerChartInstance.update();
        } else {
          wagerChartInstance = new Chart(ctxWager, {
            type: 'bar',
            data: {
              labels: wagerLabels,
              datasets: [{
                label: 'Total Bets (₹)',
                data: wagerValues,
                backgroundColor: ['#dfad3a', '#3b82f6', '#10b981', '#ef4444'],
                borderRadius: 6
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { display: false } },
              scales: {
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#64748b' } },
                x: { grid: { display: false }, ticks: { color: '#64748b' } }
              }
            }
          });
        }
      }
    }

    // Populate Deposits Table
    function updateDepositsTable() {
      const tbody = document.getElementById('deposit-requests-table-body');
      tbody.innerHTML = '';

      const sortedDeposits = Object.entries(depositsData).map(([id, d]) => ({ id, ...d }));
      sortedDeposits.sort((a, b) => {
        if (a.status === 'pending' && b.status !== 'pending') return -1;
        if (a.status !== 'pending' && b.status === 'pending') return 1;
        return new Date(b.createdAt) - new Date(a.createdAt);
      });

      if (sortedDeposits.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-slate-500">No deposit records found in the database.</td></tr>`;
        return;
      }

      sortedDeposits.forEach(d => {
        let statusBadge = '';
        if (d.status === 'pending') {
          statusBadge = `<span class="bg-yellow-500/10 text-yellow-500 text-[10px] px-2 py-0.5 rounded font-semibold uppercase tracking-wider text-center block">Pending</span>`;
        } else if (d.status === 'success' || d.status === 'request success') {
          statusBadge = `<span class="bg-emerald-500/10 text-emerald-400 text-[10px] px-2 py-0.5 rounded font-semibold uppercase tracking-wider text-center block">Approved</span>`;
        } else if (d.status === 'request on gateway') {
          statusBadge = `<span class="bg-blue-500/10 text-blue-400 text-[10px] px-2 py-0.5 rounded font-semibold uppercase tracking-wider text-center block text-slate-300">Request on Gateway</span>`;
        } else {
          statusBadge = `<span class="bg-red-500/10 text-red-400 text-[10px] px-2 py-0.5 rounded font-semibold uppercase tracking-wider text-center block">Rejected</span>`;
        }

        const screenshotLink = d.screenshot 
          ? `<a href="${d.screenshot}" target="_blank" class="text-yellow-500 hover:underline flex items-center gap-1"><i class="fa-solid fa-image"></i> View Proof</a>` 
          : `<span class="text-slate-600">No upload</span>`;

        let actionButtons = '';
        if (d.status === 'pending') {
          actionButtons = `
            <div class="flex items-center justify-center gap-2">
              <button onclick="approveDeposit('${d.id}', '${d.userId}', ${d.amount})" class="bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs px-3 py-1.5 rounded-lg transition-colors">Approve</button>
              <button onclick="rejectDeposit('${d.id}')" class="bg-red-500 hover:bg-red-600 text-white font-bold text-xs px-3 py-1.5 rounded-lg transition-colors">Reject</button>
            </div>
          `;
        } else {
          actionButtons = `<div class="text-center">${statusBadge}</div>`;
        }

        const isBd = d.method === 'BKASH' || d.method === 'NAGAD' || d.userId.startsWith('880') || d.userId.startsWith('+880');
        const symbol = isBd ? '৳' : '₹';

        tbody.innerHTML += `
          <tr class="hover:bg-slate-900/40 transition-colors">
            <td class="px-6 py-4 font-bold text-white select-all">${d.userId}</td>
            <td class="px-6 py-4 font-semibold text-white">${symbol}${parseFloat(d.amount).toFixed(2)}</td>
            <td class="px-6 py-4 text-slate-400 select-all font-mono text-xs">${d.utr || 'N/A'}</td>
            <td class="px-6 py-4 text-slate-400">${d.method || 'UPI'}</td>
            <td class="px-6 py-4">${screenshotLink}</td>
            <td class="px-6 py-4 text-xs text-slate-500">${d.createdAt || 'N/A'}</td>
            <td class="px-6 py-4">${actionButtons}</td>
          </tr>
        `;
      });
    }

    // Populate Withdrawals Table
    function updateWithdrawalsTable() {
      const tbody = document.getElementById('withdrawal-requests-table-body');
      tbody.innerHTML = '';

      const sortedWd = Object.entries(withdrawalsData).map(([id, w]) => ({ id, ...w }));
      sortedWd.sort((a, b) => {
        if (a.status === 'pending' && b.status !== 'pending') return -1;
        if (a.status !== 'pending' && b.status === 'pending') return 1;
        return new Date(b.createdAt) - new Date(a.createdAt);
      });

      if (sortedWd.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-slate-500">No withdrawal records found in the database.</td></tr>`;
        return;
      }

      sortedWd.forEach(w => {
        let statusBadge = '';
        if (w.status === 'pending') {
          statusBadge = `<span class="bg-yellow-500/10 text-yellow-500 text-[10px] px-2 py-0.5 rounded font-semibold uppercase tracking-wider text-center block">Pending</span>`;
        } else if (w.status === 'approved') {
          statusBadge = `<span class="bg-emerald-500/10 text-emerald-400 text-[10px] px-2 py-0.5 rounded font-semibold uppercase tracking-wider text-center block">Approved</span>`;
        } else {
          statusBadge = `<span class="bg-red-500/10 text-red-400 text-[10px] px-2 py-0.5 rounded font-semibold uppercase tracking-wider text-center block">Rejected</span>`;
        }

        let detailsLabel = '';
        if (w.method === 'BANK_CARD') {
          detailsLabel = `<div class="text-xs space-y-0.5">
            <span class="text-slate-400 block"><strong class="text-white">Bank Detail:</strong> ${w.withdrawNumber || 'N/A'}</span>
          </div>`;
        } else {
          detailsLabel = `<span class="font-mono text-xs text-slate-300 select-all"><strong class="text-yellow-500 font-sans">USDT:</strong> ${w.withdrawNumber || 'N/A'}</span>`;
        }

        let actionButtons = '';
        if (w.status === 'pending') {
          actionButtons = `
            <div class="flex items-center justify-center gap-2">
              <button onclick="approveWithdrawal('${w.id}')" class="bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs px-3 py-1.5 rounded-lg transition-colors">Approve</button>
              <button onclick="rejectWithdrawal('${w.id}', '${w.userId}', ${w.amount})" class="bg-red-500 hover:bg-red-600 text-white font-bold text-xs px-3 py-1.5 rounded-lg transition-colors">Reject (Refund)</button>
            </div>
          `;
        } else {
          actionButtons = `<div class="text-center">${statusBadge}</div>`;
        }

        const isBd = w.userId.startsWith('880') || w.userId.startsWith('+880');
        const symbol = isBd ? '৳' : '₹';

        tbody.innerHTML += `
          <tr class="hover:bg-slate-900/40 transition-colors">
            <td class="px-6 py-4 font-bold text-white select-all">${w.userId}</td>
            <td class="px-6 py-4 font-semibold text-white">${symbol}${parseFloat(w.amount).toFixed(2)}</td>
            <td class="px-6 py-4 text-xs font-semibold text-slate-400">${w.method || 'BANK'}</td>
            <td class="px-6 py-4">${detailsLabel}</td>
            <td class="px-6 py-4 text-xs text-slate-500">${w.createdAt || 'N/A'}</td>
            <td class="px-6 py-4">${actionButtons}</td>
          </tr>
        `;
      });
    }

    // Populate Users Table & Filtering
    function updateUsersTable() {
      const tbody = document.getElementById('users-table-body');
      tbody.innerHTML = '';

      const list = Object.entries(usersData).map(([mobile, u]) => ({ mobile, ...u }));
      const search = document.getElementById('userSearchInput').value.trim();
      const filtered = list.filter(u => u.mobile.includes(search));

      if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="px-6 py-8 text-center text-slate-500">No players match the search criteria.</td></tr>`;
        return;
      }

      filtered.sort((a, b) => new Date(b.createdate) - new Date(a.createdate));

      filtered.forEach(u => {
        const typeBadge = u.is_demo 
          ? `<span class="bg-blue-500/10 text-blue-400 text-[10px] px-2 py-0.5 rounded font-black tracking-wider uppercase">Demo</span>` 
          : `<span class="bg-yellow-500/10 text-yellow-500 text-[10px] px-2 py-0.5 rounded font-black tracking-wider uppercase">Player</span>`;

        const statusBadge = (u.status == 1) 
          ? `<span class="bg-emerald-500/10 text-emerald-400 text-[10px] px-2 py-0.5 rounded font-semibold">Active</span>` 
          : `<span class="bg-red-500/10 text-red-400 text-[10px] px-2 py-0.5 rounded font-semibold">Banned</span>`;

        const statusActionBtn = (u.status == 1)
          ? `<button onclick="toggleUserStatus('${u.mobile}', 0)" class="bg-red-500 hover:bg-red-600 text-white text-[10px] font-bold px-2 py-1 rounded transition-colors w-16">Ban</button>`
          : `<button onclick="toggleUserStatus('${u.mobile}', 1)" class="bg-emerald-500 hover:bg-emerald-600 text-slate-950 text-[10px] font-bold px-2 py-1 rounded transition-colors w-16">Unban</button>`;

        const demoToggleBtn = u.is_demo
          ? `<button onclick="toggleUserDemo('${u.mobile}', false)" class="bg-slate-800 hover:bg-slate-700 text-slate-400 text-[10px] font-bold px-2 py-1 rounded transition-colors w-24">Set Player</button>`
          : `<button onclick="toggleUserDemo('${u.mobile}', true)" class="bg-blue-500 hover:bg-blue-600 text-white text-[10px] font-bold px-2 py-1 rounded transition-colors w-24">Set Demo</button>`;

        const avatarUrl = `https://api.dicebear.com/7.x/bottts-neutral/svg?seed=${u.mobile}`;
        const isBd = u.mobile.startsWith('880') || u.mobile.startsWith('+880');
        const symbol = isBd ? '৳' : '₹';

        tbody.innerHTML += `
          <tr class="hover:bg-slate-900/40 transition-colors">
            <td class="px-6 py-4 font-bold text-white select-all">
              <div class="flex items-center gap-2.5">
                <img src="${avatarUrl}" class="w-7 h-7 rounded-full bg-[#070a13] border border-[#151c2e] p-0.5" alt="Avatar">
                <span>${u.mobile}</span>
              </div>
            </td>
            <td class="px-6 py-4 font-black text-emerald-400">${symbol}${parseFloat(u.motta || 0).toFixed(2)}</td>
            <td class="px-6 py-4 text-slate-400">${symbol}${parseFloat(u.total_deposit || 0).toFixed(2)}</td>
            <td class="px-6 py-4 text-slate-400">${symbol}${parseFloat(u.total_bet || 0).toFixed(2)}</td>
            <td class="px-6 py-4">${typeBadge}</td>
            <td class="px-6 py-4">${statusBadge}</td>
            <td class="px-6 py-4 text-center">
              <div class="flex items-center justify-center gap-1.5">
                <input type="number" id="amt-${u.mobile}" placeholder="0" oninput="storeTempInput('amt-${u.mobile}', this.value)" value="${tempInputs['amt-' + u.mobile] || ''}" class="w-16 bg-slate-950 border border-slate-800 rounded px-2 py-1 text-xs text-white text-center focus:outline-none focus:border-yellow-500">
                <button onclick="inlineAdjustBalance('${u.mobile}', 'add')" class="bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 font-bold text-xs p-1.5 rounded border border-emerald-500/10 transition-colors" title="Add Balance"><i class="fa-solid fa-plus"></i></button>
                <button onclick="inlineAdjustBalance('${u.mobile}', 'deduct')" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 font-bold text-xs p-1.5 rounded border border-red-500/10 transition-colors" title="Deduct Balance"><i class="fa-solid fa-minus"></i></button>
              </div>
            </td>
            <td class="px-6 py-4 text-center">
              <div class="flex items-center justify-center gap-1.5">
                <input type="text" id="pass-${u.mobile}" placeholder="New Pass" oninput="storeTempInput('pass-${u.mobile}', this.value)" value="${tempInputs['pass-' + u.mobile] || ''}" class="w-24 bg-slate-950 border border-slate-800 rounded px-2 py-1 text-xs text-white text-center focus:outline-none focus:border-yellow-500">
                <button onclick="inlineChangePassword('${u.mobile}')" class="bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 font-bold text-xs p-1.5 rounded border border-blue-500/10 transition-colors" title="Change Password"><i class="fa-solid fa-check"></i></button>
              </div>
            </td>
            <td class="px-6 py-4 text-center">
              <div class="flex items-center justify-center gap-2">
                ${statusActionBtn}
                ${demoToggleBtn}
              </div>
            </td>
          </tr>
        `;
      });
    }

    function filterUsers() {
      updateUsersTable();
    }


    // Custom Toast Notification System
    function showToast(message) {
      let container = document.getElementById('custom-toast-container');
      if (!container) {
        container = document.createElement('div');
        container.id = 'custom-toast-container';
        container.style.position = 'fixed';
        container.style.bottom = '20px';
        container.style.right = '20px';
        container.style.zIndex = '99999';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '10px';
        document.body.appendChild(container);
      }
      
      const toast = document.createElement('div');
      toast.style.background = '#0f172a';
      toast.style.color = '#e2e8f0';
      toast.style.border = '1px solid #eab308';
      toast.style.borderRadius = '12px';
      toast.style.padding = '12px 24px';
      toast.style.fontFamily = 'sans-serif';
      toast.style.fontSize = '14px';
      toast.style.fontWeight = 'bold';
      toast.style.boxShadow = '0 10px 15px -3px rgba(0,0,0,0.3)';
      toast.style.display = 'flex';
      toast.style.alignItems = 'center';
      toast.style.gap = '8px';
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(20px)';
      toast.style.transition = 'all 0.3s ease';
      
      toast.innerHTML = `<i class="fa-solid fa-circle-check text-yellow-500"></i> <span>${message}</span>`;
      container.appendChild(toast);
      
      setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
      }, 50);
      
      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';
        setTimeout(() => {
          toast.remove();
        }, 300);
      }, 3000);
    }
    

    // Populate Settings UI
    function updateSettingsUI() {
      const upi = settingsData.deposit_settings?.upi || {};
      const usdt = settingsData.deposit_settings?.usdt || {};
      const bkash = settingsData.deposit_settings?.bkash || {};
      const nagad = settingsData.deposit_settings?.nagad || {};

      document.getElementById('setting-upi-id').value = upi.upi_id || '';
      document.getElementById('setting-upi-qr').value = upi.qr_url || '';
      document.getElementById('setting-usdt-address').value = usdt.usdt_address || '';
      document.getElementById('setting-usdt-rate').value = settingsData.system_settings?.usdt_rate || '90';
      document.getElementById('setting-bkash-wallet').value = bkash.wallet_no || '';
      document.getElementById('setting-nagad-wallet').value = nagad.wallet_no || '';
      document.getElementById('setting-gateway-base-url').value = settingsData.system_settings?.gateway_base_url || '';
      document.getElementById('setting-gateway-app-id').value = settingsData.system_settings?.gateway_app_id || '';
      document.getElementById('setting-gateway-secret-key').value = settingsData.system_settings?.gateway_secret_key || '';
    }

    function updatePlatformSettingsUI() {
      const system = settingsData.system_settings || {};
      document.getElementById('setting-signup-balance').value = system.default_signup_balance || '0';
      document.getElementById('setting-min-deposit-withdraw').value = system.min_deposit_for_withdraw || '250';
      document.getElementById('setting-maintenance').checked = system.maintenance === true || system.maintenance === 'true';
    }

    // Action Popups
    let activeAction = null;
    function openActionModal(title, desc, confirmCallback, showInput = false, inputPlaceholder = '', inputType = 'text') {
      document.getElementById('modalTitle').innerText = title;
      document.getElementById('modalDesc').innerText = desc;
      
      const inputContainer = document.getElementById('modalInputContainer');
      if (showInput) {
        inputContainer.innerHTML = `<input type="${inputType}" id="modalActionInput" placeholder="${inputPlaceholder}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:outline-none focus:border-yellow-500 transition-colors">`;
        inputContainer.classList.remove('hidden');
      } else {
        inputContainer.classList.add('hidden');
        inputContainer.innerHTML = '';
      }

      document.getElementById('modalConfirmBtn').onclick = () => {
        let val = null;
        if (showInput) {
          val = document.getElementById('modalActionInput').value.trim();
        }
        confirmCallback(val);
        closeActionModal();
      };
      
      document.getElementById('actionModal').classList.remove('hidden');
    }

    function closeActionModal() {
      document.getElementById('actionModal').classList.add('hidden');
    }

    // 1. APPROVE DEPOSIT
    function approveDeposit(id, mobile, amount) {
      db.ref(`users/${mobile}`).once('value', snap => {
        const user = snap.val();
        if (!user) return;

        const currentBalance = parseFloat(user.motta || 0);
        const currentTotalDeposit = parseFloat(user.total_deposit || 0);
        
        const newBalance = currentBalance + parseFloat(amount);
        const newTotalDeposit = currentTotalDeposit + parseFloat(amount);

        db.ref(`users/${mobile}`).update({
          motta: newBalance,
          total_deposit: newTotalDeposit
        }).then(() => {
          db.ref(`deposits/${id}`).update({
            status: 'success'
          });
        });
      });
    }

    // 2. REJECT DEPOSIT
    function rejectDeposit(id) {
      db.ref(`deposits/${id}`).update({
        status: 'failed'
      });
    }

    // 3. APPROVE WITHDRAWAL
    function approveWithdrawal(id) {
      db.ref(`withdrawals/${id}`).update({
        status: 'approved'
      });
    }

    // 4. REJECT WITHDRAWAL (REFUND BALANCE)
    function rejectWithdrawal(id, mobile, amount) {
      db.ref(`users/${mobile}`).once('value', snap => {
        const user = snap.val();
        if (!user) return;

        const newBalance = parseFloat(user.motta || 0) + parseFloat(amount);
        
        db.ref(`users/${mobile}`).update({
          motta: newBalance
        }).then(() => {
          db.ref(`withdrawals/${id}`).update({
            status: 'failed'
          });
        });
      });
    }

    // 5. ADJUST WALLET BALANCE (INLINE)
    function inlineAdjustBalance(mobile, action) {
      const inputEl = document.getElementById(`amt-${mobile}`);
      const val = parseFloat(inputEl.value.trim());
      if (isNaN(val) || val <= 0) return;
      
      db.ref(`users/${mobile}`).once('value', snap => {
        const user = snap.val();
        if (!user) return;
        
        let newBalance = parseFloat(user.motta || 0);
        if (action === 'add') {
          newBalance += val;
        } else {
          newBalance = Math.max(0, newBalance - val);
        }
        
        // Clear input value
        tempInputs[`amt-${mobile}`] = '';
        inputEl.value = '';
        
        db.ref(`users/${mobile}`).update({
          motta: newBalance
        });
      });
    }

    // 6. RESET PASSWORD (INLINE)
    function inlineChangePassword(mobile) {
      const inputEl = document.getElementById(`pass-${mobile}`);
      const pass = inputEl.value.trim();
      if (!pass || pass.length < 4) return;
      
      const md5Hash = tempCalculateMd5(pass);
      
      // Clear input value
      tempInputs[`pass-${mobile}`] = '';
      inputEl.value = '';
      
      db.ref(`users/${mobile}`).update({
        password: md5Hash
      });
    }

    // MD5 Javascript function wrapper
    function tempCalculateMd5(str) {
      let hex_chr = '0123456789abcdef';
      function rrot(x, n) { return (x >>> n) | (x << (32 - n)); }
      
      function hex_md5(s) { return binl2hex(core_md5(str2binl(s), s.length * 8)); }
      function core_md5(x, len) {
        x[len >> 5] |= 0x80 << ((len) % 32);
        x[(((len + 64) >>> 9) << 4) + 14] = len;
        let a =  1732584193, b = -271733879, c = -1732584194, d =  271733878;
        for(let i = 0; i < x.length; i += 16) {
          let olda = a, oldb = b, oldc = c, oldd = d;
          a = md5_ff(a, b, c, d, x[i+ 0], 7 , -680876936); d = md5_ff(d, a, b, c, x[i+ 1], 12, -389564586); c = md5_ff(c, d, a, b, x[i+ 2], 17,  606105819); b = md5_ff(b, c, d, a, x[i+ 3], 22, -1044525330);
          a = md5_ff(a, b, c, d, x[i+ 4], 7 , -176418897); d = md5_ff(d, a, b, c, x[i+ 5], 12,  1200080426); c = md5_ff(c, d, a, b, x[i+ 6], 17, -1473231341); b = md5_ff(b, c, d, a, x[i+ 7], 22, -45705983);
          a = md5_ff(a, b, c, d, x[i+ 8], 7 ,  1770035416); d = md5_ff(d, a, b, c, x[i+ 9], 12, -1958414417); c = md5_ff(c, d, a, b, x[i+10], 17, -42063); b = md5_ff(b, c, d, a, x[i+11], 22, -1990404162);
          a = md5_ff(a, b, c, d, x[i+12], 7 ,  1804603682); d = md5_ff(d, a, b, c, x[i+13], 12, -40341101); c = md5_ff(c, d, a, b, x[i+14], 17, -1502002290); b = md5_ff(b, c, d, a, x[i+15], 22,  1236535329);
          a = md5_gg(a, b, c, d, x[i+ 1], 5 , -165796510); d = md5_gg(d, a, b, c, x[i+ 6], 9 , -1069501632); c = md5_gg(c, d, a, b, x[i+11], 14,  643717713); b = md5_gg(b, c, d, a, x[i+ 0], 20, -373897302);
          a = md5_gg(a, b, c, d, x[i+ 5], 5 , -701558691); d = md5_gg(d, a, b, c, x[i+10], 9 ,  38016083); c = md5_gg(c, d, a, b, x[i+15], 14, -660478335); b = md5_gg(b, c, d, a, x[i+ 4], 20, -405537848);
          a = md5_gg(a, b, c, d, x[i+ 9], 5 ,  568446438); d = md5_gg(d, a, b, c, x[i+14], 9 , -1019803690); c = md5_gg(c, d, a, b, x[i+ 3], 14, -187363961); b = md5_gg(b, c, d, a, x[i+ 8], 20,  1163531501);
          a = md5_gg(a, b, c, d, x[i+13], 5 , -1444681467); d = md5_gg(d, a, b, c, x[i+ 2], 9 , -51403784); c = md5_gg(c, d, a, b, x[i+ 7], 14,  1735328473); b = md5_gg(b, c, d, a, x[i+12], 20, -1926607734);
          a = md5_hh(a, b, c, d, x[i+ 5], 4 , -378558); d = md5_hh(d, a, b, c, x[i+ 8], 11, -2022574463); c = md5_hh(c, d, a, b, x[i+11], 16,  1839030562); b = md5_hh(b, c, d, a, x[i+14], 23, -35309556);
          a = md5_hh(a, b, c, d, x[i+ 1], 4 , -1530992060); d = md5_hh(d, a, b, c, x[i+ 4], 11,  1272893353); c = md5_hh(c, d, a, b, x[i+ 7], 16, -155497632); b = md5_hh(b, c, d, a, x[i+10], 23, -1094730640);
          a = md5_hh(a, b, c, d, x[i+13], 4 , -105573132); d = md5_hh(d, a, b, c, x[i+ 0], 11,  381183769); c = md5_hh(c, d, a, b, x[i+ 3], 16, -933547018); b = md5_hh(b, c, d, a, x[i+ 6], 23,  567782071);
          a = md5_hh(a, b, c, d, x[i+ 9], 4 , -29337475); d = md5_hh(d, a, b, c, x[i+12], 11, -166669390); c = md5_hh(c, d, a, b, x[i+15], 16,  1623101913); b = md5_hh(b, c, d, a, x[i+ 2], 23, -503841030);
          a = md5_ii(a, b, c, d, x[i+ 0], 6 , -3552516); d = md5_ii(d, a, b, c, x[i+ 7], 21, -198630844); c = md5_ii(c, d, a, b, x[i+14], 6 ,  1126891415); b = md5_ii(b, c, d, a, x[i+ 5], 21, -82128697);
          a = md5_ii(a, b, c, d, x[i+12], 6 , -1410355670); d = md5_ii(d, a, b, c, x[i+ 3], 21,  841777071); c = md5_ii(c, d, a, b, x[i+10], 6 , -1747833779); b = md5_ii(b, c, d, a, x[i+ 1], 21, -6565567);
          a = md5_ii(a, b, c, d, x[i+ 8], 6 , -1869039316); d = md5_ii(d, a, b, c, x[i+15], 21,  1879020168); c = md5_ii(c, d, a, b, x[i+ 6], 6 , -980180755); b = md5_ii(b, c, d, a, x[i+13], 21, -22046336);
          a = md5_ii(a, b, c, d, x[i+ 4], 6 ,  1126131439); d = md5_ii(d, a, b, c, x[i+11], 21, -1128103328); c = md5_ii(c, d, a, b, x[i+ 2], 6 , -343485551); b = md5_ii(b, c, d, a, x[i+ 9], 21,  350595208);
          a = safe_add(a, olda); b = safe_add(b, oldb); c = safe_add(c, oldc); d = safe_add(d, oldd);
        }
        return [a, b, c, d];
      }
      function md5_cmn(q, a, b, x, s, t) { return safe_add(rrot(safe_add(safe_add(a, q), safe_add(x, t)), s), b); }
      function md5_ff(a, b, c, d, x, s, t) { return md5_cmn((b & c) | ((~b) & d), a, b, x, s, t); }
      function md5_gg(a, b, c, d, x, s, t) { return md5_cmn((b & d) | (c & (~d)), a, b, x, s, t); }
      function md5_hh(a, b, c, d, x, s, t) { return md5_cmn(b ^ c ^ d, a, b, x, s, t); }
      function md5_ii(a, b, c, d, x, s, t) { return md5_cmn(c ^ (b | (~d)), a, b, x, s, t); }
      function safe_add(x, y) {
        let lsw = (x & 0xFFFF) + (y & 0xFFFF);
        let msw = (x >> 16) + (y >> 16) + (lsw >> 16);
        return (msw << 16) | (lsw & 0xFFFF);
      }
      function str2binl(str) {
        let bin = [];
        let mask = (1 << 8) - 1;
        for(let i = 0; i < str.length * 8; i += 8) {
          bin[i>>5] |= (str.charCodeAt(i / 8) & mask) << (i % 32);
        }
        return bin;
      }
      function binl2hex(binarray) {
        let hex_tab = hex_chr;
        let str = '';
        for(let i = 0; i < binarray.length * 4; i++) {
          str += hex_tab.charAt((binarray[i>>2] >> ((i%4)*8+4)) & 0xF) + hex_tab.charAt((binarray[i>>2] >> ((i%4)*8)) & 0xF);
        }
        return str;
      }

      return hex_md5(str);
    }

    // 7. TOGGLE USER STATUS (BAN/UNBAN)
    function toggleUserStatus(mobile, statusVal) {
      db.ref(`users/${mobile}`).update({
        status: statusVal
      });
    }

    // 8. TOGGLE DEMO STATUS
    function toggleUserDemo(mobile, isDemoVal) {
      db.ref(`users/${mobile}`).update({
        is_demo: isDemoVal
      });
    }

    // 9. SAVE PAYMENT SETTINGS
    function savePaymentSettings() {
      const upiId = document.getElementById('setting-upi-id').value.trim();
      const upiQr = document.getElementById('setting-upi-qr').value.trim();
      const usdtAddress = document.getElementById('setting-usdt-address').value.trim();
      const usdtRate = parseFloat(document.getElementById('setting-usdt-rate').value.trim());
      const bkashWallet = document.getElementById('setting-bkash-wallet').value.trim();
      const nagadWallet = document.getElementById('setting-nagad-wallet').value.trim();
      const gatewayBaseUrl = document.getElementById('setting-gateway-base-url').value.trim();
      const gatewayAppId = document.getElementById('setting-gateway-app-id').value.trim();
      const gatewaySecretKey = document.getElementById('setting-gateway-secret-key').value.trim();

      if (isNaN(usdtRate) || usdtRate <= 0) return;

      const updates = {};
      updates['deposit_settings/upi/upi_id'] = upiId;
      updates['deposit_settings/upi/qr_url'] = upiQr;
      updates['deposit_settings/usdt/usdt_address'] = usdtAddress;
      updates['system_settings/usdt_rate'] = usdtRate;
      updates['deposit_settings/bkash/wallet_no'] = bkashWallet;
      updates['deposit_settings/nagad/wallet_no'] = nagadWallet;
      updates['system_settings/gateway_base_url'] = gatewayBaseUrl;
      updates['system_settings/gateway_app_id'] = gatewayAppId;
      updates['system_settings/gateway_secret_key'] = gatewaySecretKey;

      db.ref().update(updates).then(() => {
        showToast('Changed successfully!');
      });
    }

    // 10. SAVE PLATFORM SETTINGS
    function savePlatformSettings() {
      const signupBal = parseFloat(document.getElementById('setting-signup-balance').value.trim());
      const minDepositWithdraw = parseFloat(document.getElementById('setting-min-deposit-withdraw').value.trim());
      const newAdminPassword = document.getElementById('setting-admin-password').value.trim();
      const maintenanceVal = document.getElementById('setting-maintenance').checked;

      if (isNaN(signupBal) || signupBal < 0) return;
      if (isNaN(minDepositWithdraw) || minDepositWithdraw < 0) return;

      const updates = {};
      updates['system_settings/default_signup_balance'] = signupBal;
      updates['system_settings/min_deposit_for_withdraw'] = minDepositWithdraw;
      updates['system_settings/maintenance'] = maintenanceVal;
      
      if (newAdminPassword.length > 0) {
        if (newAdminPassword.length < 4) return;
        updates['system_settings/admin_password'] = newAdminPassword;
      }

      db.ref().update(updates).then(() => {
        if (newAdminPassword.length > 0) {
          window.location.href = 'admin_panel.php?logout=1';
        }
      });
    }

    // 11. RECENT PENDING LIST (Dashboard Widget)
    function updateRecentPendingList() {
      const container = document.getElementById('recent-pending-container');
      container.innerHTML = '';

      let items = [];

      Object.entries(depositsData).forEach(([id, d]) => {
        if (d.status === 'pending') {
          items.push({
            type: 'deposit',
            id,
            mobile: d.userId,
            amount: parseFloat(d.amount),
            date: d.createdAt,
            badge: '<span class="bg-yellow-500/10 text-yellow-500 text-[10px] px-2 py-0.5 rounded font-black tracking-wider uppercase">Deposit</span>'
          });
        }
      });

      Object.entries(withdrawalsData).forEach(([id, w]) => {
        if (w.status === 'pending') {
          items.push({
            type: 'withdrawal',
            id,
            mobile: w.userId,
            amount: parseFloat(w.amount),
            date: w.createdAt,
            badge: '<span class="bg-blue-500/10 text-blue-400 text-[10px] px-2 py-0.5 rounded font-black tracking-wider uppercase">Withdrawal</span>'
          });
        }
      });

      items.sort((a, b) => new Date(b.date) - new Date(a.date));

      if (items.length === 0) {
        container.innerHTML = `<p class="text-slate-500 text-sm py-8 text-center"><i class="fa-solid fa-circle-check text-emerald-400 text-lg mr-2"></i> All caught up! No pending transactions.</p>`;
        return;
      }

      items.slice(0, 10).forEach(item => {
        const actionLabel = item.type === 'deposit' 
          ? `<button onclick="switchTab('tab-deposits')" class="text-yellow-500 hover:text-yellow-400 font-bold text-xs">Process <i class="fa-solid fa-angle-right"></i></button>`
          : `<button onclick="switchTab('tab-withdrawals')" class="text-blue-400 hover:text-blue-300 font-bold text-xs">Process <i class="fa-solid fa-angle-right"></i></button>`;

        container.innerHTML += `
          <div class="flex items-center justify-between py-3">
            <div class="flex items-center gap-3">
              ${item.badge}
              <div>
                <h4 class="font-bold text-white text-sm">${item.mobile}</h4>
                <p class="text-xs text-slate-500">${item.date || 'N/A'}</p>
              </div>
            </div>
            </div>
          </div>
        `;
      });
    }

    // =====================================================
    // GAME CONTROLLER LOGIC & SYNC
    // =====================================================
    function getPeriodDetails(gameType, typeId) {
      const now = new Date();
      const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
      const istTime = new Date(utc + (3600000 * 5.5)); // IST timezone (UTC+5.30)
      
      const hours = istTime.getHours();
      const minutes = istTime.getMinutes();
      const seconds = istTime.getSeconds();
      const totalSeconds = hours * 3600 + minutes * 60 + seconds;
      
      let intervalSec = 60;
      let typeChar = '1';
      
      if (gameType === 'wingo') {
        if (typeId === 1) { intervalSec = 60; typeChar = '1'; }
        else if (typeId === 2) { intervalSec = 180; typeChar = '2'; }
        else if (typeId === 3) { intervalSec = 300; typeChar = '3'; }
        else if (typeId === 5) { intervalSec = 30; typeChar = '5'; }
      } else if (gameType === 'trx') {
        if (typeId === 13) { intervalSec = 60; typeChar = '1'; }
        else if (typeId === 14) { intervalSec = 180; typeChar = '2'; }
        else if (typeId === 15) { intervalSec = 300; typeChar = '3'; }
        else if (typeId === 16) { intervalSec = 600; typeChar = '4'; }
      } else if (gameType === 'k3') {
        if (typeId === 9) { intervalSec = 60; typeChar = '9'; }
        else if (typeId === 10) { intervalSec = 180; typeChar = '0'; }
        else if (typeId === 11) { intervalSec = 300; typeChar = 'a'; }
        else if (typeId === 12) { intervalSec = 600; typeChar = 'b'; }
      } else if (gameType === 'd5') {
        if (typeId === 5) { intervalSec = 60; typeChar = '5'; }
        else if (typeId === 6) { intervalSec = 180; typeChar = '6'; }
        else if (typeId === 7) { intervalSec = 300; typeChar = '7'; }
        else if (typeId === 8) { intervalSec = 600; typeChar = '8'; }
      }
      
      const sequence = Math.floor(totalSeconds / intervalSec) + 1;
      const elapsedInPeriod = totalSeconds % intervalSec;
      const remaining = intervalSec - elapsedInPeriod;
      
      const year = istTime.getFullYear();
      const month = String(istTime.getMonth() + 1).padStart(2, '0');
      const date = String(istTime.getDate()).padStart(2, '0');
      const dateStr = `${year}${month}${date}`;
      const periodId = `${dateStr}1000${typeChar}${String(sequence).padStart(4, '0')}`;
      
      return { periodId, remaining };
    }

    function selectControllerGame(gameType, typeId, title) {
      gcActiveGameType = gameType;
      gcActiveGameTypeId = typeId;
      gcActiveGameTitle = title;
      
      document.getElementById('gc-active-title').innerText = title;
      
      // Update sidebar styling for selectors
      const selectorContainer = document.getElementById('game-controller-selector');
      if (selectorContainer) {
        selectorContainer.querySelectorAll('button').forEach(btn => {
          if (btn.id === `btn-gc-${gameType}-${typeId}`) {
            btn.className = "w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-amber-500 bg-opacity-15 border border-amber-500/30 transition-colors flex justify-between items-center";
          } else {
            btn.className = "w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold text-slate-400 bg-slate-950/40 hover:bg-slate-800 border border-slate-900 hover:border-slate-800 transition-colors flex justify-between items-center";
          }
        });
      }
      
      // Toggle pads
      document.getElementById('gc-pad-wingo').classList.add('hidden');
      document.getElementById('gc-pad-k3').classList.add('hidden');
      document.getElementById('gc-pad-d5').classList.add('hidden');
      
      if (gameType === 'k3') {
        document.getElementById('gc-pad-k3').classList.remove('hidden');
      } else if (gameType === 'd5') {
        document.getElementById('gc-pad-d5').classList.remove('hidden');
      } else {
        document.getElementById('gc-pad-wingo').classList.remove('hidden');
      }
      
      // Sync settings & overrides
      syncGcAutoProfit();
      updateOverrideStatusUI();
      
      // Force immediate check
      tickController();
    }

    function syncGcOverrides() {
      db.ref('admin_overrides').on('value', snap => {
        gcActiveOverrides = snap.val() || {};
        updateOverrideStatusUI();
      });
    }

    function syncGcAutoProfit() {
      db.ref('system_settings').on('value', snap => {
        const system = snap.val() || {};
        const key = `auto_profit_${gcActiveGameType}`;
        const isAutoProfit = system[key] === true || system[key] === 'true' || system[key] === 1 || system[key] === '1';
        document.getElementById('gc-auto-profit-toggle').checked = isAutoProfit;
      });
    }

    let gcBetsRef = null;
    function syncGcBets(gameType, typeId, periodId) {
      if (gcBetsRef) {
        gcBetsRef.off();
      }
      const typeKey = `${gameType}_t${typeId}`;
      gcBetsRef = db.ref(`game_bets/${typeKey}/${periodId}`);
      gcBetsRef.on('value', snap => {
        const bets = snap.val() || {};
        updateGcBetsUI(bets);
      });
    }

    function updateGcBetsUI(bets) {
      const tbody = document.getElementById('gc-bets-table-body');
      tbody.innerHTML = '';
      
      const list = Object.values(bets);
      if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No bets placed on this period yet.</td></tr>`;
        document.getElementById('gc-bets-total').innerText = 'Total Bet: ₹0.00';
        return;
      }
      
      let totalAmount = 0;
      list.forEach(b => {
        totalAmount += parseFloat(b.totalAmount || 0);
        
        let selectLabel = b.selectType;
        if (b.selectType == 10) selectLabel = '<span class="text-red-500 font-bold">Red</span>';
        else if (b.selectType == 11) selectLabel = '<span class="text-emerald-500 font-bold">Green</span>';
        else if (b.selectType == 12) selectLabel = '<span class="text-purple-500 font-bold">Violet</span>';
        else if (b.selectType == 13 || b.selectType === 'big') selectLabel = '<span class="text-amber-500 font-bold">Big</span>';
        else if (b.selectType == 14 || b.selectType === 'small') selectLabel = '<span class="text-blue-500 font-bold">Small</span>';
        
        tbody.innerHTML += `
          <tr>
            <td class="px-4 py-3 font-semibold text-slate-300 select-all">${b.userId}</td>
            <td class="px-4 py-3 text-center">${selectLabel}</td>
            <td class="px-4 py-3 text-center text-slate-400">${b.betCount}x</td>
            <td class="px-4 py-3 text-right font-bold text-white">₹${parseFloat(b.totalAmount || 0).toFixed(2)}</td>
          </tr>
        `;
      });
      
      document.getElementById('gc-bets-total').innerText = `Total Bet: ₹${totalAmount.toFixed(2)}`;
    }

    function updateOverrideStatusUI() {
      const typeKey = `${gcActiveGameType}_t${gcActiveGameTypeId}`;
      const ov = gcActiveOverrides[typeKey];
      const statusEl = document.getElementById('active-override-status');
      
      if (ov && ov.active === true) {
        let val = ov.number;
        if (gcActiveGameType === 'k3') {
          val = `Dice: ${ov.d1}, ${ov.d2}, ${ov.d3}`;
        } else if (gcActiveGameType === 'd5') {
          val = `Sequence: ${ov.premium}`;
        } else {
          if (val === 'red') val = 'RED';
          else if (val === 'green') val = 'GREEN';
          else if (val === 'violet') val = 'VIOLET';
          else if (val === 'big') val = 'BIG';
          else if (val === 'small') val = 'SMALL';
        }
        document.getElementById('override-label-status').innerText = val;
        statusEl.classList.remove('hidden');
      } else {
        statusEl.classList.add('hidden');
      }
    }

    function setResultOverride(value) {
      const typeKey = `${gcActiveGameType}_t${gcActiveGameTypeId}`;
      db.ref(`admin_overrides/${typeKey}`).set({
        number: value,
        active: true
      });
    }

    function setK3ResultOverride() {
      const d1 = parseInt(document.getElementById('k3-override-d1').value);
      const d2 = parseInt(document.getElementById('k3-override-d2').value);
      const d3 = parseInt(document.getElementById('k3-override-d3').value);
      const typeKey = `k3_t${gcActiveGameTypeId}`;
      db.ref(`admin_overrides/${typeKey}`).set({
        d1, d2, d3,
        active: true
      });
    }

    function setD5ResultOverride() {
      const input = document.getElementById('d5-override-input').value.trim();
      if (input.length !== 5 || isNaN(parseInt(input))) {
        alert('Please enter a valid 5-digit number!');
        return;
      }
      const typeKey = `d5_t${gcActiveGameTypeId}`;
      db.ref(`admin_overrides/${typeKey}`).set({
        premium: input,
        active: true
      });
    }

    // Populate Gift Codes Table
    function updateGiftCodesTable() {
      const tbody = document.getElementById('gift-codes-table-body');
      if (!tbody) return;
      tbody.innerHTML = '';

      const list = Object.entries(giftCodesData).map(([code, g]) => ({ code, ...g }));
      
      if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-slate-500">No gift codes found. Generate one above!</td></tr>`;
        return;
      }

      list.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));

      list.forEach(g => {
        const statusBadge = (g.status == 1) 
          ? `<span class="bg-emerald-500/10 text-emerald-400 text-[10px] px-2 py-0.5 rounded font-semibold uppercase tracking-wider text-center block">Active</span>` 
          : `<span class="bg-red-500/10 text-red-400 text-[10px] px-2 py-0.5 rounded font-semibold uppercase tracking-wider text-center block">Disabled</span>`;

        const actionBtn = (g.status == 1)
          ? `<button onclick="toggleGiftCodeStatus('${g.code}', 0)" class="bg-red-500 hover:bg-red-600 text-white font-bold text-xs px-3 py-1 rounded-lg transition-colors">Disable</button>`
          : `<button onclick="toggleGiftCodeStatus('${g.code}', 1)" class="bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs px-3 py-1 rounded-lg transition-colors">Enable</button>`;

        tbody.innerHTML += `
          <tr class="hover:bg-slate-900/40 transition-colors">
            <td class="px-6 py-4 font-bold text-white select-all">${g.code}</td>
            <td class="px-6 py-4 font-semibold text-white">৳${parseFloat(g.amount || 0).toFixed(2)}</td>
            <td class="px-6 py-4 text-slate-400">৳${parseFloat(g.turnover_req || 0).toFixed(2)}</td>
            <td class="px-6 py-4 text-amber-400 font-semibold">৳${parseFloat(g.min_deposit_req || 0).toFixed(2)}</td>
            <td class="px-6 py-4 text-slate-400">${g.redeemed_count || 0} / ${g.max_users || 0}</td>
            <td class="px-6 py-4">${statusBadge}</td>
            <td class="px-6 py-4 text-center space-x-2">
              ${actionBtn}
              <button onclick="deleteGiftCode('&lt;span style="color:inherit"&gt;${g.code}&lt;/span&gt;')" class="bg-slate-800 hover:bg-red-500 text-slate-400 hover:text-white font-bold text-xs px-3 py-1 rounded-lg transition-all"><i class="fa-solid fa-trash"></i></button>
            </td>
          </tr>
        `;
      });
    }

    // Toggle status
    function toggleGiftCodeStatus(code, newStatus) {
      db.ref('gift_codes/' + code).update({ status: newStatus })
        .then(() => showToast('Gift code status updated successfully!'))
        .catch(err => alert('Failed: ' + err.message));
    }

    // Delete gift code
    function deleteGiftCode(code) {
      // Decode HTML entities if code is passed wrapped in span or similar
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = code;
      const cleanCode = tempDiv.textContent || tempDiv.innerText || code;
      
      if (confirm('Are you sure you want to delete gift code: ' + cleanCode + '?')) {
        db.ref('gift_codes/' + cleanCode).remove()
          .then(() => showToast('Gift code deleted successfully!'))
          .catch(err => alert('Failed: ' + err.message));
      }
    }

    // Generate random code helper
    function generateRandomGiftCode() {
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      let result = 'GIFT-';
      for (let i = 0; i < 8; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
      }
      document.getElementById('giftCodeInput').value = result;
    }

    // Create New Gift Code
    function createGiftCode() {
      const code = document.getElementById('giftCodeInput').value.trim().toUpperCase();
      const amount = parseFloat(document.getElementById('giftAmountInput').value);
      const turnover_req = parseFloat(document.getElementById('giftTurnoverInput').value) || 0;
      const min_deposit_req = parseFloat(document.getElementById('giftMinDepositInput').value) || 0;
      const maxUsers = parseInt(document.getElementById('giftMaxUsersInput').value);

      if (!code) return alert('Please enter or generate a Gift Code!');
      if (isNaN(amount) || amount <= 0) return alert('Please enter a valid reward amount!');
      if (isNaN(maxUsers) || maxUsers <= 0) return alert('Please enter a valid usage limit!');

      const dateStr = new Date().toISOString().replace('T', ' ').substring(0, 19);

      const newGift = {
        code: code,
        amount: amount,
        turnover_req: turnover_req,
        min_deposit_req: min_deposit_req,
        max_users: maxUsers,
        redeemed_count: 0,
        status: 1,
        created_at: dateStr
      };

      db.ref('gift_codes/' + code).set(newGift)
        .then(() => {
          showToast('Gift code ' + code + ' generated successfully!');
          document.getElementById('giftCodeInput').value = '';
          document.getElementById('giftAmountInput').value = '';
          document.getElementById('giftTurnoverInput').value = '';
          document.getElementById('giftMinDepositInput').value = '';
          document.getElementById('giftMaxUsersInput').value = '';
        })
        .catch(err => alert('Failed to create gift code: ' + err.message));
    }

    function cancelActiveOverride() {
      const typeKey = `${gcActiveGameType}_t${gcActiveGameTypeId}`;
      db.ref(`admin_overrides/${typeKey}`).update({
        active: false
      });
    }

    function toggleGcAutoProfit() {
      const active = document.getElementById('gc-auto-profit-toggle').checked;
      db.ref(`system_settings`).update({
        [`auto_profit_${gcActiveGameType}`]: active
      });
    }

    function tickController() {
      const gamesList = [
        { type: 'wingo', id: 1, timerId: 'timer-gc-wingo-1' },
        { type: 'wingo', id: 2, timerId: 'timer-gc-wingo-2' },
        { type: 'wingo', id: 3, timerId: 'timer-gc-wingo-3' },
        { type: 'wingo', id: 5, timerId: 'timer-gc-wingo-5' },
        { type: 'trx', id: 13, timerId: 'timer-gc-trx-13' },
        { type: 'trx', id: 14, timerId: 'timer-gc-trx-14' },
        { type: 'k3', id: 9, timerId: 'timer-gc-k3-9' },
        { type: 'd5', id: 5, timerId: 'timer-gc-d5-5' }
      ];
      
      gamesList.forEach(g => {
        const details = getPeriodDetails(g.type, g.id);
        const timerEl = document.getElementById(g.timerId);
        if (timerEl) {
          timerEl.innerText = `${details.remaining}s`;
          if (details.remaining <= 10) {
            timerEl.className = "text-[10px] text-red-500 font-bold animate-pulse";
          } else {
            timerEl.className = "text-[10px] text-slate-500 font-semibold";
          }
        }
        
        if (g.type === gcActiveGameType && g.id === gcActiveGameTypeId) {
          document.getElementById('gc-active-period').innerText = details.periodId;
          document.getElementById('gc-active-timer').innerText = `${details.remaining}s`;
          
          if (details.remaining <= 10) {
            document.getElementById('gc-active-timer').className = "text-2xl font-black text-red-500 mt-1 block animate-pulse";
          } else {
            document.getElementById('gc-active-timer').className = "text-2xl font-black text-amber-500 mt-1 block";
          }
          
          if (details.periodId !== gcActivePeriodId) {
            gcActivePeriodId = details.periodId;
            syncGcBets(gcActiveGameType, gcActiveGameTypeId, gcActivePeriodId);
          }
        }
      });
    }
  </script>
  <?php endif; ?>
</body>
</html>

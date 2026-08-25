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
  <style>
    body {
      background-color: #0f172a;
      color: #e2e8f0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .custom-scrollbar::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
      background: #1e293b;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: #475569;
      border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
      background: #64748b;
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
        <p class="text-xs text-slate-500 select-all" id="connectedDbLabel">Loading Database Configuration...</p>
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
    <aside class="w-64 bg-slate-950 border-r border-slate-900 flex flex-col p-4 shrink-0">
      <div class="space-y-1 flex-1">
        <button onclick="switchTab('tab-overview')" id="btn-tab-overview" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold text-yellow-500 bg-yellow-500 bg-opacity-5 border border-yellow-500 border-opacity-10 transition-all">
          <span class="flex items-center gap-3">
            <i class="fa-solid fa-chart-line text-lg"></i> Overview
          </span>
          <i class="fa-solid fa-angle-right text-xs"></i>
        </button>

        <button onclick="switchTab('tab-deposits')" id="btn-tab-deposits" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold text-slate-400 hover:text-slate-200 hover:bg-slate-900 transition-all">
          <span class="flex items-center gap-3">
            <i class="fa-solid fa-wallet text-lg"></i> Deposit Requests
          </span>
          <span id="badge-deposits-count" class="bg-red-500 text-white font-black text-[10px] px-2 py-0.5 rounded-full hidden">0</span>
        </button>

        <button onclick="switchTab('tab-withdrawals')" id="btn-tab-withdrawals" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold text-slate-400 hover:text-slate-200 hover:bg-slate-900 transition-all">
          <span class="flex items-center gap-3">
            <i class="fa-solid fa-money-bill-transfer text-lg"></i> Withdrawals
          </span>
          <span id="badge-withdrawals-count" class="bg-red-500 text-white font-black text-[10px] px-2 py-0.5 rounded-full hidden">0</span>
        </button>

        <button onclick="switchTab('tab-users')" id="btn-tab-users" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold text-slate-400 hover:text-slate-200 hover:bg-slate-900 transition-all">
          <span class="flex items-center gap-3">
            <i class="fa-solid fa-users text-lg"></i> Users & Players
          </span>
          <i class="fa-solid fa-angle-right text-xs"></i>
        </button>

        <button onclick="switchTab('tab-settings')" id="btn-tab-settings" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold text-slate-400 hover:text-slate-200 hover:bg-slate-900 transition-all">
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
            <input type="text" id="userSearchInput" oninput="filterUsers()" placeholder="Search by Mobile..." class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 text-sm transition-colors w-64">
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
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Change Admin Panel Password</label>
                <input type="password" id="setting-admin-password" placeholder="New dashboard login password" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors">
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

    window.addEventListener('DOMContentLoaded', () => {
      if (!fbUrl) {
        alert('FIREBASE_URL environment variable is missing on the server! Cannot connect.');
        return;
      }
      initFirebase(fbUrl, fbSecret);
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
    }

    // UI Tab Navigation Switching
    function switchTab(tabId) {
      document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.add('hidden'));
      document.getElementById(tabId).classList.remove('hidden');

      const menuButtons = [
        { id: 'btn-tab-overview', tab: 'tab-overview', activeColor: 'text-yellow-500 bg-yellow-500 bg-opacity-5 border-yellow-500/10' },
        { id: 'btn-tab-deposits', tab: 'tab-deposits', activeColor: 'text-yellow-500 bg-yellow-500 bg-opacity-5 border-yellow-500/10' },
        { id: 'btn-tab-withdrawals', tab: 'tab-withdrawals', activeColor: 'text-yellow-500 bg-yellow-500 bg-opacity-5 border-yellow-500/10' },
        { id: 'btn-tab-users', tab: 'tab-users', activeColor: 'text-yellow-500 bg-yellow-500 bg-opacity-5 border-yellow-500/10' },
        { id: 'btn-tab-settings', tab: 'tab-settings', activeColor: 'text-yellow-500 bg-yellow-500 bg-opacity-5 border-yellow-500/10' }
      ];

      menuButtons.forEach(btn => {
        const el = document.getElementById(btn.id);
        if (btn.tab === tabId) {
          el.className = `w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold border ${btn.activeColor} transition-all`;
        } else {
          el.className = "w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold text-slate-400 hover:text-slate-200 hover:bg-slate-900 transition-all border border-transparent";
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
        } else if (d.status === 'success') {
          statusBadge = `<span class="bg-emerald-500/10 text-emerald-400 text-[10px] px-2 py-0.5 rounded font-semibold uppercase tracking-wider text-center block">Approved</span>`;
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

        tbody.innerHTML += `
          <tr class="hover:bg-slate-900/40 transition-colors">
            <td class="px-6 py-4 font-bold text-white select-all">${d.userId}</td>
            <td class="px-6 py-4 font-semibold text-white">₹${parseFloat(d.amount).toFixed(2)}</td>
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

        tbody.innerHTML += `
          <tr class="hover:bg-slate-900/40 transition-colors">
            <td class="px-6 py-4 font-bold text-white select-all">${w.userId}</td>
            <td class="px-6 py-4 font-semibold text-white">₹${parseFloat(w.amount).toFixed(2)}</td>
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

        tbody.innerHTML += `
          <tr class="hover:bg-slate-900/40 transition-colors">
            <td class="px-6 py-4 font-bold text-white select-all">${u.mobile}</td>
            <td class="px-6 py-4 font-black text-emerald-400">₹${parseFloat(u.motta || 0).toFixed(2)}</td>
            <td class="px-6 py-4 text-slate-400">₹${parseFloat(u.total_deposit || 0).toFixed(2)}</td>
            <td class="px-6 py-4 text-slate-400">₹${parseFloat(u.total_bet || 0).toFixed(2)}</td>
            <td class="px-6 py-4">${typeBadge}</td>
            <td class="px-6 py-4">${statusBadge}</td>
            <td class="px-6 py-4 text-center">
              <div class="flex items-center justify-center gap-1.5">
                <button onclick="promptEditBalance('${u.mobile}', 'add')" class="bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 font-bold text-xs p-1.5 rounded-lg border border-emerald-500/10 transition-colors" title="Add Balance"><i class="fa-solid fa-plus-circle"></i></button>
                <button onclick="promptEditBalance('${u.mobile}', 'deduct')" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 font-bold text-xs p-1.5 rounded-lg border border-red-500/10 transition-colors" title="Subtract Balance"><i class="fa-solid fa-minus-circle"></i></button>
              </div>
            </td>
            <td class="px-6 py-4 text-center">
              <button onclick="promptChangePassword('${u.mobile}')" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs px-2.5 py-1.5 border border-slate-700 rounded-lg transition-colors">Reset Pass</button>
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

    // Populate Settings UI
    function updateSettingsUI() {
      const upi = settingsData.deposit_settings?.upi || {};
      const usdt = settingsData.deposit_settings?.usdt || {};

      document.getElementById('setting-upi-id').value = upi.upi_id || '';
      document.getElementById('setting-upi-qr').value = upi.qr_url || '';
      document.getElementById('setting-usdt-address').value = usdt.usdt_address || '';
      document.getElementById('setting-usdt-rate').value = settingsData.system_settings?.usdt_rate || '90';
    }

    function updatePlatformSettingsUI() {
      const system = settingsData.system_settings || {};
      document.getElementById('setting-signup-balance').value = system.default_signup_balance || '0';
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

    // 5. ADJUST WALLET BALANCE
    function promptEditBalance(mobile, action) {
      openActionModal(
        'Adjust Wallet Balance',
        `Enter the amount you wish to ${action === 'add' ? 'add to' : 'deduct from'} ${mobile}:`,
        (amountVal) => {
          const val = parseFloat(amountVal);
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

            db.ref(`users/${mobile}`).update({
              motta: newBalance
            });
          });
        },
        true,
        'Amount in INR (e.g. 500)',
        'number'
      );
    }

    // 6. RESET PASSWORD
    function promptChangePassword(mobile) {
      openActionModal(
        'Reset Player Password',
        `Enter new plain text password for player ${mobile}:`,
        (newPassword) => {
          if (!newPassword || newPassword.length < 4) return;
          const md5Hash = tempCalculateMd5(newPassword);
          db.ref(`users/${mobile}`).update({
            password: md5Hash
          });
        },
        true,
        'New Plaintext Password',
        'text'
      );
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

      if (isNaN(usdtRate) || usdtRate <= 0) return;

      const updates = {};
      updates['deposit_settings/upi/upi_id'] = upiId;
      updates['deposit_settings/upi/qr_url'] = upiQr;
      updates['deposit_settings/usdt/usdt_address'] = usdtAddress;
      updates['system_settings/usdt_rate'] = usdtRate;

      db.ref().update(updates);
    }

    // 10. SAVE PLATFORM SETTINGS
    function savePlatformSettings() {
      const signupBal = parseFloat(document.getElementById('setting-signup-balance').value.trim());
      const newAdminPassword = document.getElementById('setting-admin-password').value.trim();
      const maintenanceVal = document.getElementById('setting-maintenance').checked;

      if (isNaN(signupBal) || signupBal < 0) return;

      const updates = {};
      updates['system_settings/default_signup_balance'] = signupBal;
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
            <div class="flex items-center gap-4">
              <span class="text-sm font-black text-white">₹${item.amount.toFixed(2)}</span>
              ${actionLabel}
            </div>
          </div>
        `;
      });
    }

  </script>
  <?php endif; ?>
</body>
</html>

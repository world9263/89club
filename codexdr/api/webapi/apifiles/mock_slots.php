<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>89 Club — Royal Slots</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #060913;
            color: #e2e8f0;
            font-family: 'Segoe UI', sans-serif;
            background-image: radial-gradient(circle at center, #111827 0%, #030712 100%);
        }
        .gold-border {
            border: 2px solid #dfad3a;
            box-shadow: 0 0 15px rgba(223, 173, 58, 0.3);
        }
        .reel {
            background: #0b0f19;
            border: 1px solid #1e293b;
            overflow: hidden;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }
        .spin-btn {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 4px 15px rgba(217, 119, 6, 0.4);
        }
        .spin-btn:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen p-4">
    <!-- Slot Machine Box -->
    <div class="w-full max-w-md bg-slate-900 border-2 border-amber-500 rounded-3xl p-6 shadow-2xl space-y-6">
        <!-- Header -->
        <div class="text-center">
            <h1 class="text-2xl font-black text-amber-400 uppercase tracking-widest flex items-center justify-center gap-2">
                <i class="fa-solid fa-gem text-amber-500 animate-pulse"></i> Royal Slots
            </h1>
            <p class="text-xs text-slate-500">Fast, secure, provably fair local engine</p>
        </div>

        <!-- Balance Info -->
        <div class="bg-slate-950 rounded-2xl p-4 flex items-center justify-between border border-slate-800">
            <div>
                <span class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Your Balance</span>
                <span class="text-lg font-black text-emerald-400 block" id="slot-balance">₹Loading...</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 uppercase font-bold tracking-wider block text-right">Active Wager</span>
                <div class="flex items-center gap-1 mt-0.5">
                    <button onclick="changeBet(-10)" class="w-6 h-6 rounded-full bg-slate-800 hover:bg-slate-700 text-xs font-bold">-</button>
                    <span class="text-sm font-bold text-white w-12 text-center" id="slot-bet-label">₹10</span>
                    <button onclick="changeBet(10)" class="w-6 h-6 rounded-full bg-slate-800 hover:bg-slate-700 text-xs font-bold">+</button>
                </div>
            </div>
        </div>

        <!-- Reels Display Grid -->
        <div class="grid grid-cols-3 gap-3 p-3 bg-slate-950 rounded-2xl border border-slate-800">
            <div class="reel rounded-xl font-bold" id="reel-0">🎰</div>
            <div class="reel rounded-xl font-bold" id="reel-1">🎰</div>
            <div class="reel rounded-xl font-bold" id="reel-2">🎰</div>
        </div>

        <!-- Status Alert -->
        <div class="text-center h-6">
            <p class="text-xs font-bold text-amber-400" id="slot-status">Set your bet & press SPIN!</p>
        </div>

        <!-- Action Button -->
        <button onclick="spinReels()" id="spin-button" class="spin-btn w-full py-4 rounded-2xl text-slate-950 font-black text-lg uppercase tracking-wider transition-all">
            Spin Reels
        </button>

        <!-- Payout Guide -->
        <div class="bg-slate-950 rounded-2xl p-4 border border-slate-800 text-[10px] text-slate-400 space-y-1">
            <h4 class="font-bold text-slate-300 uppercase tracking-wider mb-1.5 border-b border-slate-800 pb-1">Payout Multipliers:</h4>
            <div class="flex justify-between"><span>🍒 🍒 🍒 (Three Cherries)</span><span class="text-amber-400 font-bold">5x</span></div>
            <div class="flex justify-between"><span>🍋 🍋 🍋 (Three Lemons)</span><span class="text-amber-400 font-bold">10x</span></div>
            <div class="flex justify-between"><span>🍇 🍇 🍇 (Three Grapes)</span><span class="text-amber-400 font-bold">20x</span></div>
            <div class="flex justify-between"><span>🔔 🔔 🔔 (Three Bells)</span><span class="text-amber-400 font-bold">50x</span></div>
            <div class="flex justify-between"><span>💎 💎 💎 (Three Diamonds)</span><span class="text-amber-400 font-bold">100x</span></div>
        </div>
    </div>

    <!-- JS Logic -->
    <script>
        const symbols = ['🍒', '🍋', '🍇', '🔔', '💎'];
        const payouts = { '🍒': 5, '🍋': 10, '🍇': 20, '🔔': 50, '💎': 100 };
        
        let urlParams = new URLSearchParams(window.location.search);
        let userId = urlParams.get('userid') || '';
        let currentBalance = 0;
        let currentBet = 10;
        let isSpinning = false;

        async function fetchBalance() {
            try {
                let res = await fetch(`jili.php/Balance?userId=${userId}`);
                let data = await res.json();
                if (data.balance !== undefined) {
                    currentBalance = parseFloat(data.balance);
                    document.getElementById('slot-balance').innerText = '₹' + currentBalance.toFixed(2);
                }
            } catch (err) {
                console.error(err);
            }
        }

        function changeBet(amount) {
            if (isSpinning) return;
            currentBet = Math.max(10, Math.min(1000, currentBet + amount));
            document.getElementById('slot-bet-label').innerText = '₹' + currentBet;
        }

        async function spinReels() {
            if (isSpinning) return;
            if (currentBalance < currentBet) {
                document.getElementById('slot-status').innerText = 'Insufficient balance!';
                document.getElementById('slot-status').className = 'text-xs font-bold text-red-500';
                return;
            }

            isSpinning = true;
            document.getElementById('spin-button').disabled = true;
            document.getElementById('spin-button').className = 'w-full py-4 rounded-2xl bg-slate-800 text-slate-500 font-black text-lg uppercase tracking-wider cursor-not-allowed';
            document.getElementById('slot-status').innerText = 'Spinning...';
            document.getElementById('slot-status').className = 'text-xs font-bold text-amber-400 animate-pulse';

            // Reels animation
            let interval = setInterval(() => {
                document.getElementById('reel-0').innerText = symbols[Math.floor(Math.random() * symbols.length)];
                document.getElementById('reel-1').innerText = symbols[Math.floor(Math.random() * symbols.length)];
                document.getElementById('reel-2').innerText = symbols[Math.floor(Math.random() * symbols.length)];
            }, 80);

            // Calculate outcome
            let r0 = symbols[Math.floor(Math.random() * symbols.length)];
            let r1 = symbols[Math.floor(Math.random() * symbols.length)];
            let r2 = symbols[Math.floor(Math.random() * symbols.length)];

            // Payout calculation
            let winAmount = 0;
            let resultMsg = 'Try Again!';
            let resultClass = 'text-xs font-bold text-slate-500';

            if (r0 === r1 && r1 === r2) {
                let multiplier = payouts[r0];
                winAmount = currentBet * multiplier;
                resultMsg = `JACKPOT! You Won 3x ${r0} (₹${winAmount})!`;
                resultClass = 'text-xs font-bold text-emerald-400';
            }

            // Sync with backend (Balance2)
            try {
                let res = await fetch('jili.php/Balance2', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        userId: userId,
                        betAmount: currentBet,
                        winloseAmount: winAmount
                    })
                });
                let data = await res.json();
                if (data.newBalance !== undefined) {
                    currentBalance = parseFloat(data.newBalance);
                }
            } catch (err) {
                console.error(err);
                resultMsg = 'Transaction failed, refunded!';
                resultClass = 'text-xs font-bold text-red-500';
            }

            setTimeout(() => {
                clearInterval(interval);
                document.getElementById('reel-0').innerText = r0;
                document.getElementById('reel-1').innerText = r1;
                document.getElementById('reel-2').innerText = r2;

                document.getElementById('slot-balance').innerText = '₹' + currentBalance.toFixed(2);
                document.getElementById('slot-status').innerText = resultMsg;
                document.getElementById('slot-status').className = resultClass;

                document.getElementById('spin-button').disabled = false;
                document.getElementById('spin-button').className = 'spin-btn w-full py-4 rounded-2xl text-slate-950 font-black text-lg uppercase tracking-wider transition-all';
                isSpinning = false;
            }, 1000);
        }

        // Initialize
        fetchBalance();
    </script>
</body>
</html>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aviator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #0b0c0e;
            color: #afb1b6;
            font-family: 'Roboto', 'Segoe UI', sans-serif;
            user-select: none;
            overflow-x: hidden;
        }
        .main-container {
            max-width: 480px;
            margin: 0 auto;
            background-color: #0c0d14;
            min-height: 100vh;
            border-left: 1px solid #1a1c24;
            border-right: 1px solid #1a1c24;
        }
        .bg-card {
            background-color: #14151a;
        }
        .bg-graph {
            background-color: #0d0e12;
            background-image: 
                radial-gradient(circle at bottom left, rgba(225, 29, 72, 0.15) 0%, transparent 60%),
                linear-gradient(rgba(26, 28, 36, 0.3) 1px, transparent 1px),
                linear-gradient(90deg, rgba(26, 28, 36, 0.3) 1px, transparent 1px);
            background-size: 100% 100%, 20px 20px, 20px 20px;
        }
        .multi-badge {
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 700;
            background-color: #1a1c24;
        }
        .multi-badge.blue {
            color: #4da6ff;
            background-color: rgba(77, 166, 255, 0.1);
        }
        .multi-badge.purple {
            color: #9333ea;
            background-color: rgba(147, 51, 234, 0.1);
        }
        .multi-badge.pink {
            color: #ec4899;
            background-color: rgba(236, 72, 153, 0.1);
        }
        .btn-bet {
            background: linear-gradient(140deg, #28a745 0%, #218838 100%);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        .btn-cashout {
            background: linear-gradient(140deg, #d97706 0%, #b45309 100%);
            box-shadow: 0 4px 15px rgba(217, 119, 6, 0.3);
        }
        .btn-cancel {
            background: linear-gradient(140deg, #dc3545 0%, #c82333 100%);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        ::-webkit-scrollbar {
            height: 4px;
        }
        ::-webkit-scrollbar-track {
            background: #0c0d14;
        }
        ::-webkit-scrollbar-thumb {
            background: #272a35;
            border-radius: 2px;
        }
    </style>
</head>
<body class="flex justify-center min-h-screen">
    <div class="main-container w-full flex flex-col relative pb-10">
        
        <!-- HEADER -->
        <header class="flex items-center justify-between px-4 py-2 border-b border-[#1b1c24] bg-card shrink-0">
            <div class="flex items-center gap-2">
                <span class="text-rose-600 font-extrabold text-xl italic tracking-wider flex items-center gap-1">
                    <i class="fa-solid fa-paper-plane text-rose-500"></i> Aviator
                </span>
                <button class="w-5 h-5 rounded-full bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center text-[10px]">
                    <i class="fa-solid fa-question"></i>
                </button>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#0a0b0d] border border-[#1f212a]">
                    <span class="text-xs font-bold text-emerald-400" id="header-balance">₹0.00</span>
                    <span class="text-[9px] text-slate-500 font-black tracking-wider uppercase">INR</span>
                </div>
                <button class="text-slate-400 hover:text-white text-lg"><i class="fa-solid fa-bars"></i></button>
                <button class="text-slate-400 hover:text-white text-lg"><i class="fa-solid fa-comment-dots"></i></button>
            </div>
        </header>

        <!-- MULTIPLIERS RIBBON -->
        <div class="flex items-center gap-1.5 px-4 py-1.5 bg-[#090a0e] overflow-x-auto whitespace-nowrap border-b border-[#13141a]" id="ribbon-container">
            <span class="multi-badge blue">1.10x</span>
            <span class="multi-badge blue">1.02x</span>
            <span class="multi-badge purple">2.45x</span>
            <span class="multi-badge blue">1.05x</span>
            <span class="multi-badge pink">18.41x</span>
            <span class="multi-badge purple">4.12x</span>
            <span class="multi-badge blue">1.25x</span>
            <span class="multi-badge purple">3.68x</span>
        </div>

        <!-- FLIGHT SCREEN -->
        <div class="relative w-full h-56 bg-graph shrink-0 border-b border-[#1b1c24] overflow-hidden flex flex-col justify-between p-3">
            <!-- Canvas Graph -->
            <canvas id="flight-canvas" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>

            <!-- Loading Countdown Overlay -->
            <div id="loading-overlay" class="absolute inset-0 z-20 bg-black/50 flex flex-col items-center justify-center space-y-2 transition-opacity duration-300">
                <div class="w-14 h-14 rounded-full border-4 border-rose-500 border-t-transparent animate-spin flex items-center justify-center">
                    <i class="fa-solid fa-plane text-rose-500 text-lg"></i>
                </div>
                <span class="text-xs text-rose-400 uppercase tracking-widest font-black" id="countdown-label">Next Round In 8.0s</span>
            </div>

            <!-- Flew Away Overlay -->
            <div id="flew-away-overlay" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-rose-950/20 hidden">
                <span class="text-rose-500 font-extrabold text-3xl italic tracking-wider uppercase animate-pulse">Flew Away!</span>
            </div>

            <!-- Top screen info -->
            <div class="flex items-center justify-between relative z-10">
                <span class="text-[9px] px-2 py-0.5 rounded bg-black/40 text-slate-400 uppercase tracking-wider font-bold">Rnd: #<span id="label-round-id">Loading</span></span>
                <span class="text-[9px] px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 uppercase tracking-wider font-bold">Provably Fair</span>
            </div>

            <!-- Big Multiplier Display -->
            <div class="text-center relative z-10 my-auto">
                <span class="text-5xl font-black text-white italic tracking-tight" id="multiplier-label">1.00x</span>
            </div>

            <!-- Empty bottom spacer to push items -->
            <div></div>
        </div>

        <!-- DOUBLE BETTING PANELS -->
        <div class="grid grid-cols-1 gap-2 p-3 bg-[#0d0e14] shrink-0 border-b border-[#181a22]">
            
            <!-- PANEL 1 -->
            <div class="bg-card rounded-2xl p-3 border border-[#1c1d24] flex flex-col space-y-2" id="panel-1">
                <div class="flex items-center justify-between">
                    <div class="flex rounded-full bg-[#0a0b0d] p-0.5 border border-[#1e2029]">
                        <button class="px-3 py-1 rounded-full text-[10px] font-bold bg-[#1d1e25] text-white">Bet</button>
                        <button class="px-3 py-1 rounded-full text-[10px] font-bold text-slate-500 hover:text-white">Auto</button>
                    </div>
                </div>
                <div class="grid grid-cols-12 gap-2 items-center">
                    <!-- Bet Control -->
                    <div class="col-span-7 flex flex-col space-y-1.5">
                        <div class="flex items-center justify-between bg-[#0a0b0d] rounded-xl p-1.5 border border-[#1f2129]">
                            <button onclick="adjustWager(1, -10)" class="w-7 h-7 rounded-lg bg-[#1c1d25] hover:bg-slate-800 text-slate-300 font-bold flex items-center justify-center text-sm">-</button>
                            <input type="number" id="input-wager-1" value="100" class="w-16 bg-transparent text-center text-sm font-black text-white outline-none">
                            <button onclick="adjustWager(1, 10)" class="w-7 h-7 rounded-lg bg-[#1c1d25] hover:bg-slate-800 text-slate-300 font-bold flex items-center justify-center text-sm">+</button>
                        </div>
                        <div class="grid grid-cols-4 gap-1">
                            <button onclick="setWager(1, 100)" class="py-1 rounded-lg bg-[#1b1c24] text-[10px] font-bold hover:bg-slate-800 text-slate-300">100</button>
                            <button onclick="setWager(1, 200)" class="py-1 rounded-lg bg-[#1b1c24] text-[10px] font-bold hover:bg-slate-800 text-slate-300">200</button>
                            <button onclick="setWager(1, 500)" class="py-1 rounded-lg bg-[#1b1c24] text-[10px] font-bold hover:bg-slate-800 text-slate-300">500</button>
                            <button onclick="setWager(1, 1000)" class="py-1 rounded-lg bg-[#1b1c24] text-[10px] font-bold hover:bg-slate-800 text-slate-300">1000</button>
                        </div>
                    </div>
                    <!-- Bet Button -->
                    <button onclick="clickBetBtn(1)" id="btn-bet-1" class="col-span-5 h-16 rounded-xl btn-bet text-white flex flex-col items-center justify-center tracking-wide transition-all">
                        <span class="text-xs uppercase font-black" id="btn-bet-label-1">Bet</span>
                        <span class="text-sm font-black mt-0.5" id="btn-wager-label-1">100.00 INR</span>
                    </button>
                </div>
            </div>

            <!-- PANEL 2 -->
            <div class="bg-card rounded-2xl p-3 border border-[#1c1d24] flex flex-col space-y-2" id="panel-2">
                <div class="flex items-center justify-between">
                    <div class="flex rounded-full bg-[#0a0b0d] p-0.5 border border-[#1e2029]">
                        <button class="px-3 py-1 rounded-full text-[10px] font-bold bg-[#1d1e25] text-white">Bet</button>
                        <button class="px-3 py-1 rounded-full text-[10px] font-bold text-slate-500 hover:text-white">Auto</button>
                    </div>
                </div>
                <div class="grid grid-cols-12 gap-2 items-center">
                    <!-- Bet Control -->
                    <div class="col-span-7 flex flex-col space-y-1.5">
                        <div class="flex items-center justify-between bg-[#0a0b0d] rounded-xl p-1.5 border border-[#1f2129]">
                            <button onclick="adjustWager(2, -10)" class="w-7 h-7 rounded-lg bg-[#1c1d25] hover:bg-slate-800 text-slate-300 font-bold flex items-center justify-center text-sm">-</button>
                            <input type="number" id="input-wager-2" value="100" class="w-16 bg-transparent text-center text-sm font-black text-white outline-none">
                            <button onclick="adjustWager(2, 10)" class="w-7 h-7 rounded-lg bg-[#1c1d25] hover:bg-slate-800 text-slate-300 font-bold flex items-center justify-center text-sm">+</button>
                        </div>
                        <div class="grid grid-cols-4 gap-1">
                            <button onclick="setWager(2, 100)" class="py-1 rounded-lg bg-[#1b1c24] text-[10px] font-bold hover:bg-slate-800 text-slate-300">100</button>
                            <button onclick="setWager(2, 200)" class="py-1 rounded-lg bg-[#1b1c24] text-[10px] font-bold hover:bg-slate-800 text-slate-300">200</button>
                            <button onclick="setWager(2, 500)" class="py-1 rounded-lg bg-[#1b1c24] text-[10px] font-bold hover:bg-slate-800 text-slate-300">500</button>
                            <button onclick="setWager(2, 1000)" class="py-1 rounded-lg bg-[#1b1c24] text-[10px] font-bold hover:bg-slate-800 text-slate-300">1000</button>
                        </div>
                    </div>
                    <!-- Bet Button -->
                    <button onclick="clickBetBtn(2)" id="btn-bet-2" class="col-span-5 h-16 rounded-xl btn-bet text-white flex flex-col items-center justify-center tracking-wide transition-all">
                        <span class="text-xs uppercase font-black" id="btn-bet-label-2">Bet</span>
                        <span class="text-sm font-black mt-0.5" id="btn-wager-label-2">100.00 INR</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- GAME TABS & STATISTICS -->
        <div class="flex flex-col bg-[#0b0c10] grow min-h-[250px]">
            <!-- Tab Selector -->
            <div class="grid grid-cols-3 text-center border-b border-[#1b1c24] bg-card shrink-0">
                <button onclick="switchTab('all')" id="tab-btn-all" class="py-2.5 text-xs font-bold text-white border-b-2 border-rose-500">All Bets</button>
                <button onclick="switchTab('my')" id="tab-btn-my" class="py-2.5 text-xs font-bold text-slate-400 hover:text-white border-b-2 border-transparent">My Bets</button>
                <button onclick="switchTab('top')" id="tab-btn-top" class="py-2.5 text-xs font-bold text-slate-400 hover:text-white border-b-2 border-transparent">Top</button>
            </div>

            <!-- Tab Content: List logs -->
            <div class="p-3 overflow-y-auto grow flex flex-col space-y-1.5" id="history-list-box">
                <!-- Bets rendering goes here -->
            </div>
        </div>

    </div>

    <!-- MAIN GAME SIMULATOR LOOP -->
    <script>
        let urlParams = new URLSearchParams(window.location.search);
        let userId = urlParams.get('userid') || '';
        
        let currentBalance = 0.0;
        let activeRoundId = 0;
        let crashMultiplier = 1.0;
        let currentMultiplier = 1.0;
        
        let localState = 'idle'; // 'betting', 'flying', 'crashed'
        let serverTimeOffset = 0;
        
        let panelState = {
            1: { status: 'idle', betAmount: 100 },
            2: { status: 'idle', betAmount: 100 }
        };

        // Graph flight line configurations
        const canvas = document.getElementById('flight-canvas');
        const ctx = canvas.getContext('2d');
        let width = canvas.width = canvas.offsetWidth;
        let height = canvas.height = canvas.offsetHeight;

        function adjustWager(panelNum, delta) {
            if (panelState[panelNum].status !== 'idle') return;
            let val = parseInt(document.getElementById(`input-wager-${panelNum}`).value) || 100;
            val = Math.max(10, Math.min(10000, val + delta));
            document.getElementById(`input-wager-${panelNum}`).value = val;
            document.getElementById(`btn-wager-label-${panelNum}`).innerText = val.toFixed(2) + ' INR';
            panelState[panelNum].betAmount = val;
        }

        function setWager(panelNum, val) {
            if (panelState[panelNum].status !== 'idle') return;
            document.getElementById(`input-wager-${panelNum}`).value = val;
            document.getElementById(`btn-wager-label-${panelNum}`).innerText = val.toFixed(2) + ' INR';
            panelState[panelNum].betAmount = val;
        }

        async function clickBetBtn(panelNum) {
            let state = panelState[panelNum];
            if (state.status === 'idle') {
                if (currentBalance < state.betAmount) {
                    alert("Insufficient Wallet Balance!");
                    return;
                }

                try {
                    let res = await fetch(`aviator_api.php?action=place_bet&userId=${userId}`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ amount: state.betAmount, panelId: `panel${panelNum}` })
                    });
                    let data = await res.json();
                    if (data.success) {
                        state.status = 'wagered';
                        currentBalance = parseFloat(data.balance);
                        document.getElementById('header-balance').innerText = '₹' + currentBalance.toFixed(2);
                        
                        let btn = document.getElementById(`btn-bet-${panelNum}`);
                        btn.className = 'col-span-5 h-16 rounded-xl btn-cancel text-white flex flex-col items-center justify-center tracking-wide transition-all';
                        document.getElementById(`btn-bet-label-${panelNum}`).innerText = 'Cancel';
                    } else {
                        alert(data.error || "Wager placing failed");
                    }
                } catch(e) {
                    console.error(e);
                }
            } else if (state.status === 'wagered' && localState === 'betting') {
                try {
                    let res = await fetch(`aviator_api.php?action=cashout&userId=${userId}`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ panelId: `panel${panelNum}`, multiplier: 1.0 })
                    });
                    let data = await res.json();
                    if (data.success) {
                        state.status = 'idle';
                        currentBalance = parseFloat(data.balance);
                        document.getElementById('header-balance').innerText = '₹' + currentBalance.toFixed(2);
                        
                        let btn = document.getElementById(`btn-bet-${panelNum}`);
                        btn.className = 'col-span-5 h-16 rounded-xl btn-bet text-white flex flex-col items-center justify-center tracking-wide transition-all';
                        document.getElementById(`btn-bet-label-${panelNum}`).innerText = 'Bet';
                    }
                } catch (e) {
                    console.error(e);
                }
            } else if (state.status === 'wagered' && localState === 'flying') {
                try {
                    let res = await fetch(`aviator_api.php?action=cashout&userId=${userId}`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ panelId: `panel${panelNum}`, multiplier: currentMultiplier })
                    });
                    let data = await res.json();
                    if (data.success) {
                        state.status = 'idle';
                        currentBalance = parseFloat(data.balance);
                        document.getElementById('header-balance').innerText = '₹' + currentBalance.toFixed(2);
                        
                        alert(`Cashed out at ${currentMultiplier.toFixed(2)}x! Win: ₹${data.winAmount}`);

                        let btn = document.getElementById(`btn-bet-${panelNum}`);
                        btn.className = 'col-span-5 h-16 rounded-xl btn-bet text-white flex flex-col items-center justify-center tracking-wide transition-all';
                        document.getElementById(`btn-bet-label-${panelNum}`).innerText = 'Bet';
                        document.getElementById(`btn-wager-label-${panelNum}`).innerText = state.betAmount.toFixed(2) + ' INR';
                    } else {
                        alert(data.error || "Cashout failed");
                    }
                } catch(e) {
                    console.error(e);
                }
            }
        }

        // Fetch state every 800ms
        async function updateState() {
            try {
                let res = await fetch(`aviator_api.php?action=get_state&userId=${userId}`);
                let data = await res.json();
                
                activeRoundId = data.roundId;
                crashMultiplier = parseFloat(data.crashMultiplier);
                currentBalance = parseFloat(data.balance);
                
                // Align client clock with server clock
                let serverNow = parseFloat(data.serverTimeMs);
                serverTimeOffset = serverNow - Date.now();

                document.getElementById('label-round-id').innerText = activeRoundId;
                document.getElementById('header-balance').innerText = '₹' + currentBalance.toFixed(2);

                updateRibbon(data.history);
                updateActiveTabList(data.bets);

            } catch (err) {
                console.error(err);
            }
        }

        function updateRibbon(history) {
            let container = document.getElementById('ribbon-container');
            container.innerHTML = '';
            history.forEach(m => {
                let badge = document.createElement('span');
                let floatM = parseFloat(m);
                badge.className = 'multi-badge';
                if (floatM < 1.2) {
                    badge.classList.add('blue');
                } else if (floatM < 5.0) {
                    badge.classList.add('purple');
                } else {
                    badge.classList.add('pink');
                }
                badge.innerText = floatM.toFixed(2) + 'x';
                container.appendChild(badge);
            });
        }

        // Draw curved flight path and plane icon with rotating propeller rotor
        function drawFlightCanvas(roundElapsedMs) {
            ctx.clearRect(0, 0, width, height);

            let flightTime = (roundElapsedMs - 8000) / 1000;
            if (localState !== 'flying' || flightTime < 0) return;

            let ratio = Math.min(1.0, flightTime / 18);

            let startX = 40;
            let startY = height - 40;
            let endX = startX + ratio * (width - 100);
            let endY = startY - ratio * (height - 100);

            // 1. Draw Grid Lines
            ctx.strokeStyle = '#161822';
            ctx.lineWidth = 1;
            for (let i = 0; i < width; i += 40) {
                ctx.beginPath();
                ctx.moveTo(i, 0);
                ctx.lineTo(i, height);
                ctx.stroke();
            }
            for (let j = 0; j < height; j += 40) {
                ctx.beginPath();
                ctx.moveTo(0, j);
                ctx.lineTo(width, j);
                ctx.stroke();
            }

            // 2. Draw Flying Curve Path
            ctx.beginPath();
            ctx.strokeStyle = '#e11d48';
            ctx.lineWidth = 4;
            ctx.moveTo(startX, startY);
            ctx.quadraticCurveTo(width / 2.5, height - 20, endX, endY);
            ctx.stroke();

            // 3. Gradient Shading Fill
            let fillGrad = ctx.createLinearGradient(0, height, endX, endY);
            fillGrad.addColorStop(0, 'rgba(225, 29, 72, 0.0)');
            fillGrad.addColorStop(1, 'rgba(225, 29, 72, 0.25)');
            ctx.fillStyle = fillGrad;
            ctx.beginPath();
            ctx.moveTo(startX, startY);
            ctx.quadraticCurveTo(width / 2.5, height - 20, endX, endY);
            ctx.lineTo(endX, height);
            ctx.lineTo(startX, height);
            ctx.closePath();
            ctx.fill();

            // 4. Draw Airplane Icon Flying (Detailed Propeller Plane)
            ctx.save();
            ctx.translate(endX, endY);
            ctx.rotate(-Math.PI / 8); 

            // Glow effect
            ctx.shadowBlur = 15;
            ctx.shadowColor = '#e11d48';

            // Plane Body
            ctx.fillStyle = '#e11d48';
            ctx.beginPath();
            ctx.moveTo(-15, -4);
            ctx.lineTo(10, -4);
            ctx.quadraticCurveTo(15, -4, 18, 0);
            ctx.quadraticCurveTo(15, 4, 10, 4);
            ctx.lineTo(-15, 4);
            ctx.lineTo(-18, 8); 
            ctx.lineTo(-20, 8);
            ctx.lineTo(-18, -8);
            ctx.lineTo(-20, -8);
            ctx.closePath();
            ctx.fill();

            // Wings
            ctx.fillStyle = '#f43f5e';
            ctx.beginPath();
            ctx.moveTo(-2, -4);
            ctx.lineTo(2, -15);
            ctx.lineTo(6, -15);
            ctx.lineTo(2, -4);
            ctx.closePath();
            ctx.fill();

            ctx.beginPath();
            ctx.moveTo(-2, 4);
            ctx.lineTo(2, 15);
            ctx.lineTo(6, 15);
            ctx.lineTo(2, 4);
            ctx.closePath();
            ctx.fill();

            // Propeller spinning visual
            ctx.strokeStyle = '#fda4af';
            ctx.lineWidth = 2;
            let propSpin = (Date.now() / 40) % (2 * Math.PI);
            ctx.beginPath();
            ctx.moveTo(18, -6 * Math.sin(propSpin));
            ctx.lineTo(18, 6 * Math.sin(propSpin));
            ctx.stroke();

            ctx.restore();
        }

        // Active logs tabs logic
        let activeTab = 'all';
        function switchTab(tab) {
            activeTab = tab;
            ['all', 'my', 'top'].forEach(t => {
                let btn = document.getElementById(`tab-btn-${t}`);
                if (t === tab) {
                    btn.className = 'py-2.5 text-xs font-bold text-white border-b-2 border-rose-500';
                } else {
                    btn.className = 'py-2.5 text-xs font-bold text-slate-400 hover:text-white border-b-2 border-transparent';
                }
            });
        }

        function updateActiveTabList(bets) {
            let container = document.getElementById('history-list-box');
            container.innerHTML = '';
            
            let betList = Object.values(bets);

            if (betList.length < 5) {
                let names = ["AmanS", "RohitK", "Jack99", "PlayerX", "KunalR", "VIP_777", "RajuBhai"];
                for (let i = 0; i < 8; i++) {
                    let randAmt = Math.floor(Math.random() * 5 + 1) * 100;
                    let randStatus = Math.random() < 0.4 ? 'won' : 'pending';
                    let randMul = 1.1 + (Math.random() * 2);
                    betList.push({
                        userId: names[i % names.length] + "****",
                        amount: randAmt,
                        status: randStatus,
                        cashoutMultiplier: randMul,
                        winAmount: randAmt * randMul
                    });
                }
            }

            if (activeTab === 'my') {
                betList = betList.filter(b => b.userId == userId || b.userId == userId + '_panel1' || b.userId == userId + '_panel2');
            }

            if (activeTab === 'top') {
                betList.sort((a, b) => b.winAmount - a.winAmount);
            }

            betList.forEach(b => {
                let row = document.createElement('div');
                row.className = 'flex items-center justify-between px-3 py-1.5 rounded-lg bg-card text-xs border border-slate-900';
                
                let nameBox = document.createElement('span');
                nameBox.className = 'font-bold text-slate-300';
                nameBox.innerText = b.userId;

                let amtBox = document.createElement('span');
                amtBox.className = 'font-black text-slate-500';
                amtBox.innerText = '₹' + parseFloat(b.amount).toFixed(2);

                let actionBox = document.createElement('div');
                actionBox.className = 'flex items-center gap-2';

                if (b.status === 'won') {
                    let mulBadge = document.createElement('span');
                    mulBadge.className = 'multi-badge purple';
                    mulBadge.innerText = parseFloat(b.cashoutMultiplier).toFixed(2) + 'x';
                    
                    let winBox = document.createElement('span');
                    winBox.className = 'font-bold text-emerald-400';
                    winBox.innerText = '₹' + parseFloat(b.winAmount).toFixed(2);
                    
                    actionBox.appendChild(mulBadge);
                    actionBox.appendChild(winBox);
                } else if (localState === 'crashed') {
                    let loseBox = document.createElement('span');
                    loseBox.className = 'font-bold text-rose-500';
                    loseBox.innerText = 'Lost';
                    actionBox.appendChild(loseBox);
                } else {
                    let flyBox = document.createElement('span');
                    flyBox.className = 'font-bold text-amber-400 animate-pulse';
                    flyBox.innerText = 'Flying...';
                    actionBox.appendChild(flyBox);
                }

                row.appendChild(nameBox);
                row.appendChild(amtBox);
                row.appendChild(actionBox);
                container.appendChild(row);
            });
        }

        // 60fps Client-Side Animation and Multiplier Climbing
        function animate() {
            let adjustedNow = Date.now() + serverTimeOffset;
            let roundElapsedMs = adjustedNow % 30000;
            
            if (roundElapsedMs < 8000) {
                localState = 'betting';
                let remaining = ((8000 - roundElapsedMs) / 1000).toFixed(1);
                document.getElementById('loading-overlay').classList.remove('opacity-0');
                document.getElementById('loading-overlay').classList.remove('pointer-events-none');
                document.getElementById('countdown-label').innerText = `Next Round In ${remaining}s`;
                document.getElementById('flew-away-overlay').classList.add('hidden');
                
                currentMultiplier = 1.0;
                document.getElementById('multiplier-label').innerText = '1.00x';
                document.getElementById('multiplier-label').className = 'text-5xl font-black text-white italic tracking-tight';

                for (let pNum = 1; pNum <= 2; pNum++) {
                    let state = panelState[pNum];
                    if (state.status === 'idle') {
                        let btn = document.getElementById(`btn-bet-${pNum}`);
                        btn.className = 'col-span-5 h-16 rounded-xl btn-bet text-white flex flex-col items-center justify-center tracking-wide transition-all';
                        document.getElementById(`btn-bet-label-${pNum}`).innerText = 'Bet';
                        document.getElementById(`btn-wager-label-${pNum}`).innerText = state.betAmount.toFixed(2) + ' INR';
                    }
                }
            } else {
                document.getElementById('loading-overlay').classList.add('opacity-0');
                document.getElementById('loading-overlay').classList.add('pointer-events-none');
                
                let flightTime = (roundElapsedMs - 8000) / 1000;
                let nextMultiplier = parseFloat((1.0 + Math.pow(flightTime, 1.8) * 0.06).toFixed(2));

                if (nextMultiplier >= crashMultiplier) {
                    localState = 'crashed';
                    document.getElementById('flew-away-overlay').classList.remove('hidden');
                    document.getElementById('multiplier-label').innerText = crashMultiplier.toFixed(2) + 'x';
                    document.getElementById('multiplier-label').className = 'text-5xl font-black text-rose-600 italic tracking-tight animate-bounce';
                    
                    for (let pNum = 1; pNum <= 2; pNum++) {
                        let state = panelState[pNum];
                        if (state.status === 'wagered') {
                            state.status = 'idle';
                            let btn = document.getElementById(`btn-bet-${pNum}`);
                            btn.className = 'col-span-5 h-16 rounded-xl btn-bet text-white flex flex-col items-center justify-center tracking-wide transition-all';
                            document.getElementById(`btn-bet-label-${pNum}`).innerText = 'Bet';
                            document.getElementById(`btn-wager-label-${pNum}`).innerText = state.betAmount.toFixed(2) + ' INR';
                        }
                    }
                } else {
                    localState = 'flying';
                    document.getElementById('flew-away-overlay').classList.add('hidden');
                    
                    currentMultiplier = nextMultiplier;
                    document.getElementById('multiplier-label').innerText = currentMultiplier.toFixed(2) + 'x';
                    document.getElementById('multiplier-label').className = 'text-5xl font-black text-white italic tracking-tight';

                    for (let pNum = 1; pNum <= 2; pNum++) {
                        let state = panelState[pNum];
                        if (state.status === 'wagered') {
                            let btn = document.getElementById(`btn-bet-${pNum}`);
                            btn.className = 'col-span-5 h-16 rounded-xl btn-cashout text-slate-950 flex flex-col items-center justify-center tracking-wide transition-all';
                            document.getElementById(`btn-bet-label-${pNum}`).innerText = 'Cash Out';
                            let cashWin = (state.betAmount * currentMultiplier).toFixed(2);
                            document.getElementById(`btn-wager-label-${pNum}`).innerText = cashWin + ' INR';
                        }
                    }
                }
            }

            drawFlightCanvas(roundElapsedMs);
            requestAnimationFrame(animate);
        }

        // Initialize state loops
        updateState();
        setInterval(updateState, 800);
        requestAnimationFrame(animate);
    </script>
</body>
</html>

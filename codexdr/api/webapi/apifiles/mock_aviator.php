<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>89 Club — Aviator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #0d0105;
            color: #e2e8f0;
            font-family: 'Segoe UI', sans-serif;
            background-image: radial-gradient(circle at center, #1a0208 0%, #0d0105 100%);
        }
        .aviator-border {
            border: 2px solid #e11d48;
            box-shadow: 0 0 15px rgba(225, 29, 72, 0.2);
        }
        .cashout-btn {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
            box-shadow: 0 4px 15px rgba(225, 29, 72, 0.4);
        }
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-lg bg-[#14151a] rounded-3xl p-6 shadow-2xl border border-rose-950 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-900 pb-3">
            <h1 class="text-xl font-black text-rose-500 uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-plane-departure animate-bounce"></i> Aviator
            </h1>
            <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 font-bold border border-emerald-500/10" id="balance-label">₹Loading...</span>
        </div>

        <!-- Flying Screen Area -->
        <div class="relative w-full h-64 bg-[#0a0b0d] rounded-2xl flex items-center justify-center overflow-hidden border border-slate-900">
            <!-- Canvas for flight graph -->
            <canvas id="flight-canvas" class="absolute inset-0 w-full h-full"></canvas>
            
            <!-- Multiplier Display -->
            <div class="relative z-10 text-center">
                <span class="text-5xl font-black text-white" id="multiplier-label">1.00x</span>
                <p class="text-xs text-rose-500 mt-2 font-bold uppercase tracking-wider hidden animate-pulse" id="flew-away-label">Flew Away!</p>
            </div>
        </div>

        <!-- Controls Box -->
        <div class="grid grid-cols-2 gap-4">
            <!-- Bet Amount Selection -->
            <div class="bg-[#0a0b0d] rounded-2xl p-4 border border-slate-900 flex flex-col justify-between">
                <span class="text-[10px] text-slate-500 uppercase font-bold tracking-wider block">Bet Amount</span>
                <div class="flex items-center justify-between mt-2">
                    <button onclick="changeBet(-10)" class="w-8 h-8 rounded-xl bg-[#1c1d24] hover:bg-slate-800 font-bold text-slate-300">-</button>
                    <span class="text-lg font-black text-white" id="bet-label">₹10</span>
                    <button onclick="changeBet(10)" class="w-8 h-8 rounded-xl bg-[#1c1d24] hover:bg-slate-800 font-bold text-slate-300">+</button>
                </div>
            </div>

            <!-- Action Button -->
            <button onclick="handleActionButton()" id="action-button" class="w-full h-full rounded-2xl bg-amber-500 text-slate-950 font-black text-lg uppercase tracking-wider transition-all hover:bg-amber-400 shadow-lg shadow-amber-500/10">
                Bet
            </button>
        </div>
    </div>

    <!-- JS Logic -->
    <script>
        let urlParams = new URLSearchParams(window.location.search);
        let userId = urlParams.get('userid') || '';
        let currentBalance = 0;
        let currentBet = 10;
        let gameState = 'idle'; // 'idle', 'betting', 'flying', 'cashed_out', 'crashed'
        
        let currentMultiplier = 1.0;
        let crashMultiplier = 1.0;
        let flightInterval = null;
        let animFrame = null;

        // Canvas Configuration
        const canvas = document.getElementById('flight-canvas');
        const ctx = canvas.getContext('2d');
        let width = canvas.width = canvas.offsetWidth;
        let height = canvas.height = canvas.offsetHeight;

        async function fetchBalance() {
            try {
                let res = await fetch(`jili.php/Balance?userId=${userId}`);
                let data = await res.json();
                if (data.balance !== undefined) {
                    currentBalance = parseFloat(data.balance);
                    document.getElementById('balance-label').innerText = '₹' + currentBalance.toFixed(2);
                }
            } catch (err) {
                console.error(err);
            }
        }

        function changeBet(amount) {
            if (gameState !== 'idle') return;
            currentBet = Math.max(10, Math.min(1000, currentBet + amount));
            document.getElementById('bet-label').innerText = '₹' + currentBet;
        }

        function handleActionButton() {
            if (gameState === 'idle') {
                placeBet();
            } else if (gameState === 'flying') {
                cashOut();
            }
        }

        async function placeBet() {
            if (currentBalance < currentBet) {
                alert('Insufficient Balance!');
                return;
            }

            gameState = 'flying';
            document.getElementById('flew-away-label').classList.add('hidden');
            
            // Deduct Bet immediately in database (Balance3 with type=1)
            try {
                let res = await fetch('jili.php/Balance3', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        userId: userId,
                        betAmount: currentBet,
                        winloseAmount: 0,
                        type: 1
                    })
                });
                let data = await res.json();
                if (data.newBalance !== undefined) {
                    currentBalance = parseFloat(data.newBalance);
                    document.getElementById('balance-label').innerText = '₹' + currentBalance.toFixed(2);
                }
            } catch (err) {
                console.error(err);
            }

            // Update UI to cashout
            const btn = document.getElementById('action-button');
            btn.innerText = 'Cash Out';
            btn.className = 'w-full h-full rounded-2xl cashout-btn text-white font-black text-lg uppercase tracking-wider transition-all';
            
            // Random crash point between 1.01 and 7.5
            crashMultiplier = 1.0 + (Math.random() * 6.5);
            if (Math.random() < 0.15) crashMultiplier = 1.0; // Instant crash 15% of the time

            currentMultiplier = 1.0;
            startFlightAnimation();
        }

        function startFlightAnimation() {
            let startTime = Date.now();
            
            flightInterval = setInterval(() => {
                let elapsed = (Date.now() - startTime) / 1000;
                
                // Exponential growth curves
                currentMultiplier = 1.0 + Math.pow(elapsed, 1.8) * 0.15;
                document.getElementById('multiplier-label').innerText = currentMultiplier.toFixed(2) + 'x';
                
                if (currentMultiplier >= crashMultiplier) {
                    triggerCrash();
                }
            }, 50);

            drawFlightCurve();
        }

        function drawFlightCurve() {
            let x = 0;
            function animate() {
                if (gameState !== 'flying') return;
                ctx.clearRect(0, 0, width, height);
                
                // Draw curve
                ctx.beginPath();
                ctx.strokeStyle = '#e11d48';
                ctx.lineWidth = 4;
                ctx.moveTo(0, height);
                
                let curX = (currentMultiplier - 1.0) * (width / 7);
                let curY = height - (currentMultiplier - 1.0) * (height / 7);
                
                ctx.quadraticCurveTo(width / 2, height, curX, curY);
                ctx.stroke();
                
                // Draw plane icon
                ctx.fillStyle = '#e11d48';
                ctx.beginPath();
                ctx.arc(curX, curY, 8, 0, 2 * Math.PI);
                ctx.fill();
                
                animFrame = requestAnimationFrame(animate);
            }
            animate();
        }

        async function cashOut() {
            gameState = 'cashed_out';
            clearInterval(flightInterval);
            cancelAnimationFrame(animFrame);
            
            let winAmount = currentBet * currentMultiplier;
            
            // Credit win to database (Balance3 with type=2)
            try {
                let res = await fetch('jili.php/Balance3', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        userId: userId,
                        betAmount: 0,
                        winloseAmount: winAmount,
                        type: 2
                    })
                });
                let data = await res.json();
                if (data.newBalance !== undefined) {
                    currentBalance = parseFloat(data.newBalance);
                    document.getElementById('balance-label').innerText = '₹' + currentBalance.toFixed(2);
                }
            } catch (err) {
                console.error(err);
            }

            alert(`You Cashed Out successfully at ${currentMultiplier.toFixed(2)}x (Won ₹${winAmount.toFixed(2)})!`);
            resetGame();
        }

        function triggerCrash() {
            gameState = 'crashed';
            clearInterval(flightInterval);
            cancelAnimationFrame(animFrame);

            // Display red flew away
            document.getElementById('multiplier-label').className = 'text-5xl font-black text-rose-600';
            document.getElementById('flew-away-label').classList.remove('hidden');
            
            setTimeout(() => {
                resetGame();
            }, 2500);
        }

        function resetGame() {
            gameState = 'idle';
            document.getElementById('multiplier-label').className = 'text-5xl font-black text-white';
            document.getElementById('multiplier-label').innerText = '1.00x';
            document.getElementById('flew-away-label').classList.add('hidden');
            
            // Draw initial clean canvas
            ctx.clearRect(0, 0, width, height);

            const btn = document.getElementById('action-button');
            btn.innerText = 'Bet';
            btn.className = 'w-full h-full rounded-2xl bg-amber-500 text-slate-950 font-black text-lg uppercase tracking-wider transition-all hover:bg-amber-400';
            
            fetchBalance();
        }

        // Initialize
        fetchBalance();
    </script>
</body>
</html>

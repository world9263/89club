<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎮 Game Self-service Center</title>
    <style>
    * { 
        margin: 0; 
        padding: 0; 
        box-sizing: border-box; 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        color: #333;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
    }
    
    .container {
        width: 100%;
        max-width: 500px;
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }
    
    .container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 10px;
        background: linear-gradient(90deg, #ff9a9e 0%, #fad0c4 50%, #fbc2eb 100%);
    }
    
    .support-buttons {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    
    .support-btn {
        flex: 1;
        padding: 12px;
        border-radius: 10px;
        color: white;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .support-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 14px rgba(0, 0, 0, 0.1);
    }
    
    .support-btn:active {
        transform: translateY(0);
    }
    
    .telegram-btn {
        background: #0088cc;
        margin-right: 10px;
    }
    
    .whatsapp-btn {
        background: #25D366;
        margin-left: 10px;
    }
    
    .support-btn i {
        margin-right: 8px;
        font-size: 1.2em;
    }
    
    .header-title { 
        font-size: 1.8em;
        color: #4a4a4a;
        margin-bottom: 25px;
        text-align: center;
        font-weight: 700;
        background: linear-gradient(90deg, #ff9a9e 0%, #fad0c4 50%, #fbc2eb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }
    
    .form-group { 
        margin-bottom: 20px; 
        position: relative;
    }
    
    .form-group label { 
        font-size: 0.95em; 
        margin-bottom: 8px; 
        display: block; 
        color: #5a5a5a;
        font-weight: 600;
    }
    
    .form-group select, .form-group input, .form-group textarea {
        width: 100%; 
        padding: 12px 15px; 
        font-size: 1em; 
        border: 2px solid #e0e0e0;
        border-radius: 10px; 
        background-color: #f9f9f9; 
        color: #333;
        transition: all 0.3s ease;
    }
    
    .form-group select:focus, .form-group input:focus, .form-group textarea:focus {
        border-color: #fbc2eb;
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(251, 194, 235, 0.3);
        outline: none;
    }
    
    .form-group input::placeholder {
        color: #aaa;
    }
    
    .additional-form { 
        display: none; 
        animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .btn { 
        width: 100%; 
        padding: 14px; 
        font-size: 1.1em; 
        border: none; 
        border-radius: 10px; 
        cursor: pointer; 
        margin: 10px 0; 
        color: white;
        font-weight: 600;
        background: linear-gradient(90deg, #ff9a9e 0%, #fad0c4 50%, #fbc2eb 100%);
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .btn:hover { 
        transform: translateY(-2px);
        box-shadow: 0 7px 14px rgba(0, 0, 0, 0.1);
    }
    
    .btn:active { 
        transform: translateY(0);
    }
    
    .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(5px);
    }
    
    .popup {
        width: 90%;
        max-width: 400px;
        padding: 25px;
        background-color: white;
        border-radius: 15px;
        text-align: center;
        position: relative;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        animation: popIn 0.4s ease;
    }
    
    @keyframes popIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
    
    .popup-close {
        position: absolute;
        top: 15px;
        right: 15px;
        color: #ff9a9e;
        cursor: pointer;
        font-size: 1.8em;
        font-weight: bold;
        transition: transform 0.3s ease;
    }
    
    .popup-close:hover {
        transform: rotate(90deg);
        color: #f76b8a;
    }
    
    #issueResults {
        max-height: 300px;
        overflow-y: auto;
        text-align: left;
        margin-top: 20px;
        padding-right: 10px;
    }
    
    #issueResults p { 
        margin: 8px 0; 
        line-height: 1.5; 
        color: #555;
    }
    
    #issueResults strong {
        color: #ff9a9e;
    }
    
    #issueResults hr { 
        border: none;
        height: 1px;
        background: linear-gradient(90deg, transparent 0%, #ff9a9e 50%, transparent 100%);
        margin: 15px 0; 
    }
    
    .issue-card {
        background: #f9f9f9;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        border-left: 4px solid #fbc2eb;
        transition: all 0.3s ease;
    }
    
    .issue-card:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .status-pending {
        color: #ff9800;
    }
    
    .status-resolved {
        color: #4caf50;
    }
    
    .status-processing {
        color: #2196f3;
    }
    
    /* Custom scrollbar */
    #issueResults::-webkit-scrollbar {
        width: 6px;
    }
    
    #issueResults::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    #issueResults::-webkit-scrollbar-thumb {
        background: linear-gradient(#ff9a9e, #fbc2eb);
        border-radius: 10px;
    }
    
    /* File input styling */
    .file-input-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
        width: 100%;
    }
    
    .file-input-button {
        border: 2px dashed #e0e0e0;
        border-radius: 10px;
        padding: 30px 15px;
        text-align: center;
        background-color: #f9f9f9;
        color: #777;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
    }
    
    .file-input-button:hover {
        border-color: #fbc2eb;
        background-color: #fff;
    }
    
    .file-input-wrapper input[type=file] {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .file-name {
        margin-top: 8px;
        font-size: 0.8em;
        color: #ff9a9e;
        font-weight: 500;
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="support-buttons">
            <a href="https://t.me/kheloindi890" class="support-btn telegram-btn" target="_blank">
                <i class="fab fa-telegram"></i> Telegram Support
            </a>
            <a href="https://t.me/kheloindi890" class="support-btn whatsapp-btn" target="_blank">
                <i class="fab fa-whatsapp"></i> WhatsApp Support
            </a>
        </div>
        
        <div class="header-title">🎮 Game Self-service Center</div>

        <form action="submit_issue.php" method="POST" enctype="multipart/form-data" onsubmit="return confirmSubmission()">
            <div class="form-group">
                <label for="issue">What issue are you facing?</label>
                <select id="issue" name="issue" onchange="toggleAdditionalForm()" required>
                    <option value="">-- Select your issue --</option>
                    <option value="depositNotReceived">💰 Deposit Not Received</option>
                    <option value="withdrawalProblem">💸 Withdrawal Problem</option>
                    <option value="modifyBankAccount">🏦 Modify Bank Account</option>
                    <option value="changeBankAccount">🔄 Change Bank Account</option>
                    <option value="wingo1MinWinStreakBonus">🎰 Wingo Win Streak Bonus</option>
                </select>
            </div>

            <div class="form-group">
                <label for="account">Your Game ID:</label>
                <input type="text" id="account" name="account" placeholder="Enter your Game ID" required />
            </div>

            <!-- Deposit Not Received Form -->
            <div id="depositNotReceivedForm" class="additional-form">
                <div class="form-group">
                    <label for="amountDeposit">Deposit Amount:</label>
                    <input type="text" id="amountDeposit" name="amountDeposit" placeholder="Enter amount" />
                </div>
                <div class="form-group">
                    <label for="utrNumber">UTR/Transaction ID:</label>
                    <input type="text" id="utrNumber" name="utrNumber" placeholder="Enter transaction reference" />
                </div>
                <div class="form-group">
                    <label for="upiid">Receiver UPI ID:</label>
                    <input type="text" id="upiid" name="upiid" placeholder="Enter UPI ID used" />
                </div>
                <div class="form-group">
                    <label>Deposit Proof:</label>
                    <div class="file-input-wrapper">
                        <div class="file-input-button">
                            <i class="fas fa-cloud-upload-alt"></i> Click to upload receipt
                            <div class="file-name" id="depositProofName">No file chosen</div>
                        </div>
                        <input type="file" id="depositProof" name="depositProof" onchange="showFileName(this, 'depositProofName')" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="orderNumber">Order Number (if any):</label>
                    <input type="text" id="orderNumber" name="orderNumber" placeholder="Enter order number" />
                </div>
            </div>

            <!-- Withdrawal Problem Form -->
            <div id="withdrawalProblemForm" class="additional-form">
                <div class="form-group">
                    <label for="withdrawalOrderNumber">Withdrawal Order #:</label>
                    <input type="text" id="withdrawalOrderNumber" name="withdrawalOrderNumber" placeholder="Enter order number" />
                </div>
                <div class="form-group">
                    <label for="withdrawalAmount">Amount:</label>
                    <input type="text" id="withdrawalAmount" name="withdrawalAmount" placeholder="Enter withdrawal amount" />
                </div>
            </div>

            <!-- Modify Bank Account Form -->
            <div id="modifyBankAccountForm" class="additional-form">
                <div class="form-group">
                    <label>Game ID Screenshot:</label>
                    <div class="file-input-wrapper">
                        <div class="file-input-button">
                            <i class="fas fa-cloud-upload-alt"></i> Upload screenshot
                            <div class="file-name" id="screenshotName">No file chosen</div>
                        </div>
                        <input type="file" id="screenshot" name="screenshot" onchange="showFileName(this, 'screenshotName')" />
                    </div>
                </div>
                <div class="form-group">
                    <label>ID Proof:</label>
                    <div class="file-input-wrapper">
                        <div class="file-input-button">
                            <i class="fas fa-cloud-upload-alt"></i> Upload ID card
                            <div class="file-name" id="identificationCardName">No file chosen</div>
                        </div>
                        <input type="file" id="identificationCard" name="identificationCard" onchange="showFileName(this, 'identificationCardName')" />
                    </div>
                </div>
                <div class="form-group">
                    <label>Bank Proof:</label>
                    <div class="file-input-wrapper">
                        <div class="file-input-button">
                            <i class="fas fa-cloud-upload-alt"></i> Upload bank details
                            <div class="file-name" id="bankAccountPhotoName">No file chosen</div>
                        </div>
                        <input type="file" id="bankAccountPhoto" name="bankAccountPhoto" onchange="showFileName(this, 'bankAccountPhotoName')" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="bankName">Bank Name:</label>
                    <input type="text" id="bankName" name="bankName" placeholder="Enter bank name" />
                </div>
                <div class="form-group">
                    <label for="bankAccountHolder">Account Holder Name:</label>
                    <input type="text" id="bankAccountHolder" name="bankAccountHolder" placeholder="As per bank records" />
                </div>
                <div class="form-group">
                    <label for="bankAccountNumber">Account Number:</label>
                    <input type="text" id="bankAccountNumber" name="bankAccountNumber" placeholder="Enter account number" />
                </div>
                <div class="form-group">
                    <label for="ifscCode">IFSC Code:</label>
                    <input type="text" id="ifscCode" name="ifscCode" placeholder="Enter IFSC code" />
                </div>
                <div class="form-group">
                    <label for="phoneNumber">Registered Phone:</label>
                    <input type="text" id="phoneNumber" name="phoneNumber" placeholder="Enter registered number" />
                </div>
            </div>

            <!-- Change Bank Account Form -->
            <div id="changeBankAccountForm" class="additional-form">
                <div class="form-group">
                    <label>Game ID Screenshot:</label>
                    <div class="file-input-wrapper">
                        <div class="file-input-button">
                            <i class="fas fa-cloud-upload-alt"></i> Upload screenshot
                            <div class="file-name" id="screenshotName2">No file chosen</div>
                        </div>
                        <input type="file" id="screenshot" name="screenshot" onchange="showFileName(this, 'screenshotName2')" />
                    </div>
                </div>
                <div class="form-group">
                    <label>ID Proof:</label>
                    <div class="file-input-wrapper">
                        <div class="file-input-button">
                            <i class="fas fa-cloud-upload-alt"></i> Upload ID card
                            <div class="file-name" id="identificationCardName2">No file chosen</div>
                        </div>
                        <input type="file" id="identificationCard" name="identificationCard" onchange="showFileName(this, 'identificationCardName2')" />
                    </div>
                </div>
                <div class="form-group">
                    <label>Old Bank Proof:</label>
                    <div class="file-input-wrapper">
                        <div class="file-input-button">
                            <i class="fas fa-cloud-upload-alt"></i> Upload old passbook
                            <div class="file-name" id="oldBankPassbookName">No file chosen</div>
                        </div>
                        <input type="file" id="oldBankPassbook" name="oldBankPassbook" onchange="showFileName(this, 'oldBankPassbookName')" />
                    </div>
                </div>
                <div class="form-group">
                    <label>New Bank Proof:</label>
                    <div class="file-input-wrapper">
                        <div class="file-input-button">
                            <i class="fas fa-cloud-upload-alt"></i> Upload new passbook
                            <div class="file-name" id="newBankPassbookName">No file chosen</div>
                        </div>
                        <input type="file" id="newBankPassbook" name="newBankPassbook" onchange="showFileName(this, 'newBankPassbookName')" />
                    </div>
                </div>
                <div class="form-group">
                    <label>Latest Deposit Proof:</label>
                    <div class="file-input-wrapper">
                        <div class="file-input-button">
                            <i class="fas fa-cloud-upload-alt"></i> Upload receipt
                            <div class="file-name" id="latestDepositProofName">No file chosen</div>
                        </div>
                        <input type="file" id="latestDepositProof" name="latestDepositProof" onchange="showFileName(this, 'latestDepositProofName')" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="bankName">New Bank Name:</label>
                    <input type="text" id="bankName" name="bankName" placeholder="Enter new bank name" />
                </div>
                <div class="form-group">
                    <label for="bankAccountHolder">Account Holder Name:</label>
                    <input type="text" id="bankAccountHolder" name="bankAccountHolder" placeholder="As per new bank records" />
                </div>
                <div class="form-group">
                    <label for="bankAccountNumber">New Account Number:</label>
                    <input type="text" id="bankAccountNumber" name="bankAccountNumber" placeholder="Enter new account number" />
                </div>
                <div class="form-group">
                    <label for="ifscCode">New IFSC Code:</label>
                    <input type="text" id="ifscCode" name="ifscCode" placeholder="Enter new IFSC code" />
                </div>
                <div class="form-group">
                    <label for="phoneNumber">Registered Phone:</label>
                    <input type="text" id="phoneNumber" name="phoneNumber" placeholder="Enter registered number" />
                </div>
                <div class="form-group">
                    <label for="reasonForChange">Reason for Change:</label>
                    <textarea id="reasonForChange" name="reasonForChange" placeholder="Explain why you need to change bank details" rows="3"></textarea>
                </div>
            </div>

            <!-- Wingo 1 Min Win Streak Bonus Form -->
            <div id="wingo1MinWinStreakBonusForm" class="additional-form">
                <div class="form-group">
                    <label for="winingStartPeriodNo">Start Period:</label>
                    <input type="text" id="winingStartPeriodNo" name="winingStartPeriodNo" placeholder="Start date/time of winning streak" />
                </div>
                <div class="form-group">
                    <label for="winingEndPeriodNo">End Period:</label>
                    <input type="text" id="winingEndPeriodNo" name="winingEndPeriodNo" placeholder="End date/time of winning streak" />
                </div>
                <div class="form-group">
                    <label for="consecutiveWinStreak">Win Streak Count:</label>
                    <input type="text" id="consecutiveWinStreak" name="consecutiveWinStreak" placeholder="Enter streak count (8/18/28/38/48)" />
                </div>
            </div>

            <button type="submit" class="btn">Submit Issue 🚀</button>
            <button type="button" class="btn" onclick="openPopup()">Check Status 🔍</button>
        </form>
    </div>

    <!-- Overlay for Popup -->
    <div id="overlay" class="overlay">
        <div class="popup">
            <div class="popup-close" onclick="closePopup()">×</div>
            <div class="header-title">Issue Tracker</div>
            <div class="form-group">
                <label for="accountSearch">Enter Game ID:</label>
                <input type="text" id="accountSearch" placeholder="Your Game ID" />
            </div>
            <div class="form-group">
                <label for="issueType">Filter by Issue:</label>
                <select id="issueType">
                    <option value="">All Issues</option>
                    <option value="depositNotReceived">💰 Deposit Issues</option>
                    <option value="withdrawalProblem">💸 Withdrawal Issues</option>
                    <option value="modifyBankAccount">🏦 Bank Modifications</option>
                    <option value="changeBankAccount">🔄 Bank Changes</option>
                    <option value="wingo1MinWinStreakBonus">🎰 Wingo Bonuses</option>
                </select>
            </div>
            <button onclick="searchIssues()" class="btn">Search Now 🔎</button>
            <div id="issueResults"></div>
        </div>
    </div>

    <script>
        function toggleAdditionalForm() {
            const forms = document.querySelectorAll('.additional-form');
            forms.forEach(form => {
                form.style.display = 'none';
                form.style.animation = 'none';
                setTimeout(() => form.style.animation = 'fadeIn 0.5s ease', 10);
            });
            
            const selectedIssue = document.getElementById('issue').value;
            const formToShow = document.getElementById(`${selectedIssue}Form`);
            if (formToShow) formToShow.style.display = 'block';
        }

        function showFileName(input, elementId) {
            const fileName = input.files[0] ? input.files[0].name : 'No file chosen';
            document.getElementById(elementId).textContent = fileName;
        }

        function confirmSubmission() {
            return confirm("Please confirm all details are correct before submitting. Continue?");
        }

        function openPopup() {
            document.getElementById('overlay').style.display = 'flex';
            document.getElementById('accountSearch').focus();
        }

        function closePopup() {
            document.getElementById('overlay').style.display = 'none';
        }

        function searchIssues() {
            const accountID = document.getElementById('accountSearch').value.trim();
            const issueType = document.getElementById('issueType').value;
            const issueResults = document.getElementById('issueResults');

            if (!accountID) {
                issueResults.innerHTML = '<div class="issue-card"><p>Please enter your Game ID to search</p></div>';
                return;
            }

            // Show loading state
            issueResults.innerHTML = '<div class="issue-card"><p>Searching for issues...</p></div>';

            // Simulate API call with timeout
            setTimeout(() => {
                // This is a mock response - replace with actual API call
                const mockIssues = [
                    {
                        issue_type: "Deposit Not Received",
                        amount_deposit: "₹5,000",
                        status: "Processing",
                        date: "2023-05-15"
                    },
                    {
                        issue_type: "Withdrawal Problem",
                        withdrawal_amount: "₹3,200",
                        status: "Resolved",
                        date: "2023-05-10"
                    }
                ];

                displayIssues(mockIssues.filter(issue => 
                    !issueType || 
                    (issueType === 'depositNotReceived' && issue.issue_type.includes('Deposit')) ||
                    (issueType === 'withdrawalProblem' && issue.issue_type.includes('Withdrawal'))
                ));
            }, 800);
        }

        function displayIssues(issues) {
            const issueResults = document.getElementById('issueResults');
            
            if (issues.length === 0) {
                issueResults.innerHTML = '<div class="issue-card"><p>No issues found for this account</p></div>';
                return;
            }

            issueResults.innerHTML = '';
            
            issues.forEach(issue => {
                const statusClass = issue.status.toLowerCase().includes('pending') ? 'status-pending' : 
                                  issue.status.toLowerCase().includes('resolved') ? 'status-resolved' : 
                                  'status-processing';
                
                const issueElement = document.createElement('div');
                issueElement.className = 'issue-card';
                issueElement.innerHTML = `
                    <p><strong>Issue:</strong> ${issue.issue_type}</p>
                    <p><strong>Amount:</strong> ${issue.amount_deposit || issue.withdrawal_amount || 'N/A'}</p>
                    <p><strong>Date:</strong> ${issue.date || 'N/A'}</p>
                    <p><strong>Status:</strong> <span class="${statusClass}">${issue.status}</span></p>
                `;
                issueResults.appendChild(issueElement);
            });
        }

        // Close popup when clicking outside
        document.getElementById('overlay').addEventListener('click', function(e) {
            if (e.target === this) {
                closePopup();
            }
        });
    </script>
</body>
</html>

<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out - IntecGIB</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .logout-container {
            background: white;
            padding: 3rem 2rem;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 400px;
            animation: slideIn 0.4s ease-out;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .logout-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.5s ease-out 0.2s both;
        }
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        .logout-icon svg {
            width: 50px;
            height: 50px;
            color: white;
        }
        .logout-message {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
            animation: fadeIn 0.6s ease-out 0.4s both;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        .logout-submessage {
            font-size: 0.95rem;
            color: #6b7280;
            margin-bottom: 2rem;
            animation: fadeIn 0.6s ease-out 0.5s both;
        }
        .spinner {
            width: 40px;
            height: 40px;
            margin: 1.5rem auto;
            border: 4px solid #e5e7eb;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .redirecting-text {
            font-size: 0.9rem;
            color: #9ca3af;
            animation: fadeIn 0.6s ease-out 0.6s both;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 21 14 21 14 3 10 3 10 21 4 21"></polyline>
            </svg>
        </div>
        <h1 class="logout-message">Logout made successfully!</h1>
        <p class="logout-submessage">Your session has been closed.</p>
        <div class="spinner"></div>
        <p class="redirecting-text">Redirecting to home...</p>
    </div>

    <script>
        setTimeout(function() {
            window.location.href = 'index.html';
        }, 2000);
    </script>
</body>
</html>
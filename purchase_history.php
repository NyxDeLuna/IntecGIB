<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php?redirect=purchase_history.php');
    exit;
}

$userName = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? '';

$orders = [];
$conn = mysqli_connect('localhost', 'root', '', 'intecgib_db');
if ($conn) {
    mysqli_set_charset($conn, 'utf8');
    if ($userEmail !== '') {
        $stmt = mysqli_prepare($conn, "SELECT reference_number, service_type, technicians, hours, service_date, service_time, total_amount, status, created_at, service_address FROM service_orders WHERE customer_email = ? ORDER BY created_at DESC");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $userEmail);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $reference, $serviceType, $technicians, $hours, $serviceDate, $serviceTime, $totalAmount, $status, $createdAt, $serviceAddress);
            while (mysqli_stmt_fetch($stmt)) {
                $orders[] = [
                    'reference' => $reference,
                    'service_type' => $serviceType,
                    'technicians' => $technicians,
                    'hours' => $hours,
                    'service_date' => $serviceDate,
                    'service_time' => $serviceTime,
                    'total_amount' => $totalAmount,
                    'status' => $status,
                    'created_at' => $createdAt,
                    'service_address' => $serviceAddress,
                ];
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History - IntecGIB</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .history-wrap { max-width: 1024px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 12px 24px rgba(0,0,0,.08); }
        .history-wrap h1 { margin-bottom: 1rem; }
        .history-note { margin-bottom: 1.5rem; color: #555; }
        .history-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .history-table th, .history-table td { padding: 12px 14px; border: 1px solid #e2e8f0; text-align: left; }
        .history-table th { background: #f5f7fb; color: #333; }
        .history-table tbody tr:nth-child(even) { background: #fafbfe; }
        .history-status { padding: 6px 10px; border-radius: 999px; color: #fff; display: inline-block; font-size: 0.9rem; }
        .status-paid { background: #16a34a; }
        .status-pending { background: #f59e0b; }
        .status-completed { background: #0ea5e9; }
        .status-cancelled { background: #ef4444; }
        .no-orders { padding: 2rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; color: #475569; }
        .small-text { color: #6b7280; font-size: 0.95rem; margin-top: 0.25rem; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.html" class="nav-logo">
                <img src="img/misc/logo_intecgib.png" alt="IntecGIB Logo">
            </a>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.html" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="about.html" class="nav-link">About</a></li>
                <li class="nav-item"><a href="residential.html" class="nav-link">Residential</a></li>
                <li class="nav-item"><a href="business.html" class="nav-link">Business</a></li>
                <li class="nav-item"><a href="projects.html" class="nav-link">Projects</a></li>
                <li class="nav-item"><a href="services.html" class="nav-link">Services</a></li>
                <li class="nav-item"><a href="contact.html" class="nav-link">Contact</a></li>
                <li class="nav-item" id="adminNavLink" style="display: none;"><a href="admin.php" class="nav-link">Admin</a></li>
                <li class="nav-item user-menu">
                    <div class="user-dropdown" id="userDropdown">
                        <button class="user-button" id="userButton">
                            <span id="userName"><?= htmlspecialchars($userName); ?></span>
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor" class="dropdown-arrow">
                                <path d="M6 8L2 4h8z"/>
                            </svg>
                        </button>
                        <div class="dropdown-menu" id="dropdownMenu">
                            <a href="profile.php" class="dropdown-item">Profile</a>
                            <a href="purchase_history.php" class="dropdown-item">Purchase History</a>
                            <a href="logout.php" class="dropdown-item">Logout</a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <div class="history-wrap">
            <h1>Purchase History</h1>
            <p class="history-note">These are the purchases associated with the currently logged-in user session. Only orders made with your account email are shown.</p>

            <?php if (empty($orders)): ?>
                <div class="no-orders">
                    <strong>No purchase history found.</strong>
                    <p class="small-text">If you recently made a booking, it may take a moment to appear. Make sure you completed the payment using the same account email.</p>
                </div>
            <?php else: ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Service</th>
                            <th>Qty</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Booking Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['reference']); ?></td>
                                <td><?= htmlspecialchars($order['service_date'] . ' ' . $order['service_time']); ?></td>
                                <td><?= htmlspecialchars(ucfirst($order['service_type'])); ?></td>
                                <td><?= htmlspecialchars($order['technicians'] . ' tech / ' . $order['hours'] . ' hrs'); ?></td>
                                <td>£<?= htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                                <td><span class="history-status status-<?= htmlspecialchars($order['status']); ?>"><?= htmlspecialchars(ucfirst($order['status'])); ?></span></td>
                                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($order['created_at']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <script src="js/admin-nav.js"></script>
</body>
</html>

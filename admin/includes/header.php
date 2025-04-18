<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Shree Ram Janmabhoomi</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/ram/assets/css/admin.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/ram/admin/dashboard.php">
                <img src="/ram/assets/logo.png" alt="Logo" height="30">
                Admin Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/ram/admin/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ram/admin/tours.php">Tours</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ram/admin/bookings.php">Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/ram/admin/users.php">Users</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/ram/admin/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav> 
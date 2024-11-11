<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./css/frame.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap">
    <title>Asset Exchange Admin Website</title>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">
            <img src="./assets/logo.png" alt="Logo">
            <span class="txt_AssetEx">AssetEx.</span>
        </div>
        <div class="search-bar">
            <img class="ic_search" src="./assets/search.png"/>
            <input type="text" placeholder="Try searching 'demographic'">
        </div>
        <div class="user-settings">
            <img class="ic_profile" src="assets/profile-icon.png" alt="Profile">
            <span class="txt_Username">Mark Echo</span>
            <img class="ic_settings" src="assets/settings-icon.png" alt="Settings">
        </div>
    </div>

    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h1>Welcome to AssetEx Admin System</h1>
            <img class="waving_hand" src="./assets/waving-hand(1) 1.png" alt="waving-hand">
            <ul class="menu">
                <div class="nav-section">General</div>
                <li><a href="asset_exchange_admin.php">Dashboard</a></li>
                <li><a href="AssetEx._Storage.html">Storage</a></li>
                <li><a href="AssetEx._PushNotif.html">Push Notification</a></li>
                <div class="nav-section">Statistics</div>
                <li><a href="AssetEx._SalesReport.html">Sales Report</a></li>
                <div class="nav-section">Access</div>
                <li><a href="AssetEx._UserControl.html">User Control</a></li>
                <li><a href="#">Reports</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">

            <div class="upper_part">
                <div class="july_reports">
                    <h2>July Report</h2>
                    <h3>Revenue</h3>
                    <h1>$524,699</h1>

                    <div class="updates">
                        <div class="increase">
                            <img src="./assets/arrow-up.png" alt="arrow-up">
                            <p>5</p>
                        </div>
                        <div class="estimate">
                            <p>$25,043 from last month</p>
                        </div>
                    </div>
                </div>

                <div class="Revenue_container">
                    <h2>Revenue</h2>
                    
                    <div class="cards">
                        <div class="card_Top-users">
                            <div class="card-content">
                                <div class="top-user">Top user</div>
                                <p class="number">45<span>Projects</span></p>
                                <div class="icon-box">
                                    <img src="./assets/ic_john.png" alt="ic_john">
                                    <p class="username">John</p>
                                    <div class="arrow">&rsaquo;</div>
                                </div>
                            </div>
                        </div>

                        <div class="card_Top-ads">
                            <div class="card-content">
                                <div class="top-user">Top ads convert</div>
                                <p class="number">70K<span>clicks</span></p>
                                <div class="icon-box">
                                    <img src="./assets/ic_facebook.png" alt="ic_facebook">
                                    <p class="username">facebook</p>
                                    <div class="arrow-fb">&rsaquo;</div>
                                </div>
                            </div>
                        </div>

                        <div class="card_Top-influencers">
                            <div class="card-content">
                                <div class="top-user">Top influencer</div>
                                <p class="number">500<span>referrals</span></p>
                                <div class="icon-box">
                                    <img src="./assets/ic_mika.png" alt="ic_mika">
                                    <p class="username">Mika</p>
                                    <div class="arrow">&rsaquo;</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tag-container">
                <div class="tag">#AssetManagement</div>
                <div class="tag">#DesignApproval</div>
                <div class="tag">#VersionControl</div>
                <div class="tag">#PortfolioShowcase</div>
                <div class="tag">#SeamlessApproval</div>
            </div>

            <div class="charts">
                <div class="chart" id="pie-chart">
                    <canvas id="pie"></canvas>
                </div>

                <div class="chart">
                    <div class="bar_chart-container">
                        <img class="bar-chart-img" src="./assets/bar_chart.png" alt="bar_chart">
                    </div>

                    <div class="txt-below-bar">
                        <div class="grey-txt-how-ads">How ads performs</div>
                        <div class="social-media-txt">Social Media</div>
                    </div>
                </div>

                <div class="chart">
                    <div class="storage-container" onclick="window.location.href='AssetEx._Storage.html';" style="cursor: pointer;">
                        <img class="storage-img" src="./assets/storage-img.png" alt="storage">
                    </div>

                    <div class="txt-below-storage">
                        <div class="grey-txt-storage">Storage</div>
                        <div class="TB"><span>3TB</span>/6TB</div>
                    </div>    
                </div>
            </div>

            <div class="lower-grid">
                <div class="chart_1">
                    <div class="push-notif-container">
                        <img src="./assets/push-notif.png" alt="push-notif">
                    </div>
                </div>

                <div class="chart_2">
                    <div class="user-control-container">
                        <img src="./assets/user_control.png" alt="user_control">
                    </div>
                </div>

                <div class="active_users">
                    <div class="active_users-container">
                        <img src="./assets/Active_users.png" alt="active_users">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="chart1.js"></script>
    
</body>
</html>

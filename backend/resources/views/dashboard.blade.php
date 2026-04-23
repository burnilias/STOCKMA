<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Stock Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 {
            font-size: 1.5rem;
        }
        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .btn {
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }
        .btn:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }
        .stat-card.primary { border-left: 4px solid #667eea; }
        .stat-card.success { border-left: 4px solid #10b981; }
        .stat-card.warning { border-left: 4px solid #f59e0b; }
        .stat-card.danger { border-left: 4px solid #ef4444; }
        .section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .section h2 {
            margin-bottom: 1rem;
            color: #333;
        }
        .loading {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
        }
        .movement-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        .movement-item:last-child {
            border-bottom: none;
        }
        .movement-type {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .movement-type.entry {
            background: #d4edda;
            color: #155724;
        }
        .movement-type.exit {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>📦 Stock Management</h1>
        <div class="user-info">
            <span id="userName">Loading...</span>
            <button class="btn" onclick="logout()">Logout</button>
        </div>
    </nav>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card primary">
                <h3>Total Products</h3>
                <div class="value" id="totalProducts">-</div>
            </div>
            <div class="stat-card success">
                <h3>Total Value</h3>
                <div class="value" id="totalValue">-</div>
            </div>
            <div class="stat-card warning">
                <h3>Low Stock Alerts</h3>
                <div class="value" id="alertCount">-</div>
            </div>
            <div class="stat-card danger">
                <h3>Out of Stock</h3>
                <div class="value" id="outOfStock">-</div>
            </div>
        </div>

        <div class="section">
            <h2>Recent Stock Movements</h2>
            <div id="recentMovements" class="loading">Loading...</div>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('token');
        const user = JSON.parse(localStorage.getItem('user') || '{}');

        if (!token) {
            window.location.href = '/';
        }

        document.getElementById('userName').textContent = user.nom || 'User';

        async function loadDashboard() {
            try {
                const response = await fetch('/api/reports/dashboard', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const kpis = data.data.kpis;
                    document.getElementById('totalProducts').textContent = kpis.total_products;
                    document.getElementById('totalValue').textContent = '$' + kpis.valeur_totale.toFixed(2);
                    document.getElementById('alertCount').textContent = kpis.alertes_count;
                    document.getElementById('outOfStock').textContent = kpis.out_of_stock_count;

                    const movements = data.data.recent_movements;
                    const movementsHtml = movements.map(movement => `
                        <div class="movement-item">
                            <div>
                                <strong>${movement.product_name || 'Unknown Product'}</strong>
                                <br>
                                <small>${movement.user_name || 'Unknown User'} - ${movement.date}</small>
                            </div>
                            <div>
                                <span class="movement-type ${movement.type}">${movement.type.toUpperCase()}</span>
                                <strong>${movement.quantity}</strong>
                            </div>
                        </div>
                    `).join('');

                    document.getElementById('recentMovements').innerHTML = movementsHtml || '<p>No recent movements</p>';
                } else {
                    throw new Error(data.message || 'Failed to load dashboard');
                }
            } catch (error) {
                document.getElementById('recentMovements').innerHTML = `<div class="error">Error: ${error.message}</div>`;
            }
        }

        function logout() {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/';
        }

        // Load dashboard on page load
        loadDashboard();
    </script>
</body>
</html>

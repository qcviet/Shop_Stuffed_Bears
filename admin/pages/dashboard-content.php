<div class="row">
    <div class="col-md-8">
        <h5 class="mb-3">Recent Activity</h5>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th>User</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>New order placed</td>
                        <td>user123</td>
                        <td>2 minutes ago</td>
                        <td><span class="badge bg-success">Completed</span></td>
                    </tr>
                    <tr>
                        <td>Product updated</td>
                        <td>admin</td>
                        <td>15 minutes ago</td>
                        <td><span class="badge bg-info">Updated</span></td>
                    </tr>
                    <tr>
                        <td>New user registered</td>
                        <td>newuser456</td>
                        <td>1 hour ago</td>
                        <td><span class="badge bg-warning">Pending</span></td>
                    </tr>
                    <tr>
                        <td>Payment received</td>
                        <td>customer789</td>
                        <td>2 hours ago</td>
                        <td><span class="badge bg-success">Paid</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="col-md-4">
        <h5 class="mb-3">Quick Actions</h5>
        <div class="d-grid gap-2">
            <a href="index.php?page=products" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle"></i> Add New Product
            </a>
            <a href="index.php?page=orders" class="btn btn-outline-success">
                <i class="bi bi-eye"></i> View Orders
            </a>
            <a href="index.php?page=users" class="btn btn-outline-info">
                <i class="bi bi-people"></i> Manage Users
            </a>
            <a href="index.php?page=settings" class="btn btn-outline-secondary">
                <i class="bi bi-gear"></i> System Settings
            </a>
        </div>
        
        <h5 class="mb-3 mt-4">System Status</h5>
        <div class="list-group list-group-flush">
            <div class="list-group-item d-flex justify-content-between align-items-center">
                Database
                <span class="badge bg-success">Online</span>
            </div>
            <div class="list-group-item d-flex justify-content-between align-items-center">
                Web Server
                <span class="badge bg-success">Online</span>
            </div>
            <div class="list-group-item d-flex justify-content-between align-items-center">
                Email Service
                <span class="badge bg-warning">Warning</span>
            </div>
            <div class="list-group-item d-flex justify-content-between align-items-center">
                Backup System
                <span class="badge bg-success">Online</span>
            </div>
        </div>
    </div>
</div> 
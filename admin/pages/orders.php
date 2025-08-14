<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Order Management</h5>
    <div>
        <button class="btn btn-outline-secondary me-2">
            <i class="bi bi-download"></i> Export
        </button>
        <button class="btn btn-primary">
            <i class="bi bi-eye"></i> View All
        </button>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover" id="ordersTable">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Orders will be loaded dynamically -->
        </tbody>
    </table>
</div>

<div id="ordersPagination"></div>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary" id="totalOrders">0</h5>
                <p class="card-text">Total Orders</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-success" id="completedOrders">0</h5>
                <p class="card-text">Completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning" id="processingOrders">0</h5>
                <p class="card-text">Processing</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-info" id="shippedOrders">0</h5>
                <p class="card-text">Shipped</p>
            </div>
        </div>
    </div>
</div> 
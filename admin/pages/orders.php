<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Order Management</h5>
    <div>
        <button class="btn btn-outline-secondary me-2">
            <i class="bi bi-download"></i> Export
        </button>
        <button class="btn btn-warning me-2" id="filterPendingOrdersBtn">
            <i class="bi bi-hourglass-split"></i> Pending Confirmations
        </button>
        <button class="btn btn-primary" id="viewAllOrdersBtn">
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
                <th>Payment</th>
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

<!-- Update Order Modal -->
<div class="modal fade" id="orderUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="orderUpdateForm">
                    <input type="hidden" name="order_id" />
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="Chờ xác nhận">Chờ xác nhận</option>
                            <option value="Đã xác nhận">Đã xác nhận</option>
                            <option value="Đang giao">Đang giao</option>
                            <option value="Đã giao">Đã giao</option>
                            <option value="Đã hủy">Đã hủy</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment</label>
                        <select class="form-select" name="payment_status">
                            <option value="Chưa thanh toán">Chưa thanh toán</option>
                            <option value="Đã thanh toán">Đã thanh toán</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveOrderUpdateBtn">Save</button>
            </div>
        </div>
    </div>
    </div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details <span class="text-muted od-order-id"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div><strong>Customer:</strong> <span class="od-customer-name"></span></div>
                        <div><strong>Email:</strong> <span class="od-customer-email"></span></div>
                        <div><strong>Phone:</strong> <span class="od-customer-phone"></span></div>
                        <div><strong>Address:</strong> <span class="od-customer-address"></span></div>
                    </div>
                    <div class="col-md-6">
                        <div><strong>Date:</strong> <span class="od-order-date"></span></div>
                        <div><strong>Status:</strong> <span class="od-status"></span></div>
                        <div><strong>Payment:</strong> <span class="od-payment"></span></div>
                        <div><strong>Total:</strong> <span class="od-total"></span></div>
                    </div>
                </div>
                <div class="mb-2">
                    <strong>Items:</strong> <span class="od-items-count">0</span>
                    <span class="ms-3"><strong>Total quantity:</strong> <span class="od-total-quantity">0</span></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th style="width:70px">Image</th>
                                <th>Product</th>
                                <th>Size</th>
                                <th>Color</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="od-items-tbody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
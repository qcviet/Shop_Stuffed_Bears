// Admin CRUD Operations JavaScript
class AdminCRUD {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.bindImagePreview();
        this.bindPriceFormatting();
        this.loadData();
    }

    bindEvents() {
        // Product events
        $(document).on('click', '.add-product-btn', () => this.showProductModal());
        $(document).on('click', '.edit-product-btn', (e) => this.editProduct(e));
        $(document).on('click', '.delete-product-btn', (e) => this.deleteProduct(e));
        $(document).on('click', '.expand-variants-btn', (e) => this.toggleVariantsDetails(e));
        $(document).on('submit', '#productForm', (e) => this.saveProduct(e));
        // Variant events
        // Inline variant events
        $(document).on('click', '#addVariantRowInline', () => this.addVariantRowInline());
        $(document).on('click', '.variant-save-btn', (e) => this.saveVariantRowInline(e));
        $(document).on('click', '.variant-delete-btn', (e) => this.deleteVariantRowInline(e));

        // Category events
        $(document).on('click', '.add-category-btn', () => this.showCategoryModal());
        $(document).on('click', '.edit-category-btn', (e) => this.editCategory(e));
        $(document).on('click', '.delete-category-btn', (e) => this.deleteCategory(e));
        $(document).on('submit', '#categoryForm', (e) => this.saveCategory(e));

        // User events
        $(document).on('click', '.add-user-btn', () => this.showUserModal());
        $(document).on('click', '.edit-user-btn', (e) => this.editUser(e));
        $(document).on('click', '.delete-user-btn', (e) => this.deleteUser(e));
        $(document).on('click', '.toggle-user-status-btn', (e) => this.toggleUserStatus(e));
        $(document).on('submit', '#userForm', (e) => this.saveUser(e));

        // Order events
        $(document).on('click', '.update-order-status', (e) => this.updateOrderStatus(e));
        $(document).on('click', '.delete-order-btn', (e) => this.deleteOrder(e));

        // Orders history filter
        $(document).on('submit', '#ordersHistoryFilter', (e) => this.applyOrdersHistoryFilter(e));
    }

    // Product Methods
    showProductModal(product = null) {
        const modal = $('#productModal');
        const form = $('#productForm');
        
        if (product) {
            form.find('[name=product_id]').val(product.product_id);
            form.find('[name=product_name]').val(product.product_name);
            form.find('[name=category_id]').val(product.category_id);
            form.find('[name=description]').val(product.description);
            modal.find('.modal-title').text('Edit Product');
            const base = (typeof BASE_URL !== 'undefined' && BASE_URL) ? BASE_URL : (window.BASE_URL || '');
            const images = product.images || (product.image_url ? [product.image_url] : []);
            const list = $('#imageList');
            list.empty();
            images.forEach((url, idx) => {
                const fullUrl = url && url.startsWith('http') ? url : (url ? `${base}/${url}` : `${base}/assets/images/sp1.jpeg`);
                list.append(`<div class="position-relative"><img src="${fullUrl}" style="height:60px;width:60px;object-fit:cover;border-radius:6px;" /><button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 delete-image-btn" data-url="${url}" title="Delete"><i class="bi bi-x"></i></button></div>`);
            });
        } else {
            form[0].reset();
            form.find('[name=product_id]').val('');
            modal.find('.modal-title').text('Add New Product');
            $('#imageList').empty();
        }
        
        modal.modal('show');
        // Load inline variants table if editing existing product
        const pid = form.find('[name=product_id]').val();
        if (pid) {
            this.loadVariantsInline(pid);
            $('#variantsInlineHint').hide();
        } else {
            $('#variantsTableInline tbody').empty();
            $('#variantsInlineHint').show();
        }
    }

    async editProduct(e) {
        const productId = $(e.currentTarget).data('id');
        try {
            const response = await $.get('actions/product_actions.php', { action: 'get', product_id: productId });
            if (response.success) {
                this.showProductModal(response.data);
            } else {
                this.showAlert('Error', response.message, 'error');
            }
        } catch (error) {
            this.showAlert('Error', 'Failed to load product data', 'error');
        }
    }

    async deleteProduct(e) {
        e.preventDefault();
        e.stopPropagation();
        const $btn = $(e.currentTarget);
        const productId = $btn.data('id');
        const productName = $btn.closest('tr').find('td').eq(1).text().trim();
        
        if (confirm(`Are you sure you want to delete "${productName}"?`)) {
            try {
                const response = await $.post('actions/product_actions.php', {
                    action: 'delete',
                    product_id: productId
                });
                
                if (response.success) {
                    this.showAlert('Success', response.message, 'success');
                    this.loadProducts();
                } else {
                    this.showAlert('Error', response.message, 'error');
                }
            } catch (error) {
                this.showAlert('Error', 'Failed to delete product', 'error');
            }
        }
    }

    async saveProduct(e) {
        e.preventDefault();
        const form = $(e.target);
        const formData = new FormData(form[0]);
        formData.append('action', form.find('[name=product_id]').val() ? 'update' : 'create');

        try {
            const response = await $.ajax({
                url: 'actions/product_actions.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false
            });

            if (response.success) {
                this.showAlert('Success', response.message, 'success');
                $('#productModal').modal('hide');
                this.loadProducts();
            } else {
                this.showAlert('Error', response.message, 'error');
            }
        } catch (error) {
            this.showAlert('Error', 'Failed to save product', 'error');
        }
    }

    // Category Methods
    showCategoryModal(category = null) {
        const modal = $('#categoryModal');
        const form = $('#categoryForm');
        
        if (category) {
            form.find('[name=category_id]').val(category.category_id);
            form.find('[name=category_name]').val(category.category_name);
            form.find('[name=description]').val(category.description);
            modal.find('.modal-title').text('Edit Category');
        } else {
            form[0].reset();
            form.find('[name=category_id]').val('');
            modal.find('.modal-title').text('Add New Category');
        }
        
        modal.modal('show');
    }

    async editCategory(e) {
        const categoryId = $(e.currentTarget).data('id');
        try {
            const response = await $.get('actions/category_actions.php', { action: 'get', category_id: categoryId });
            if (response.success) {
                this.showCategoryModal(response.data);
            } else {
                this.showAlert('Error', response.message, 'error');
            }
        } catch (error) {
            this.showAlert('Error', 'Failed to load category data', 'error');
        }
    }

    async deleteCategory(e) {
        const categoryId = $(e.currentTarget).data('id');
        const categoryName = $(e.currentTarget).data('name');
        
        if (confirm(`Are you sure you want to delete "${categoryName}"?`)) {
            try {
                const response = await $.post('actions/category_actions.php', {
                    action: 'delete',
                    category_id: categoryId
                });
                
                if (response.success) {
                    this.showAlert('Success', response.message, 'success');
                    this.loadCategories();
                } else {
                    this.showAlert('Error', response.message, 'error');
                }
            } catch (error) {
                this.showAlert('Error', 'Failed to delete category', 'error');
            }
        }
    }

    async saveCategory(e) {
        e.preventDefault();
        const form = $(e.target);
        const formData = new FormData(form[0]);
        formData.append('action', form.find('[name=category_id]').val() ? 'update' : 'create');

        try {
            const response = await $.ajax({
                url: 'actions/category_actions.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false
            });

            if (response.success) {
                this.showAlert('Success', response.message, 'success');
                $('#categoryModal').modal('hide');
                this.loadCategories();
            } else {
                this.showAlert('Error', response.message, 'error');
            }
        } catch (error) {
            this.showAlert('Error', 'Failed to save category', 'error');
        }
    }

    // User Methods
    showUserModal(user = null) {
        const modal = $('#userModal');
        const form = $('#userForm');
        
        if (user) {
            form.find('[name=user_id]').val(user.user_id);
            form.find('[name=username]').val(user.username);
            form.find('[name=email]').val(user.email);
            form.find('[name=full_name]').val(user.full_name);
            form.find('[name=phone]').val(user.phone);
            form.find('[name=address]').val(user.address);
            form.find('[name=role]').val(user.role);
            form.find('[name=password]').prop('required', false);
            modal.find('.modal-title').text('Edit User');
        } else {
            form[0].reset();
            form.find('[name=user_id]').val('');
            form.find('[name=password]').prop('required', true);
            modal.find('.modal-title').text('Add New User');
        }
        
        modal.modal('show');
    }

    async editUser(e) {
        const userId = $(e.currentTarget).data('id');
        try {
            const response = await $.get('actions/user_actions.php', { action: 'get', user_id: userId });
            if (response.success) {
                this.showUserModal(response.data);
            } else {
                this.showAlert('Error', response.message, 'error');
            }
        } catch (error) {
            this.showAlert('Error', 'Failed to load user data', 'error');
        }
    }

    async deleteUser(e) {
        const userId = $(e.currentTarget).data('id');
        const userName = $(e.currentTarget).data('name');
        
        if (confirm(`Are you sure you want to delete "${userName}"? This will also delete all their orders and cart data.`)) {
            try {
                const response = await $.post('actions/user_actions.php', {
                    action: 'delete',
                    user_id: userId
                });
                
                if (response.success) {
                    this.showAlert('Success', response.message, 'success');
                    // Update user counts if provided
                    if (response.status_counts) {
                        $('#totalUsers').text(response.status_counts.total || 0);
                        $('#activeUsers').text(response.status_counts.active || 0);
                        $('#inactiveUsers').text(response.status_counts.inactive || 0);
                        $('#pendingUsers').text(response.status_counts.pending || 0);
                    }
                    this.loadUsers();
                } else {
                    this.showAlert('Error', response.message, 'error');
                }
            } catch (error) {
                console.error('Delete user error:', error);
                this.showAlert('Error', 'Failed to delete user. Please try again.', 'error');
            }
        }
    }

    async toggleUserStatus(e) {
        const $btn = $(e.currentTarget);
        const userId = $btn.data('id');
        const current = $btn.data('status');
        const next = current === 'active' ? 'inactive' : 'active';
        try {
            const res = await $.post('actions/user_actions.php', { action: 'update_status', user_id: userId, status: next });
            if (res.success) {
                // update button and badge inline without reload
                $btn.data('status', next);
                const $row = $btn.closest('tr');
                const $badge = $row.find('.user-status-badge');
                $badge.removeClass('bg-success bg-danger bg-warning').addClass(next === 'active' ? 'bg-success' : 'bg-danger').text(next === 'active' ? 'Active' : 'Inactive');
                // update counters if provided
                if (res.status_counts) {
                    $('#totalUsers').text(res.status_counts.total || 0);
                    $('#activeUsers').text(res.status_counts.active || 0);
                    $('#inactiveUsers').text(res.status_counts.inactive || 0);
                    $('#pendingUsers').text(res.status_counts.pending || 0);
                }
            } else {
                this.showAlert('Error', res.message, 'error');
            }
        } catch (err) {
            this.showAlert('Error', 'Failed to update status', 'error');
        }
    }

    async saveUser(e) {
        e.preventDefault();
        const form = $(e.target);
        const formData = new FormData(form[0]);
        formData.append('action', form.find('[name=user_id]').val() ? 'update' : 'create');

        try {
            const response = await $.ajax({
                url: 'actions/user_actions.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false
            });

            if (response.success) {
                this.showAlert('Success', response.message, 'success');
                $('#userModal').modal('hide');
                this.loadUsers();
            } else {
                this.showAlert('Error', response.message, 'error');
            }
        } catch (error) {
            this.showAlert('Error', 'Failed to save user', 'error');
        }
    }

    // Order Methods
    async updateOrderStatus(e) {
        const orderId = $(e.target).data('id');
        const currentStatus = $(e.target).data('status');
        const newStatus = prompt('Enter new status (Chờ xác nhận/Đang giao/Đã giao/Đã hủy):', currentStatus);
        
        if (newStatus && newStatus !== currentStatus) {
            try {
                const response = await $.post('actions/order_actions.php', {
                    action: 'update_status',
                    order_id: orderId,
                    status: newStatus
                });
                
                if (response.success) {
                    this.showAlert('Success', response.message, 'success');
                    this.loadOrders();
                } else {
                    this.showAlert('Error', response.message, 'error');
                }
            } catch (error) {
                this.showAlert('Error', 'Failed to update order status', 'error');
            }
        }
    }

    async deleteOrder(e) {
        const orderId = $(e.target).data('id');
        
        if (confirm('Are you sure you want to delete this order?')) {
            try {
                const response = await $.post('actions/order_actions.php', {
                    action: 'delete',
                    order_id: orderId
                });
                
                if (response.success) {
                    this.showAlert('Success', response.message, 'success');
                    this.loadOrders();
                } else {
                    this.showAlert('Error', response.message, 'error');
                }
            } catch (error) {
                this.showAlert('Error', 'Failed to delete order', 'error');
            }
        }
    }

    // Data Loading Methods
    async loadData() {
        const currentPage = this.getCurrentPage();
        
        switch (currentPage) {
            case 'products':
                await this.loadProducts();
                break;
            case 'categories':
                await this.loadCategories();
                break;
            case 'users':
                await this.loadUsers();
                break;
            case 'orders':
                await this.loadOrders();
                break;
        }
    }

    async loadProducts() {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = parseInt(urlParams.get('p') || '1', 10);
            const response = await $.get('actions/product_actions.php', { action: 'list', page: currentPage, limit: 10 });
            if (response.success) {
                const products = (response.data || []);
                this.renderProductsTable(products);
                this.renderPagination(response.pages || 1, response.current_page || 1, 'products');
            }
        } catch (error) {
            console.error('Failed to load products:', error);
        }
    }

    async loadCategories() {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = parseInt(urlParams.get('p') || '1', 10);
            const response = await $.get('actions/category_actions.php', { action: 'list', page: currentPage, limit: 10 });
            if (response.success) {
                const categories = response.data || [];
                this.renderCategoriesTable(categories);
                // Update stats
                const totalCategories = response.total || categories.length;
                const totalProducts = categories.reduce((sum, c) => sum + (parseInt(c.product_count, 10) || 0), 0);
                const avgProducts = totalCategories > 0 ? Math.round(totalProducts / totalCategories) : 0;
                $('#totalCategories').text(totalCategories);
                $('#activeCategories').text(totalCategories); // No status field, assume all active
                $('#totalProducts').text(totalProducts);
                $('#avgProducts').text(avgProducts);
                // Render pagination for categories
                this.renderPagination(response.pages || 1, response.current_page || 1, 'categories');
            }
        } catch (error) {
            console.error('Failed to load categories:', error);
        }
    }

    async loadUsers() {
        try {
            const response = await $.get('actions/user_actions.php', { action: 'list' });
            if (response.success) {
                this.renderUsersTable(response.data);
                this.renderPagination(response.pages, response.current_page, 'users');
                // Update counters
                const sc = response.status_counts || {};
                if (response.total !== undefined) $('#totalUsers').text(response.total);
                if (sc.active !== undefined) $('#activeUsers').text(sc.active);
                if (sc.inactive !== undefined) $('#inactiveUsers').text(sc.inactive);
                if (sc.pending !== undefined) $('#pendingUsers').text(sc.pending);
            }
        } catch (error) {
            console.error('Failed to load users:', error);
        }
    }

    async loadOrders() {
        try {
            const response = await $.get('actions/order_actions.php', { action: 'list' });
            if (response.success) {
                this.renderOrdersTable(response.data);
                this.renderPagination(response.pages, response.current_page, 'orders');
                // Load history chart default (last 12 months)
                await this.loadOrdersHistory();
            }
        } catch (error) {
            console.error('Failed to load orders:', error);
        }
    }

    async loadOrdersHistory(fromYm = null, toYm = null) {
        try {
            const params = { action: 'monthly_report' };
            if (fromYm) params.from = fromYm;
            if (toYm) params.to = toYm;
            const res = await $.get('actions/order_actions.php', params);
            if (!res.success) return;
            const rows = res.data || [];
            // Update table
            const tbody = $('#ordersMonthlyTbody');
            tbody.empty();
            rows.forEach(r => {
                const rev = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(Number(r.revenue || 0));
                tbody.append(`<tr><td>${r.ym}</td><td>${rev}</td><td>${r.orders}</td></tr>`);
            });
            // Update chart
            const labels = rows.map(r => r.ym);
            const revenues = rows.map(r => Number(r.revenue || 0));
            const orders = rows.map(r => Number(r.orders || 0));
            this.renderOrdersHistoryChart(labels, revenues, orders);
        } catch (err) {
            console.error('Failed to load orders history:', err);
        }
    }

    applyOrdersHistoryFilter(e) {
        e.preventDefault();
        const fromYm = $('#fromMonth').val() || null;
        const toYm = $('#toMonth').val() || null;
        this.loadOrdersHistory(fromYm, toYm);
    }

    renderOrdersHistoryChart(labels, revenues, orders) {
        const ctx = document.getElementById('ordersMonthlyChart');
        if (!ctx) return;
        if (this._ordersMonthlyChart) {
            this._ordersMonthlyChart.destroy();
        }
        this._ordersMonthlyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { type: 'bar', label: 'Revenue (VND)', data: revenues, backgroundColor: 'rgba(75, 192, 192, 0.4)', borderColor: 'rgba(75, 192, 192, 1)', borderWidth: 1, yAxisID: 'y' },
                    { type: 'line', label: 'Orders', data: orders, borderColor: 'rgba(54, 162, 235, 1)', backgroundColor: 'rgba(54, 162, 235, 0.2)', tension: 0.3, yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: v => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(v) } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } }
                }
            }
        });
    }

    // Rendering Methods
    renderProductsTable(products) {
        const tbody = $('#productsTable tbody');
        tbody.empty();
        
        products.forEach(product => {
            const base = (typeof BASE_URL !== 'undefined' && BASE_URL) ? BASE_URL : (window.BASE_URL || '');
            const imageUrl = product.image_url ? (product.image_url.startsWith('http') ? product.image_url : `${base}/${product.image_url}`) : `${base}/assets/images/sp1.jpeg`;
            // Build prices list display from lowest to highest
            let pricesDisplay = '—';
            if (product.price_list) {
                const prices = String(product.price_list)
                    .split(',')
                    .map(p => Number(p))
                    .filter(n => !isNaN(n))
                    .sort((a, b) => a - b);
                if (prices.length > 0) {
                    pricesDisplay = prices.map(n => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(n)).join(', ');
                }
            }
            const row = `
                <tr>
                    <td>${product.product_id}</td>
                    <td><img src="${imageUrl}" alt="${product.product_name}" style="height:40px;width:40px;object-fit:cover;border-radius:6px;" /></td>
                    <td>${product.product_name}</td>
                    <td>${product.category_name || 'N/A'}</td>
                    <td>${this.renderMinMaxPrice(product)}</td>
                    <td>${product.stock}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary edit-product-btn" data-id="${product.product_id}"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger delete-product-btn" data-id="${product.product_id}" data-name="${product.product_name}"><i class="bi bi-trash"></i></button>
                        <button class="btn btn-sm btn-outline-secondary expand-variants-btn" data-id="${product.product_id}" title="View variants"><i class="bi bi-chevron-down"></i></button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    renderMinMaxPrice(product) {
        // prefer price_list; else derive from variants if attached
        let prices = [];
        if (product.price_list) {
            prices = String(product.price_list)
                .split(',')
                .map(p => Number(p))
                .filter(n => !isNaN(n));
        } else if (product.variants && product.variants.length) {
            prices = product.variants.map(v => Number(v.price || 0)).filter(n => !isNaN(n));
        } else if (product.price != null) {
            prices = [Number(product.price)];
        }
        if (prices.length === 0) return '—';
        prices.sort((a, b) => a - b);
        const fmt = n => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(n);
        if (prices.length === 1) return fmt(prices[0]);
        return `${fmt(prices[0])} - ${fmt(prices[prices.length - 1])}`;
    }

    async toggleVariantsDetails(e) {
        const $btn = $(e.currentTarget);
        const productId = $btn.data('id');
        const $row = $btn.closest('tr');
        const $next = $row.next('.product-variants-row');
        if ($next.length) {
            $next.remove();
            $btn.find('i').removeClass('bi-chevron-up').addClass('bi-chevron-down');
            return;
        }
        try {
            const res = await $.get('actions/product_actions.php', { action: 'variants', product_id: productId });
            if (!res.success) return;
            const variants = res.data || [];
            const detailsHtml = this.renderVariantsDetailsTable(variants);
            const colspan = $('#productsTable thead th').length;
            const detailsRow = `<tr class="product-variants-row"><td colspan="${colspan}">${detailsHtml}</td></tr>`;
            $row.after(detailsRow);
            $btn.find('i').removeClass('bi-chevron-down').addClass('bi-chevron-up');
        } catch (err) {
            console.error('toggleVariantsDetails error', err);
        }
    }

    renderVariantsDetailsTable(variants) {
        if (!variants.length) {
            return '<div class="text-muted">No variants</div>';
        }
        let rows = '';
        variants
            .slice()
            .sort((a, b) => Number(a.price || 0) - Number(b.price || 0))
            .forEach(v => {
                const price = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(Number(v.price || 0));
                rows += `<tr><td>${v.size || ''}</td><td>${price}</td><td>${v.stock || 0}</td></tr>`;
            });
        return `
            <div class="card card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Size</th><th>Price</th><th>Stock</th></tr></thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            </div>
        `;
    }

    // Image preview
    bindImagePreview() {
        $(document).on('change', '#image', function() {
            const files = this.files || [];
            const list = $('#imageList');
            list.find('img.temp').parent().remove();
            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    list.append(`<div class="position-relative"><img class="temp" src="${e.target.result}" style="height:60px;width:60px;object-fit:cover;border-radius:6px;opacity:0.8;" /><span class="position-absolute bottom-0 start-0 badge bg-secondary">new</span></div>`);
                };
                reader.readAsDataURL(file);
            });
        });
        // delete existing image
        $(document).on('click', '.delete-image-btn', async (e) => {
            const $btn = $(e.currentTarget);
            const productId = $('#productForm [name=product_id]').val();
            const url = $btn.data('url');
            if (!productId || !url) return;
            if (!confirm('Delete this image?')) return;
            try {
                // find image_id by fetching images then matching url
                const res = await $.get('actions/product_actions.php', { action: 'images', product_id: productId });
                if (res.success) {
                    const img = (res.data || []).find(i => i.image_url === url);
                    if (img) {
                        const del = await $.post('actions/product_actions.php', { action: 'delete_image', image_id: img.image_id });
                        if (del.success) {
                            $btn.parent().remove();
                        } else {
                            alert(del.message || 'Failed to delete');
                        }
                    }
                }
            } catch (err) {
                console.error(err);
            }
        });
    }

    bindPriceFormatting() {
        // Live thousand separators for VND in inline variant price inputs
        $(document).on('input', '.variant-price', function() {
            const raw = this.value.replace(/[^0-9]/g, '');
            if (raw.length === 0) {
                this.value = '';
                return;
            }
            const formatted = new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 0 }).format(parseInt(raw, 10));
            this.value = formatted;
        });
        $(document).on('blur', '.variant-price', function() {
            const raw = this.value.replace(/[^0-9]/g, '');
            this.value = raw ? new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 0 }).format(parseInt(raw, 10)) : '';
        });
    }

    // Inline Variant management
    async loadVariantsInline(productId) {
        try {
            const res = await $.get('actions/product_actions.php', { action: 'variants', product_id: productId });
            if (!res.success) return;
            const tbody = $('#variantsTableInline tbody');
            tbody.empty();
            (res.data || []).forEach(v => this.renderVariantRowInline(v));
        } catch (err) {
            console.error('loadVariantsInline error', err);
        }
    }

    renderVariantRowInline(variant) {
        const tbody = $('#variantsTableInline tbody');
        const idAttr = variant.variant_id ? `data-id="${variant.variant_id}"` : '';
        const priceDisplay = variant.price !== undefined && variant.price !== null
            ? new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 0 }).format(Math.round(Number(variant.price)))
            : '';
        const row = `
            <tr class="variant-row" ${idAttr}>
                <td><input type="text" class="form-control form-control-sm variant-size" value="${variant.size || ''}"></td>
                <td><input type="text" class="form-control form-control-sm variant-price" value="${priceDisplay}" placeholder="0"></td>
                <td><input type="number" class="form-control form-control-sm variant-stock" value="${variant.stock !== undefined ? variant.stock : ''}" min="0" placeholder="0"></td>
                <td>
                    <button type="button" class="btn btn-sm btn-success variant-save-btn">Save</button>
                    ${variant.variant_id ? `<button type="button" class="btn btn-sm btn-outline-danger variant-delete-btn">Delete</button>` : ''}
                </td>
            </tr>
        `;
        tbody.append(row);
    }

    addVariantRowInline() {
        const hasId = !!$('#productForm [name=product_id]').val();
        if (!hasId) {
            $('#variantsInlineHint').show();
            return;
        }
        this.renderVariantRowInline({});
    }

    async saveVariantRowInline(e) {
        const $row = $(e.currentTarget).closest('tr');
        const variantId = $row.data('id');
        const productId = $('#productForm [name=product_id]').val();
        const size = ($row.find('.variant-size').val() || '').trim();
        const priceRaw = ($row.find('.variant-price').val() || '').toString().replace(/[^0-9]/g, '');
        const price = priceRaw.length ? parseInt(priceRaw, 10) : 0;
        const stock = parseInt($row.find('.variant-stock').val() || '0', 10);
        if (!size) { alert('Size is required'); return; }
        try {
            let res;
            if (variantId) {
                res = await $.post('actions/product_actions.php', { action: 'variant_update', variant_id: variantId, size, price, stock });
            } else {
                res = await $.post('actions/product_actions.php', { action: 'variant_create', product_id: productId, size, price, stock });
            }
            if (res.success) {
                await this.loadVariantsInline(productId);
                this.loadProducts();
            } else {
                alert(res.message || 'Failed to save variant');
            }
        } catch (err) {
            console.error('saveVariantRowInline error', err);
        }
    }

    async deleteVariantRowInline(e) {
        const $row = $(e.currentTarget).closest('tr');
        const variantId = $row.data('id');
        const productId = $('#productForm [name=product_id]').val();
        if (!variantId) return;
        if (!confirm('Delete this variant?')) return;
        try {
            const res = await $.post('actions/product_actions.php', { action: 'variant_delete', variant_id: variantId });
            if (res.success) {
                await this.loadVariantsInline(productId);
                this.loadProducts();
            } else {
                alert(res.message || 'Failed to delete variant');
            }
        } catch (err) {
            console.error('deleteVariantRowInline error', err);
        }
    }

    renderCategoriesTable(categories) {
        const tbody = $('#categoriesTable tbody');
        tbody.empty();
        
        categories.forEach(category => {
            const row = `
                <tr>
                    <td>${category.category_id}</td>
                    <td>${category.category_name}</td>
                    <td>${category.description || 'N/A'}</td>
                    <td>${category.product_count || 0}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary edit-category-btn" data-id="${category.category_id}"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger delete-category-btn" data-id="${category.category_id}" data-name="${category.category_name}"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    renderUsersTable(users) {
        const tbody = $('#usersTable tbody');
        tbody.empty();
        
        users.forEach(user => {
            const status = user.status || 'active';
            const statusClass = status === 'active' ? 'bg-success' : (status === 'pending' ? 'bg-warning' : 'bg-danger');
            const row = `
                <tr>
                    <td>${user.user_id}</td>
                    <td>${user.full_name || user.username}</td>
                    <td>${user.email}</td>
                    <td><span class="badge ${user.role === 'admin' ? 'bg-danger' : 'bg-primary'}">${user.role}</span></td>
                    <td><span class="badge user-status-badge ${statusClass}">${status.charAt(0).toUpperCase()+status.slice(1)}</span></td>
                    <td>${user.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary edit-user-btn" data-id="${user.user_id}"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-secondary toggle-user-status-btn" data-id="${user.user_id}" data-status="${status}" title="Toggle Active/Inactive"><i class="bi bi-toggle2-on"></i></button>
                        <button class="btn btn-sm btn-outline-danger delete-user-btn" data-id="${user.user_id}" data-name="${user.full_name || user.username}"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    renderOrdersTable(orders) {
        const tbody = $('#ordersTable tbody');
        tbody.empty();
        
        orders.forEach(order => {
            const statusClass = this.getStatusClass(order.status);
            const row = `
                <tr>
                    <td>#${order.order_id}</td>
                    <td>${order.full_name || order.username}</td>
                    <td>${order.total_amount}₫</td>
                    <td><span class="badge ${statusClass}">${order.status}</span></td>
                    <td>${order.order_date ? new Date(order.order_date).toLocaleDateString() : 'N/A'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary update-order-status" data-id="${order.order_id}" data-status="${order.status}"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger delete-order-btn" data-id="${order.order_id}"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    renderPagination(pages, currentPage, type) {
        const pagination = $(`#${type}Pagination`);
        pagination.empty();
        
        if (pages <= 1) return;
        
        let paginationHtml = '<ul class="pagination justify-content-center">';
        
        // Previous button
        paginationHtml += `<li class="page-item ${currentPage == 1 ? 'disabled' : ''}">
            <a class="page-link" href="index.php?page=${type}&p=${currentPage - 1}">Previous</a>
        </li>`;
        
        // Page numbers
        for (let i = 1; i <= pages; i++) {
            paginationHtml += `<li class="page-item ${i == currentPage ? 'active' : ''}">
                <a class="page-link" href="index.php?page=${type}&p=${i}">${i}</a>
            </li>`;
        }
        
        // Next button
        paginationHtml += `<li class="page-item ${currentPage == pages ? 'disabled' : ''}">
            <a class="page-link" href="index.php?page=${type}&p=${currentPage + 1}">Next</a>
        </li>`;
        
        paginationHtml += '</ul>';
        pagination.html(paginationHtml);
    }

    // Utility Methods
    getCurrentPage() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('page') || 'dashboard';
    }

    getStatusClass(status) {
        switch (status) {
            case 'Chờ xác nhận': return 'bg-warning';
            case 'Đang giao': return 'bg-info';
            case 'Đã giao': return 'bg-success';
            case 'Đã hủy': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }

    showAlert(title, message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <strong>${title}:</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert').remove();
        
        // Add new alert
        $('.content-card').prepend(alertHtml);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }
}

// Initialize when document is ready
$(document).ready(function() {
    window.adminCRUD = new AdminCRUD();
}); 
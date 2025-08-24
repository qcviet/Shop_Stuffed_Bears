<?php
require_once __DIR__ . '/../../config/config.php';
?>

<?php include __DIR__ . '/../includes/global.php'; ?>
<?php include __DIR__ . '/header.php'; ?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary">Liên Hệ Với Chúng Tôi</h1>
                <p class="lead text-muted">Chúng tôi luôn sẵn sàng hỗ trợ bạn 24/7</p>
            </div>

            <div class="row g-4">
                <!-- Contact Information -->
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="card-title fw-bold mb-4">
                                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                Thông Tin Liên Hệ
                            </h3>
                            
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                    <i class="bi bi-geo-alt-fill text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">Địa Chỉ</h6>
                                    <p class="text-muted mb-0">123 Đường ABC, Quận XYZ, Hà Nội</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                    <i class="bi bi-telephone-fill text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">Điện Thoại</h6>
                                    <p class="text-muted mb-0">+84 123 456 789</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                    <i class="bi bi-envelope-fill text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">Email</h6>
                                    <p class="text-muted mb-0">info@shopgauyeu.com</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                    <i class="bi bi-clock-fill text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">Giờ Làm Việc</h6>
                                    <p class="text-muted mb-0">Thứ 2 - Chủ Nhật: 8:00 - 22:00</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                    <i class="bi bi-chat-dots-fill text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">Hỗ Trợ Online</h6>
                                    <p class="text-muted mb-0">Zalo: @shopgauyeu</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="card-title fw-bold mb-4">
                                <i class="bi bi-chat-square-text-fill text-primary me-2"></i>
                                Gửi Tin Nhắn
                            </h3>
                            
                            <form id="contactForm">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-semibold">Họ và Tên *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label fw-semibold">Số Điện Thoại</label>
                                    <input type="tel" class="form-control" id="phone" name="phone">
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label fw-semibold">Chủ Đề *</label>
                                    <select class="form-select" id="subject" name="subject" required>
                                        <option value="">Chọn chủ đề</option>
                                        <option value="order">Hỏi về đơn hàng</option>
                                        <option value="product">Hỏi về sản phẩm</option>
                                        <option value="shipping">Hỏi về vận chuyển</option>
                                        <option value="return">Đổi trả sản phẩm</option>
                                        <option value="other">Khác</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label fw-semibold">Nội Dung Tin Nhắn *</label>
                                    <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 fw-bold">
                                    <i class="bi bi-send-fill me-2"></i>
                                    Gửi Tin Nhắn
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="card-title fw-bold mb-4">
                                <i class="bi bi-map-fill text-primary me-2"></i>
                                Bản Đồ
                            </h3>
                            <div class="ratio ratio-16x9">
                                <iframe 
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3724.0964843003007!2d105.78159831476825!3d21.02851178599432!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ab3b4220c2bd%3A0x1c9e359e2a4f618c!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBDw7RuZyBuZ2jhu4cgVGjDtG5nIHRpbiB2aWV0!5e0!3m2!1svi!2s!4v1640995200000!5m2!1svi!2s" 
                                    style="border:0;" 
                                    allowfullscreen="" 
                                    loading="lazy">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.bg-primary {
    background-color: var(--primary-color) !important;
}

.text-primary {
    color: var(--primary-color) !important;
}
</style>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Show success message (in a real app, you'd send this to a server)
    alert('Cảm ơn bạn đã liên hệ với chúng tôi! Chúng tôi sẽ phản hồi sớm nhất có thể.');
    
    // Reset form
    this.reset();
});
</script>

<?php include __DIR__ . '/footer.php'; ?>

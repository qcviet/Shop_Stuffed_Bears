class Chatbot {
    constructor() {
        this.isOpen = false;
        this.messages = [];
        this.currentUser = null;
        this.cache = new Map();
        this.isTyping = false;
        this.typingTimeout = null;
        this.analytics = {
            messagesSent: 0,
            messagesReceived: 0,
            sessionStart: new Date(),
            featuresUsed: {}
        };
        this.init();
    }

    init() {
        this.createChatbotHTML();
        this.bindEvents();
        this.loadWelcomeMessage();
        this.checkForUnreadMessages();
        this.setupAutoSave();
        this.trackAnalytics('chatbot_loaded');
    }

    createChatbotHTML() {
        const chatbotHTML = `
            <div class="chatbot-container">
                <button class="chatbot-toggle" id="chatbotToggle" aria-label="Mở chatbot">
                    <i class="bi bi-chat-dots"></i>
                    <div class="notification" id="chatbotNotification" style="display: none;">1</div>
                </button>
                
                <div class="chatbot-window" id="chatbotWindow">
                    <div class="chatbot-header">
                        <h3><i class="bi bi-robot"></i> Trợ lý Shop Gau Yeu</h3>
                        <div class="chatbot-actions">
                            <button class="chatbot-action" id="chatbotMinimize" aria-label="Thu nhỏ">
                                <i class="bi bi-dash"></i>
                            </button>
                            <button class="chatbot-close" id="chatbotClose" aria-label="Đóng">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="chatbot-messages" id="chatbotMessages">
                        <!-- Messages will be added here -->
                    </div>
                    
                    <div class="chatbot-input">
                        <input type="text" id="chatbotInput" placeholder="Nhập tin nhắn của bạn..." autocomplete="off">
                        <button class="chatbot-send" id="chatbotSend" aria-label="Gửi tin nhắn">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    }

    bindEvents() {
        // Toggle chatbot
        document.getElementById('chatbotToggle').addEventListener('click', () => {
            this.toggleChatbot();
        });

        // Close chatbot
        document.getElementById('chatbotClose').addEventListener('click', () => {
            this.closeChatbot();
        });

        // Minimize chatbot
        document.getElementById('chatbotMinimize').addEventListener('click', () => {
            this.minimizeChatbot();
        });

        // Send message with debounce
        const sendButton = document.getElementById('chatbotSend');
        const input = document.getElementById('chatbotInput');
        
        sendButton.addEventListener('click', () => {
            this.sendMessage();
        });

        // Enter key to send with debounce
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        // Auto-resize input
        input.addEventListener('input', this.debounce(() => {
            this.autoResizeInput();
        }, 100));

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.chatbot-container') && this.isOpen) {
                this.closeChatbot();
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                this.toggleChatbot();
            }
        });

        // Handle window resize
        window.addEventListener('resize', this.debounce(() => {
            this.handleResize();
        }, 250));
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    toggleChatbot() {
        if (this.isOpen) {
            this.closeChatbot();
        } else {
            this.openChatbot();
        }
    }

    openChatbot() {
        this.isOpen = true;
        const window = document.getElementById('chatbotWindow');
        window.style.display = 'flex';
        window.classList.add('chatbot-open');
        document.getElementById('chatbotNotification').style.display = 'none';
        this.scrollToBottom();
        document.getElementById('chatbotInput').focus();
        this.trackAnalytics('chatbot_opened');
    }

    closeChatbot() {
        this.isOpen = false;
        const window = document.getElementById('chatbotWindow');
        window.classList.remove('chatbot-open');
        window.style.display = 'none';
        this.saveConversation();
        this.trackAnalytics('chatbot_closed');
    }

    minimizeChatbot() {
        const window = document.getElementById('chatbotWindow');
        window.classList.toggle('chatbot-minimized');
        this.trackAnalytics('chatbot_minimized');
    }

    loadWelcomeMessage() {
        const welcomeMessage = {
            type: 'bot',
            content: 'Xin chào! Tôi là trợ lý của Shop Gau Yeu. Tôi có thể giúp bạn:',
            quickReplies: [
                'Tìm sản phẩm',
                'Hướng dẫn mua hàng',
                'Chính sách đổi trả',
                'Liên hệ hỗ trợ'
            ]
        };
        
        this.addMessage(welcomeMessage);
    }

    sendMessage() {
        const input = document.getElementById('chatbotInput');
        const message = input.value.trim();
        
        if (!message || this.isTyping) return;
        
        // Add user message
        this.addMessage({
            type: 'user',
            content: message,
            timestamp: new Date()
        });
        
        input.value = '';
        this.autoResizeInput();
        
        // Show typing indicator
        this.showTyping();
        
        // Process message after delay
        setTimeout(() => {
            this.processUserMessage(message);
        }, 800);
        
        this.trackAnalytics('message_sent');
    }

    showTyping() {
        this.isTyping = true;
        const typingMessage = {
            type: 'typing',
            content: '<div class="typing-dots"><div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div></div>'
        };
        
        this.addMessage(typingMessage);
    }

    removeTyping() {
        this.isTyping = false;
        const typingMessage = document.querySelector('.chatbot-message.typing');
        if (typingMessage) {
            typingMessage.remove();
        }
    }

    processUserMessage(message) {
        this.removeTyping();
        
        const lowerMessage = message.toLowerCase();
        let response = null;
        let feature = 'default';
        
        // Product search
        if (lowerMessage.includes('tìm') || lowerMessage.includes('search') || lowerMessage.includes('sản phẩm')) {
            feature = 'product_search';
            this.handleProductSearchAPI(message);
            return;
        }
        // Shopping guide
        else if (lowerMessage.includes('mua hàng') || lowerMessage.includes('hướng dẫn') || lowerMessage.includes('order')) {
            feature = 'shopping_guide';
            response = this.handleShoppingGuide();
        }
        // Return policy
        else if (lowerMessage.includes('đổi trả') || lowerMessage.includes('return') || lowerMessage.includes('refund')) {
            feature = 'return_policy';
            response = this.handleReturnPolicy();
        }
        // Contact support
        else if (lowerMessage.includes('liên hệ') || lowerMessage.includes('support') || lowerMessage.includes('help')) {
            feature = 'contact_support';
            response = this.handleContactSupport();
        }
        // Price inquiry
        else if (lowerMessage.includes('giá') || lowerMessage.includes('price') || lowerMessage.includes('bao nhiêu')) {
            feature = 'price_inquiry';
            response = this.handlePriceInquiry(message);
        }
        // Shipping
        else if (lowerMessage.includes('ship') || lowerMessage.includes('giao hàng') || lowerMessage.includes('delivery')) {
            feature = 'shipping_info';
            response = this.handleShippingInfo();
        }
        // Default response
        else {
            response = this.handleDefaultResponse();
        }
        
        this.addMessage({
            ...response,
            timestamp: new Date()
        });
        
        this.trackAnalytics('feature_used', feature);
    }

    handleProductSearch(message) {
        const keywords = message.replace(/tìm|search|sản phẩm|gì|nào/gi, '').trim();
        
        if (keywords) {
            return {
                type: 'bot',
                content: `Tôi sẽ tìm kiếm sản phẩm "${keywords}" cho bạn. Bạn có thể xem kết quả tại trang tìm kiếm.`,
                quickReplies: [
                    'Xem tất cả sản phẩm',
                    'Gấu bông',
                    'Thú nhồi bông',
                    'Quà tặng'
                ]
            };
        } else {
            return {
                type: 'bot',
                content: 'Bạn muốn tìm sản phẩm gì? Tôi có thể giúp bạn tìm kiếm.',
                quickReplies: [
                    'Gấu bông',
                    'Thú nhồi bông',
                    'Quà tặng',
                    'Sản phẩm mới'
                ]
            };
        }
    }

    handleProductSearchAPI(message) {
        const keywords = message.replace(/tìm|search|sản phẩm|gì|nào/gi, '').trim();
        
        if (!keywords) {
            this.addMessage(this.handleProductSearch(message));
            return;
        }

        // Check cache first
        const cacheKey = `search_${keywords}`;
        if (this.cache.has(cacheKey)) {
            const cachedData = this.cache.get(cacheKey);
            this.displaySearchResults(cachedData, keywords);
            return;
        }

        // Call API to search products
        fetch('chatbot_handler.php?action=search_products', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `query=${encodeURIComponent(keywords)}&limit=3`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Cache the result for 5 minutes
            this.cache.set(cacheKey, data);
            setTimeout(() => this.cache.delete(cacheKey), 5 * 60 * 1000);
            
            this.displaySearchResults(data, keywords);
        })
        .catch(error => {
            console.error('Error searching products:', error);
            this.addMessage({
                type: 'bot',
                content: 'Xin lỗi, có lỗi xảy ra khi tìm kiếm. Vui lòng thử lại sau.',
                quickReplies: [
                    'Tìm sản phẩm khác',
                    'Liên hệ hỗ trợ',
                    'Xem tất cả sản phẩm'
                ],
                timestamp: new Date()
            });
        });
    }

    displaySearchResults(data, keywords) {
        if (data.success && data.products.length > 0) {
            let content = `Tôi tìm thấy ${data.count} sản phẩm phù hợp với "${keywords}":\n\n`;
            
            data.products.forEach(product => {
                content += `• ${product.name} - ${product.price}\n`;
            });
            
            content += '\nBạn có muốn xem chi tiết sản phẩm nào không?';
            
            this.addMessage({
                type: 'bot',
                content: content,
                quickReplies: [
                    'Xem tất cả sản phẩm',
                    'Tìm sản phẩm khác',
                    'Hướng dẫn mua hàng'
                ],
                timestamp: new Date()
            });
        } else {
            this.addMessage({
                type: 'bot',
                content: `Xin lỗi, tôi không tìm thấy sản phẩm nào phù hợp với "${keywords}". Bạn có thể thử tìm kiếm với từ khóa khác.`,
                quickReplies: [
                    'Xem tất cả sản phẩm',
                    'Gấu bông',
                    'Thú nhồi bông',
                    'Tìm sản phẩm khác'
                ],
                timestamp: new Date()
            });
        }
    }

    handleShoppingGuide() {
        return {
            type: 'bot',
            content: 'Hướng dẫn mua hàng tại Shop Gau Yeu:\n\n1. Chọn sản phẩm bạn muốn mua\n2. Thêm vào giỏ hàng\n3. Kiểm tra giỏ hàng và thanh toán\n4. Chọn phương thức thanh toán\n5. Xác nhận đơn hàng\n\nBạn cần hỗ trợ thêm về bước nào?',
            quickReplies: [
                'Thanh toán online',
                'Thanh toán khi nhận hàng',
                'Thêm vào giỏ hàng',
                'Xem giỏ hàng'
            ]
        };
    }

    handleReturnPolicy() {
        return {
            type: 'bot',
            content: 'Chính sách đổi trả:\n\n• Đổi trả trong vòng 7 ngày\n• Sản phẩm phải còn nguyên vẹn\n• Không áp dụng với sản phẩm đã sử dụng\n• Phí vận chuyển đổi trả: 30.000đ\n\nBạn có câu hỏi gì khác không?',
            quickReplies: [
                'Liên hệ hỗ trợ',
                'Hướng dẫn mua hàng',
                'Tìm sản phẩm'
            ]
        };
    }

    handleContactSupport() {
        return {
            type: 'bot',
            content: 'Thông tin liên hệ hỗ trợ:\n\n📞 Hotline: 1900-xxxx\n📧 Email: support@shopgauyeu.com\n💬 Facebook: Shop Gau Yeu\n⏰ Giờ làm việc: 8h-22h (T2-CN)\n\nBạn muốn liên hệ qua kênh nào?',
            quickReplies: [
                'Gọi điện',
                'Gửi email',
                'Facebook',
                'Tìm sản phẩm'
            ]
        };
    }

    handlePriceInquiry(message) {
        return {
            type: 'bot',
            content: 'Giá sản phẩm tại Shop Gau Yeu:\n\n• Gấu bông: 50.000đ - 500.000đ\n• Thú nhồi bông: 30.000đ - 300.000đ\n• Quà tặng: 100.000đ - 1.000.000đ\n\nBạn muốn xem sản phẩm cụ thể nào?',
            quickReplies: [
                'Gấu bông giá rẻ',
                'Thú nhồi bông cao cấp',
                'Quà tặng đặc biệt',
                'Xem tất cả'
            ]
        };
    }

    handleShippingInfo() {
        return {
            type: 'bot',
            content: 'Thông tin giao hàng:\n\n🚚 Giao hàng toàn quốc\n💰 Phí ship: 20.000đ - 50.000đ\n⏱️ Thời gian: 1-3 ngày\n📦 Miễn phí ship đơn từ 500.000đ\n\nBạn ở khu vực nào?',
            quickReplies: [
                'Hà Nội',
                'TP.HCM',
                'Tỉnh khác',
                'Tính phí ship'
            ]
        };
    }

    handleDefaultResponse() {
        const responses = [
            'Xin lỗi, tôi chưa hiểu rõ câu hỏi của bạn. Bạn có thể hỏi về sản phẩm, hướng dẫn mua hàng, hoặc chính sách đổi trả.',
            'Tôi có thể giúp bạn tìm sản phẩm, hướng dẫn mua hàng, hoặc trả lời các câu hỏi về chính sách. Bạn cần hỗ trợ gì?',
            'Bạn có thể hỏi tôi về:\n• Tìm kiếm sản phẩm\n• Hướng dẫn mua hàng\n• Chính sách đổi trả\n• Thông tin giao hàng'
        ];
        
        const randomResponse = responses[Math.floor(Math.random() * responses.length)];
        
        return {
            type: 'bot',
            content: randomResponse,
            quickReplies: [
                'Tìm sản phẩm',
                'Hướng dẫn mua hàng',
                'Chính sách đổi trả',
                'Liên hệ hỗ trợ'
            ]
        };
    }

    addMessage(messageData) {
        const messagesContainer = document.getElementById('chatbotMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chatbot-message ${messageData.type}`;
        
        let content = messageData.content;
        
        // Handle typing indicator
        if (messageData.type === 'typing') {
            messageDiv.innerHTML = `<div class="message-content">${content}</div>`;
        } else {
            // Add timestamp if available
            const timestamp = messageData.timestamp ? this.formatTimestamp(messageData.timestamp) : '';
            messageDiv.innerHTML = `
                <div class="message-content">
                    ${content}
                    ${timestamp ? `<div class="message-timestamp">${timestamp}</div>` : ''}
                </div>
            `;
        }
        
        // Add quick replies if available - Now positioned below the message content
        if (messageData.quickReplies && messageData.type === 'bot') {
            const quickRepliesDiv = document.createElement('div');
            quickRepliesDiv.className = 'quick-replies';
            
            messageData.quickReplies.forEach(reply => {
                const replyButton = document.createElement('button');
                replyButton.className = 'quick-reply';
                replyButton.textContent = reply;
                replyButton.setAttribute('aria-label', `Chọn: ${reply}`);
                replyButton.addEventListener('click', () => {
                    this.handleQuickReply(reply);
                });
                quickRepliesDiv.appendChild(replyButton);
            });
            
            // Append quick replies inside the message-content div
            messageDiv.querySelector('.message-content').appendChild(quickRepliesDiv);
        }
        
        messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
        
        // Store message
        this.messages.push(messageData);
        
        // Track analytics
        if (messageData.type === 'user') {
            this.analytics.messagesReceived++;
        } else if (messageData.type === 'bot') {
            this.analytics.messagesSent++;
        }
    }

    formatTimestamp(date) {
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        
        if (minutes < 1) return 'Vừa xong';
        if (minutes < 60) return `${minutes} phút trước`;
        
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return `${hours} giờ trước`;
        
        return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    }

    handleQuickReply(reply) {
        // Add user message
        this.addMessage({
            type: 'user',
            content: reply,
            timestamp: new Date()
        });
        
        // Show typing indicator
        this.showTyping();
        
        // Process reply after delay
        setTimeout(() => {
            this.processUserMessage(reply);
        }, 800);
        
        this.trackAnalytics('quick_reply_used');
    }

    scrollToBottom() {
        const messagesContainer = document.getElementById('chatbotMessages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    autoResizeInput() {
        const input = document.getElementById('chatbotInput');
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 100) + 'px';
    }

    handleResize() {
        if (this.isOpen) {
            this.scrollToBottom();
        }
    }

    checkForUnreadMessages() {
        // Check if there are unread messages (you can implement this based on your needs)
        const hasUnreadMessages = false; // This would be set based on your logic
        
        if (hasUnreadMessages) {
            document.getElementById('chatbotNotification').style.display = 'flex';
        }
    }

    setupAutoSave() {
        // Auto save conversation every 30 seconds
        setInterval(() => {
            if (this.messages.length > 0) {
                this.saveConversation();
            }
        }, 30000);
    }

    saveConversation() {
        if (this.messages.length === 0) return;

        const conversationData = {
            messages: this.messages,
            analytics: this.analytics,
            sessionId: this.getSessionId()
        };

        fetch('chatbot_handler.php?action=save_conversation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(conversationData)
        })
        .catch(error => {
            console.error('Error saving conversation:', error);
        });
    }

    getSessionId() {
        if (!this.sessionId) {
            this.sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }
        return this.sessionId;
    }

    trackAnalytics(event, data = null) {
        this.analytics.featuresUsed[event] = (this.analytics.featuresUsed[event] || 0) + 1;
        
        // Send analytics to server
        fetch('chatbot_handler.php?action=track_analytics', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                event: event,
                data: data,
                sessionId: this.getSessionId(),
                timestamp: new Date().toISOString()
            })
        })
        .catch(error => {
            console.error('Error tracking analytics:', error);
        });
    }

    // Method to add product suggestions
    addProductSuggestion(product) {
        const suggestionHTML = `
            <div class="product-suggestion" onclick="window.location.href='${product.url}'">
                <div class="product-info">
                    <img src="${product.image}" alt="${product.name}" loading="lazy">
                    <div class="product-details">
                        <div class="product-name">${product.name}</div>
                        <div class="product-price">${product.price}</div>
                    </div>
                </div>
            </div>
        `;
        
        return suggestionHTML;
    }

    // Performance optimization methods
    clearCache() {
        this.cache.clear();
    }

    getAnalytics() {
        return {
            ...this.analytics,
            sessionDuration: new Date() - this.analytics.sessionStart
        };
    }
}

// Initialize chatbot when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Check if chatbot is already initialized
    if (!window.chatbot) {
        window.chatbot = new Chatbot();
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Chatbot;
}

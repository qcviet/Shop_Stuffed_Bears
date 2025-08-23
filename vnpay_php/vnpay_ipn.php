﻿<?php
/* Payment Notify
 * IPN URL: Ghi nhận kết quả thanh toán từ VNPAY
 * Các bước thực hiện:
 * Kiểm tra checksum 
 * Tìm giao dịch trong database
 * Kiểm tra số tiền giữa hai hệ thống
 * Kiểm tra tình trạng của giao dịch trước khi cập nhật
 * Cập nhật kết quả vào Database
 * Trả kết quả ghi nhận lại cho VNPAY
 */

require_once("./config.php");
require_once("../config/database.php");
require_once("../models/OrderModel.php");
$inputData = array();
$returnData = array();
foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

$vnp_SecureHash = $inputData['vnp_SecureHash'];
unset($inputData['vnp_SecureHash']);
ksort($inputData);
$i = 0;
$hashData = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
$vnpTranId = $inputData['vnp_TransactionNo']; //Mã giao dịch tại VNPAY
$vnp_BankCode = $inputData['vnp_BankCode']; //Ngân hàng thanh toán
$vnp_Amount = $inputData['vnp_Amount']/100; // Số tiền thanh toán VNPAY phản hồi

$Status = 0; // Là trạng thái thanh toán của giao dịch chưa có IPN lưu tại hệ thống của merchant chiều khởi tạo URL thanh toán.
$orderId = $inputData['vnp_TxnRef'];

try {
    //Check Orderid    
    //Kiểm tra checksum của dữ liệu
    if ($secureHash == $vnp_SecureHash) {
        // Initialize database connection and order model
        $database = new Database();
        $db = $database->getConnection();
        $orderModel = new OrderModel($db);
        
        // Get order from database using order ID
        $order = $orderModel->getById($orderId);
        
        if ($order != NULL) {
            // Check if payment amount matches (VNPay returns amount in VND cents, so divide by 100)
            if($order["total_amount"] == $vnp_Amount) 
            {
                // Check if order payment status is not already paid
                if ($order["payment_status"] != 'Đã thanh toán') {
                    if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00') {
                        // Payment successful - update order payment status
                        $updateResult = $orderModel->updatePaymentStatus($orderId, 'Đã thanh toán');
                        
                        if ($updateResult) {
                            $returnData['RspCode'] = '00';
                            $returnData['Message'] = 'Confirm Success';
                            // Log successful payment update
                            error_log("VNPay IPN: Order #$orderId payment status updated to 'Đã thanh toán'");
                        } else {
                            $returnData['RspCode'] = '99';
                            $returnData['Message'] = 'Database update failed';
                            error_log("VNPay IPN: Failed to update payment status for Order #$orderId");
                        }
                    } else {
                        // Payment failed
                        $returnData['RspCode'] = '00';
                        $returnData['Message'] = 'Payment failed';
                    }
                } else {
                    $returnData['RspCode'] = '02';
                    $returnData['Message'] = 'Order already confirmed';
                }
            }
            else {
                $returnData['RspCode'] = '04';
                $returnData['Message'] = 'invalid amount';
            }
        } else {
            $returnData['RspCode'] = '01';
            $returnData['Message'] = 'Order not found';
        }
    } else {
        $returnData['RspCode'] = '97';
        $returnData['Message'] = 'Invalid signature';
    }
} catch (Exception $e) {
    $returnData['RspCode'] = '99';
    $returnData['Message'] = 'Unknow error';
}
//Trả lại VNPAY theo định dạng JSON
echo json_encode($returnData);

<?php
/**
 * ─────────────────────────────────────────────────────────────────────────────
 * setup_database.php — Database Initialization Script
 * ─────────────────────────────────────────────────────────────────────────────
 * เพิ่มระบบป้องกันเพื่อความปลอดภัย (Security Check) ป้องกันการถูกสั่ง Reset โดยไม่ได้รับอนุญาต
 * ─────────────────────────────────────────────────────────────────────────────
 */
header('Content-Type: application/json; charset=utf-8');

// ตรวจสอบความปลอดภัย: อนุญาตเฉพาะ CLI หรือเมื่อมี key=admin1234
$authKey = $_GET['key'] ?? '';
$isCli   = (php_sapi_name() === 'cli');

if (!$isCli && $authKey !== 'admin1234') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'ไม่อนุญาตให้เข้าถึงสคริปต์นี้โดยตรง เพื่อความปลอดภัยของข้อมูลในระบบ (Access Denied)'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/../inc/connDB.php';

try {
    $sqlFile = __DIR__ . '/../../dbNorthwind.sql';
    if (!file_exists($sqlFile)) {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบไฟล์ dbNorthwind.sql ที่ ' . $sqlFile
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sql = file_get_contents($sqlFile);
    $conn->exec($sql);

    $stmt = $conn->query("SELECT COUNT(*) AS total FROM tb_products");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'นำเข้าและเตรียมฐานข้อมูล Northwind เรียบร้อยแล้ว!',
        'total_products' => (int)($result['total'] ?? 0)
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

-- ==========================================
-- 可能性量表與財務衝擊量表資料表
-- Migration: 002_create_scale_tables_utf8
-- 建立日期: 2025-09-30
-- 說明: 為風險評估範本建立可能性量表和財務衝擊量表的相關資料表（UTF-8版本）
-- ==========================================

USE esg_db;

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ==========================================
-- 可能性量表相關資料表
-- ==========================================

-- 可能性量表主表 - 存儲每個範本的可能性量表設定
CREATE TABLE IF NOT EXISTS probability_scales (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主鍵ID',
    template_id BIGINT(20) UNSIGNED NOT NULL COMMENT '範本ID,關聯risk_assessment_templates表',
    description_text TEXT NULL COMMENT '說明文字內容',
    show_description TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否顯示說明文字: 0=不顯示, 1=顯示',
    selected_display_column VARCHAR(50) NULL COMMENT '預設顯示欄位: probability=發生可能性程度, 或欄位ID數字',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    PRIMARY KEY (id),
    KEY idx_template_id (template_id),
    FOREIGN KEY (template_id) REFERENCES risk_assessment_templates(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='可能性量表主表 - 存儲範本的可能性量表基本設定';

-- 可能性量表欄位定義表 - 存儲量表的動態欄位設定
CREATE TABLE IF NOT EXISTS probability_scale_columns (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主鍵ID',
    scale_id BIGINT(20) UNSIGNED NOT NULL COMMENT '量表ID,關聯probability_scales表',
    column_id INT NOT NULL COMMENT '欄位ID (前端使用的唯一識別碼)',
    name VARCHAR(255) NOT NULL COMMENT '欄位名稱,如:如風險不曾發生過',
    removable TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否可移除: 0=不可移除, 1=可移除',
    sort_order INT NOT NULL DEFAULT 0 COMMENT '排序順序,數字越小越前面',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    PRIMARY KEY (id),
    KEY idx_scale_id (scale_id),
    KEY idx_sort_order (sort_order),
    FOREIGN KEY (scale_id) REFERENCES probability_scales(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='可能性量表欄位定義表 - 存儲量表的動態欄位設定';

-- 可能性量表資料列表 - 存儲量表的實際資料內容
CREATE TABLE IF NOT EXISTS probability_scale_rows (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主鍵ID',
    scale_id BIGINT(20) UNSIGNED NOT NULL COMMENT '量表ID,關聯probability_scales表',
    probability VARCHAR(255) NULL COMMENT '發生可能性程度 (固定欄位)',
    score_range VARCHAR(50) NULL COMMENT '分數級距 (固定欄位,用作下拉選單的value)',
    dynamic_fields JSON NULL COMMENT '動態欄位資料,格式: {"column_id": "value"}',
    sort_order INT NOT NULL DEFAULT 0 COMMENT '排序順序,數字越小越前面',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    PRIMARY KEY (id),
    KEY idx_scale_id (scale_id),
    KEY idx_sort_order (sort_order),
    FOREIGN KEY (scale_id) REFERENCES probability_scales(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='可能性量表資料列表 - 存儲量表的實際資料內容';

-- ==========================================
-- 財務衝擊量表相關資料表
-- ==========================================

-- 財務衝擊量表主表 - 存儲每個範本的財務衝擊量表設定
CREATE TABLE IF NOT EXISTS impact_scales (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主鍵ID',
    template_id BIGINT(20) UNSIGNED NOT NULL COMMENT '範本ID,關聯risk_assessment_templates表',
    selected_display_column VARCHAR(50) NULL COMMENT '預設顯示欄位: impactLevel=財務衝擊程度, 或欄位ID數字',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    PRIMARY KEY (id),
    KEY idx_template_id (template_id),
    FOREIGN KEY (template_id) REFERENCES risk_assessment_templates(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='財務衝擊量表主表 - 存儲範本的財務衝擊量表基本設定';

-- 財務衝擊量表欄位定義表 - 存儲量表的動態欄位設定
CREATE TABLE IF NOT EXISTS impact_scale_columns (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主鍵ID',
    scale_id BIGINT(20) UNSIGNED NOT NULL COMMENT '量表ID,關聯impact_scales表',
    column_id INT NOT NULL COMMENT '欄位ID (前端使用的唯一識別碼)',
    name VARCHAR(255) NOT NULL COMMENT '欄位名稱,如:股東權益金額、實際權益金額(分配後)',
    amount_note VARCHAR(255) NULL COMMENT '金額說明 (僅實際權益金額欄位使用,顯示為藍色提示)',
    removable TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否可移除: 0=不可移除, 1=可移除',
    sort_order INT NOT NULL DEFAULT 0 COMMENT '排序順序,數字越小越前面',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    PRIMARY KEY (id),
    KEY idx_scale_id (scale_id),
    KEY idx_sort_order (sort_order),
    FOREIGN KEY (scale_id) REFERENCES impact_scales(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='財務衝擊量表欄位定義表 - 存儲量表的動態欄位設定';

-- 財務衝擊量表資料列表 - 存儲量表的實際資料內容
CREATE TABLE IF NOT EXISTS impact_scale_rows (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主鍵ID',
    scale_id BIGINT(20) UNSIGNED NOT NULL COMMENT '量表ID,關聯impact_scales表',
    impact_level VARCHAR(255) NULL COMMENT '財務衝擊程度 (固定欄位)',
    score_range VARCHAR(50) NULL COMMENT '分數級距 (固定欄位,用作下拉選單的value)',
    dynamic_fields JSON NULL COMMENT '動態欄位資料,格式: {"column_id": "value"}',
    sort_order INT NOT NULL DEFAULT 0 COMMENT '排序順序,數字越小越前面',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    PRIMARY KEY (id),
    KEY idx_scale_id (scale_id),
    KEY idx_sort_order (sort_order),
    FOREIGN KEY (scale_id) REFERENCES impact_scales(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='財務衝擊量表資料列表 - 存儲量表的實際資料內容';
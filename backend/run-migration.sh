#!/bin/bash
# Migration 執行腳本 - Linux/Mac

echo "========================================"
echo "CodeIgniter 4 Migration Runner"
echo "========================================"
echo ""

# 檢查 .env 檔案
if [ ! -f ".env" ]; then
    echo "[警告] 找不到 .env 檔案"
    echo "請先將 env 檔案複製為 .env 並設定資料庫連線"
    echo ""
    echo "執行指令: cp env .env"
    echo "然後編輯 .env 檔案設定資料庫連線資訊"
    echo ""
    exit 1
fi

echo "執行 Migration..."
echo ""

# 執行 migration
php spark migrate

echo ""
echo "========================================"
echo "Migration 完成"
echo "========================================"
echo ""

# 顯示 migration 狀態
echo "檢查 Migration 狀態..."
php spark migrate:status

echo ""
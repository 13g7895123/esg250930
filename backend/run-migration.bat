@echo off
REM Migration 執行腳本 - Windows 批次檔

echo ========================================
echo CodeIgniter 4 Migration Runner
echo ========================================
echo.

REM 設定 PHP 路徑 (請根據你的環境修改)
set PHP_PATH=php

REM 檢查常見的 PHP 安裝路徑
if exist "C:\xampp\php\php.exe" (
    set PHP_PATH=C:\xampp\php\php.exe
    echo 使用 XAMPP PHP
)
if exist "C:\wamp64\bin\php\php8.3.0\php.exe" (
    set PHP_PATH=C:\wamp64\bin\php\php8.3.0\php.exe
    echo 使用 WAMP PHP
)
if exist "C:\php\php.exe" (
    set PHP_PATH=C:\php\php.exe
    echo 使用系統 PHP
)

echo.
echo PHP 路徑: %PHP_PATH%
echo.

REM 檢查 .env 檔案
if not exist ".env" (
    echo [警告] 找不到 .env 檔案
    echo 請先將 env 檔案複製為 .env 並設定資料庫連線
    echo.
    echo 執行指令: copy env .env
    echo 然後編輯 .env 檔案設定資料庫連線資訊
    echo.
    pause
    exit /b 1
)

echo 執行 Migration...
echo.

REM 執行 migration
%PHP_PATH% spark migrate

echo.
echo ========================================
echo Migration 完成
echo ========================================
echo.

REM 顯示 migration 狀態
echo 檢查 Migration 狀態...
%PHP_PATH% spark migrate:status

echo.
pause
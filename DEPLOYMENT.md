# ESG 專案部署指南

## 生產環境部署

### 前置準備

1. 確保已安裝 Docker 和 Docker Compose
2. 複製環境變數檔案並設定正確的值：
   ```bash
   cp .env.prod.example .env.prod
   ```

3. 編輯 `.env.prod` 檔案，設定以下參數：
   - `NUXT_PUBLIC_API_BASE_URL`: 前端 API 基礎 URL
   - `NUXT_PUBLIC_BACKEND_URL`: 後端 URL
   - `NUXT_PUBLIC_BACKEND_HOST`: 後端主機名稱
   - `NUXT_PUBLIC_BACKEND_PORT`: 後端埠號 (通常是 443 for HTTPS)
   - `MYSQL_ROOT_PASSWORD`: MySQL root 密碼
   - `MYSQL_DATABASE`: 資料庫名稱
   - `MYSQL_USER`: 資料庫使用者
   - `MYSQL_PASSWORD`: 資料庫密碼
   - `PHPMYADMIN_PORT`: phpMyAdmin 埠號
   - `PHPMYADMIN_URL`: phpMyAdmin 網址

### 部署步驟

1. **建置並啟動所有服務**
   ```bash
   docker-compose -f docker-compose.prod.yml --env-file .env.prod up -d --build
   ```

2. **查看服務狀態**
   ```bash
   docker-compose -f docker-compose.prod.yml ps
   ```

3. **查看日誌**
   ```bash
   # 查看所有服務日誌
   docker-compose -f docker-compose.prod.yml logs -f

   # 查看特定服務日誌
   docker-compose -f docker-compose.prod.yml logs -f frontend
   docker-compose -f docker-compose.prod.yml logs -f backend
   docker-compose -f docker-compose.prod.yml logs -f mysql
   docker-compose -f docker-compose.prod.yml logs -f phpmyadmin
   ```

4. **存取 phpMyAdmin**
   - 開啟瀏覽器並前往 `PHPMYADMIN_URL` 設定的網址
   - 或使用 `http://localhost:9402` (若使用預設埠號)
   - 使用 MySQL root 帳號登入

### 服務說明

#### 前端服務
- **技術**: Nuxt 3 (Node.js)
- **埠號**: 3000 (預設)
- **功能**:
  - 自動執行 `npm run build` 進行專案打包
  - 使用 Node.js 執行打包後的應用程式
  - 支援 Server-Side Rendering (SSR)

#### 後端服務
- **技術**: CodeIgniter 4 (PHP 8.1)
- **埠號**: 8080 (預設，映射到容器內的 80)
- **功能**:
  - 內建 Nginx + PHP-FPM
  - **自動執行資料庫 Migration**
  - 等待 MySQL 就緒後才啟動

#### 資料庫服務
- **技術**: MySQL 8.0
- **埠號**: 3306 (預設)
- **功能**:
  - 資料持久化 (使用 Docker Volume)
  - 健康檢查機制
  - UTF-8 MB4 字元集

#### phpMyAdmin 服務
- **技術**: phpMyAdmin (最新版)
- **埠號**: 9402 (預設)
- **網址**: 需在 `.env.prod` 中設定 `PHPMYADMIN_URL`
- **功能**:
  - 網頁介面管理 MySQL 資料庫
  - 等待 MySQL 就緒後才啟動
  - 支援自訂網址配置

### 重要功能

#### 自動執行 Migration
後端服務啟動時會自動執行以下步驟：
1. 等待 MySQL 服務就緒
2. 執行資料庫 Migration (`php spark migrate --all`)
3. 啟動 PHP-FPM 和 Nginx

這確保了部署完成後資料庫架構已經更新完成，可以立即使用。

#### 健康檢查
MySQL 服務配置了健康檢查，後端服務會等待 MySQL 完全就緒後才啟動，避免連線錯誤。

### 停止服務

```bash
docker-compose -f docker-compose.prod.yml down
```

### 停止並清除所有資料（包含資料庫）

```bash
docker-compose -f docker-compose.prod.yml down -v
```

### 更新應用程式

當程式碼有更新時：

```bash
# 拉取最新程式碼
git pull

# 重新建置並啟動
docker-compose -f docker-compose.prod.yml up -d --build
```

### 故障排除

#### 前端無法連線
1. 檢查 `.env.prod` 中的 API URL 設定是否正確
2. 確認後端服務已經啟動
3. 查看前端日誌：`docker-compose -f docker-compose.prod.yml logs frontend`

#### 後端無法連線資料庫
1. 檢查 MySQL 服務是否正常運行
2. 確認資料庫連線參數是否正確
3. 查看後端日誌：`docker-compose -f docker-compose.prod.yml logs backend`

#### Migration 失敗
1. 查看後端日誌中的錯誤訊息
2. 確認資料庫使用者權限是否足夠
3. 可以手動進入容器執行 migration：
   ```bash
   docker-compose -f docker-compose.prod.yml exec backend php spark migrate --all
   ```

## 開發環境

開發環境請使用原本的 `docker-compose.yml`：

```bash
docker-compose up -d
```

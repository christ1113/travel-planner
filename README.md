# Travel Planner 專案

本專案為一個基於 Laravel + Docker 的旅遊行程規劃系統，包含後端 API 與前端 SPA，後端使用 MySQL 資料庫。

---

## 使用技術

- Laravel 12.19.3
- Docker / Docker Compose
- MySQL 8.0
- PHP 8.2.29

---

## 環境需求

- Docker 20.10以上
- Docker Compose 1.29以上
- Git

---

## 快速部署指南

### 1. 取得專案原始碼

git clone https://github.com/christ1113/travel-planner.git
cd travel-planner

### 2. 複製 `.env` 檔案並修改

cp .env.example .env

編輯 `.env`，確定以下設定：

APP_NAME=Laravel
APP_ENV=local
APP_KEY= # 留空，稍後產生
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=travel_planner
DB_USERNAME=traveluser
DB_PASSWORD=travelpass

### 3. 啟動容器

docker-compose up -d


### 4. 在後端容器內安裝依賴與產生金鑰

docker-compose exec backend bash
composer install
php artisan key:generate


### 5. 進行資料庫遷移

確保 MySQL 容器已啟動，然後：

php artisan migrate

---

## 常見指令

| 功能              | 指令                                            |
| ----------------- | ----------------------------------------------- |
| 進入後端容器      | `docker-compose exec backend bash`              |
| 安裝 PHP 依賴     | `composer install`                              |
| 執行資料庫遷移    | `php artisan migrate`                           |
| 關閉容器          | `docker-compose down`                           |
| 查看容器日誌      | `docker-compose logs -f`                        |

---

## 注意事項

- 若修改 `.env`，建議重啟後端容器並清除快取：

php artisan config:clear
php artisan cache:clear
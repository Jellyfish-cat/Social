@echo off
cd /d D:\xampp\htdocs\Social

start "REVERB" cmd /k D:\xampp\php\php.exe artisan reverb:start
start "QUEUE" cmd /k D:\xampp\php\php.exe artisan queue:work
start "VITE DEV SERVER" cmd /k npm run dev
start "MEILISEARCH" cmd /k .\meilisearch.exe
timeout /t 5 >nul
start "IMPORT POST" cmd /k php artisan scout:import "App\Models\Post" 
timeout /t 2 >nul
start "IMPORT USER" cmd /k php artisan scout:import "App\Models\User" 
timeout /t 2 >nul
start "IMPORT TOPIC" cmd /k php artisan scout:import "App\Models\Topic" 


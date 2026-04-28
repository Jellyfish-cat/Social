@echo off
cd /d D:\xampp\htdocs\Social

start "PYTHON AI RECOMMENDER" cmd /k "cd python_ai_service && python -m uvicorn main:app --reload --port 8001"
start "REVERB" cmd /k D:\xampp\php\php.exe artisan reverb:start
start "QUEUE" cmd /k D:\xampp\php\php.exe artisan queue:work
start "VITE DEV SERVER" cmd /k npm run dev
start "PHP SERVER" cmd /k php artisan serve
start "MEILISEARCH" cmd /k .\meilisearch.exe
timeout /t 5 >nul
start "IMPORT POST" cmd /c php artisan scout:import "App\Models\Post"
timeout /t 2 >nul
start "IMPORT USER" cmd /c php artisan scout:import "App\Models\User"
timeout /t 2 >nul
start "IMPORT TOPIC" cmd /c php artisan scout:import "App\Models\Topic"
timeout /t 2 >nul
start "IMPORT MESSAGE" cmd /c php artisan scout:import "App\Models\Message"

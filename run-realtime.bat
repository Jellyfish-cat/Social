@echo off
cd /d D:\xampp\htdocs\Social

start "REVERB" cmd /k D:\xampp\php\php.exe artisan reverb:start
start "QUEUE" cmd /k D:\xampp\php\php.exe artisan queue:work
start "VITE DEV SERVER" cmd /k npm run dev
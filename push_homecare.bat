
@echo off
setlocal
title Homecare - Push to GitHub

REM ============================================
REM HOMECARE DEPLOYMENT: SERVER TO GITHUB
REM Project : C:\xampp\htdocs\homecare
REM PHP     : C:\php84\php.exe
REM Repo    : turtrsst/homecare
REM Branch  : main
REM ============================================

cd /d C:\xampp\htdocs\homecare
if errorlevel 1 (
    echo [ERROR] Folder Homecare tidak ditemukan.
    pause
    exit /b 1
)

echo.
echo ============================================
echo       HOMECARE - PUSH TO GITHUB
echo ============================================
echo.

REM 1. Pastikan PHP 8.4 tersedia
if not exist "C:\php84\php.exe" (
    echo [ERROR] PHP 8.4 tidak ditemukan.
    pause
    exit /b 1
)

echo [1/7] Pemeriksaan versi PHP...
"C:\php84\php.exe" artisan --version
if errorlevel 1 (
    echo [ERROR] Laravel gagal dijalankan.
    pause
    exit /b 1
)

REM 2. Pastikan direktori Git benar
echo.
echo [2/7] Pemeriksaan repository...
git rev-parse --show-toplevel
if errorlevel 1 (
    echo [ERROR] Folder ini bukan repository Git.
    pause
    exit /b 1
)

REM 3. Pastikan branch main
echo.
echo [3/7] Pemeriksaan branch...
for /f "delims=" %%B in ('git branch --show-current') do set "BRANCH=%%B"

if /i not "%BRANCH%"=="main" (
    echo [ERROR] Branch aktif bukan main: %BRANCH%
    echo Push dibatalkan demi keamanan.
    pause
    exit /b 1
)

REM 4. Pastikan file environment tidak terlacak
echo.
echo [4/7] Pemeriksaan file sensitif...

git ls-files --error-unmatch .env >nul 2>&1
if not errorlevel 1 (
    echo [ERROR] .env sudah terlacak Git.
    echo Periksa dan amankan file sensitif sebelum push.
    pause
    exit /b 1
)

git check-ignore -q .env
if errorlevel 1 (
    echo [ERROR] .env belum diabaikan oleh .gitignore.
    echo Tambahkan .env ke .gitignore terlebih dahulu.
    pause
    exit /b 1
)

git ls-files --error-unmatch .env.bak >nul 2>&1
if not errorlevel 1 (
    echo [ERROR] .env.bak sudah terlacak Git.
    pause
    exit /b 1
)

REM 5. Periksa remote dan perubahan
echo.
echo [5/7] Remote GitHub:
git remote -v
if errorlevel 1 (
    echo [ERROR] Gagal membaca remote.
    pause
    exit /b 1
)

echo.
echo Perubahan yang akan diperiksa:
git status --short

echo.
echo Periksa daftar file di atas.
echo Pastikan tidak ada password, API key, atau data rahasia.
echo.
choice /C YN /M "Lanjutkan add, commit, dan push"
if errorlevel 2 (
    echo Push dibatalkan.
    pause
    exit /b 0
)

REM 6. Tambahkan perubahan dan commit
echo.
echo [6/7] Menambahkan perubahan...
git add -A
if errorlevel 1 (
    echo [ERROR] git add gagal.
    pause
    exit /b 1
)

git diff --cached --check
if errorlevel 1 (
    echo [ERROR] Ada masalah whitespace pada perubahan.
    echo Periksa dengan git diff --cached.
    pause
    exit /b 1
)

git status

echo.
set "MSG="
set /p "MSG=Masukkan pesan commit: "

if not defined MSG (
    echo [ERROR] Pesan commit wajib diisi.
    pause
    exit /b 1
)

git diff --cached --quiet
if not errorlevel 1 (
    echo Tidak ada perubahan baru untuk di-commit.
    goto PUSH
)

git commit -m "%MSG%"
if errorlevel 1 (
    echo [ERROR] Commit gagal.
    pause
    exit /b 1
)

:PUSH
REM 7. Push ke GitHub
echo.
echo [7/7] Push ke GitHub...
git push -u origin main
if errorlevel 1 (
    echo.
    echo [ERROR] Push gagal.
    echo Periksa autentikasi dan koneksi GitHub.
    pause
    exit /b 1
)

echo.
echo ============================================
echo       PUSH HOMECARE BERHASIL
echo ============================================
git log -1 --oneline
git status

pause
endlocal

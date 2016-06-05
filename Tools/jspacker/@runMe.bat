@echo off
cd packer
copy /B *.js engine.js
move engine.js ..\engine.js
cd ..
jsmin <engine.js >engine2.js "(c)2008 Doonoyz"
del engine.js
rename engine2.js engine.js
echo "Engine.js must be compiled at http://dean.edwards.name/packer/ or using -packer-.mht"
echo.
echo "You should use 3.0 until 3.1 is corrected"
pause
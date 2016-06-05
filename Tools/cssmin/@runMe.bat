@echo off
cd packer
copy /B *.css engine.css
move engine.css ..\engine.css
cd ..
cssmin engine.css engine2.css
del engine.css
rename engine2.css engine.css
echo "Done !"
pause
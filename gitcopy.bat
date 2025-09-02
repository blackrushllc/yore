# Copy all Yore code to public repository and push it
echo "Don't forget to sanitize credentials"
pause
copy *.md \projects\blackrushllc\yore
copy *.php \projects\blackrushllc\yore
copy web\*.* \projects\blackrushllc\yore\web
copy web\js\*.* \projects\blackrushllc\yore\web\js
copy web\css\*.* \projects\blackrushllc\yore\web\css
xcopy web\themes\default\*.* \projects\blackrushllc\yore\web\themes\default /s /d /y /c /e
xcopy pages\default\*.* \projects\blackrushllc\yore\pages\default /s /d /y /c /e
xcopy modules\*.* \projects\blackrushllc\yore\modules /s /d /y /c /e
xcopy app\*.* \projects\blackrushllc\yore\app /s /d /y /c /e

cd \projects\blackrushllc\yore
del modules\Chsapp\*.* /s /q
rmdir modules\Chsapp /s /q
git add -A
git commit -m gitcopy
git push
cd \projects\yorr\yore
p wip







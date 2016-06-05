cd bin
dir /s /b c:\Core\*.php > ./list.txt
xgettext --keyword=t_ -f ./list.txt -o ./core.en.po
dir /s /b c:\Doonoyz\*.php >> ./list.txt
xgettext --keyword=t_ -f ./list.txt -o ./messagesTemp.po

;merger des fichiers de traductions
msgmerge.exe c:\Core\Twindoo\Languages\core.en.po ./messagesTemp.po > ./messages.po
msgmerge.exe c:\Doonoyz\application\languages\lang.en.po ./messages.po > ./message.po
msgfmt.exe -c ./message.po
msgfmt -o ./messages.mo ./message.po

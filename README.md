# Temporary Safe House
## EN   
Simple engine for a one-page forum. Can be useful for creating a temporarily private space for communication.   
Logins and passwords are written in the index.php file   
A web server with PHP support is required   
For correct operation, the root folder must contain the folder uploads, styles.css, index.php and posts.html   
The owners of the files and folders must be the user and group www-data   
   
Then copy the folder uploads, styles.css, index.php and posts.html to /var/www/html/   
Do not forget to set the owner to www-data   
chown -R www-data:www-data /var/www/html/   
## RU   
Простой движок для одностраничеого форума. Может быть полезен для создания временно приватного пространства для общения.   
Логины и пароли прописываются в файле index.php   
Необходим веб сервер с поддержкой php   
Для корректной работы в корневой папке должны находиться папка uploads,styles.css, index.php и posts.html   
Владельцами файлов и папок должны быть пользователь и группа www-data   
   
После чего копируем папку uploads,styles.css, index.php и posts.html в /var/www/html/  
Не забываем выставить владельцем www-data   
chown -R www-data:www-data /var/www/html/ 

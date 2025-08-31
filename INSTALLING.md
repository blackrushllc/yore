Yore - A Framework for Applications
```
▄▄▄▄    ██▓    ▄▄▄       ▄████▄   ██ ▄█▀ ██▀███   █    ██   ██████  ██░ ██
▓█████▄ ▓██▒   ▒████▄    ▒██▀ ▀█   ██▄█▒ ▓██ ▒ ██▒ ██  ▓██▒▒██    ▒ ▓██░ ██▒
▒██▒ ▄██▒██░   ▒██  ▀█▄  ▒▓█    ▄ ▓███▄░ ▓██ ░▄█ ▒▓██  ▒██░░ ▓██▄   ▒██▀▀██░
▒██░█▀  ▒██░   ░██▄▄▄▄██ ▒▓▓▄ ▄██▒▓██ █▄ ▒██▀▀█▄  ▓▓█  ░██░  ▒   ██▒░▓█ ░██
░▓█  ▀█▓░██████▒▓█   ▓██▒▒ ▓███▀ ░▒██▒ █▄░██▓ ▒██▒▒▒█████▓ ▒██████▒▒░▓█▒░██▓
░▒▓███▀▒░ ▒░▓  ░▒▒   ▓▒█░░ ░▒ ▒  ░▒ ▒▒ ▓▒░ ▒▓ ░▒▓░░▒▓▒ ▒ ▒ ▒ ▒▓▒ ▒ ░ ▒ ░░▒░▒
▒░▒   ░ ░ ░ ▒  ░ ▒   ▒▒ ░  ░  ▒   ░ ░▒ ▒░  ░▒ ░ ▒░░░▒░ ░ ░ ░ ░▒  ░ ░ ▒ ░▒░ ░
░    ░   ░ ░    ░   ▒   ░        ░ ░░ ░   ░░   ░  ░░░ ░ ░ ░  ░  ░   ░  ░░ ░
░          ░  ░     ░  ░░ ░      ░  ░      ░        ░           ░   ░  ░  ░
░                  ░

```

```
Copyright (C) 2025, Blackrush LLC, All Rights Reserved
Created by Erik Olson, Tarpon Springs, Florida
For more information, visit BlackrushDrive.com
```

# Installation Instructions for Yore Framework

## Prerequisites
- PHP 7.0 or higher installed on your system.
- Composer installed for dependency management.
- A web server (e.g., Apache, Nginx) or PHP's built-in server for running the application.
- A database server (e.g., MySQL, PostgreSQL) if your application requires one.


## Step 1: Download Yore Framework
(You can download the Yore framework from its official repository or website.)

Bash/Terminal:
```git clone https://github.com/blackrushllc/yore```

## Step 2: Navigate to the Project Directory
```cd yore```
## Step 3: Install Dependencies
```composer install```
## Step 4: Configure the Web Server Virtual Host
- For Apache, create a new virtual host configuration file (e.g., `yore.conf`) in the Apache `sites-available` directory:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /path/to/yore/public

    <Directory /path/to/yore/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/yore_error.log
    CustomLog ${APACHE_LOG_DIR}/yore_access.log combined
</VirtualHost>
```
- Enable the site and rewrite module:
```bash
sudo a2ensite yore.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
``` 
- For Nginx, create a new server block configuration file (e.g., `yore`):
```nginx
server {
    listen 80;
    server_name yourdomain.com;

    root /path/to/yore/public;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock; # Adjust PHP version as needed
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```
- Enable the site and restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/yore /etc/nginx/sites-enabled/
sudo systemctl restart nginx
```

## Step 5: Set Up Environment Configuration 
(TODO)

## Step 5a: Configure the Database Module

## Step 6: Set Permissions
```bash
sudo chown -R www-data:www-data /path/to/yore/   
sudo chmod -R 775 /path/to/yore/
```



## Step 7: Run Database Migrations for sample applications
```bash ./yore cli migrate```



## Optional: Configure Default Modules
(TODO)
* Admin
* Basic
* Database
* Debug
* Library
* Mail
* Users

## Step 8: Access the Application

- Open your web browser and navigate to `http://yourdomain.com` or `http://localhost` if you're running it locally.
- You should see the Yore framework welcome page or your application's homepage.
- If your application has not been developed, you will be prompted with a wizard to create some example application pages.
- Follow the on-screen instructions to set up your application.

Yore will create a new website for each domain name that you are using. Make sure that your web server is configured to point to the `web` directory of the Yore framework.

Yore sites will automatically use all of the modules that you have installed and configured by default. Use the modules.json file in the root of your domain folder to enable or disable modules for that specific domain as well as override any global module settings.

You can also create a "modules" folder in the root of your domain folder, with subfolders to any modules that you are using in order to to customize module settings (in settings.json) and create view overrides that (in a folder called modules/<Module Name>/views/) are specific to that domain, although this is not required for most modules.

See the sample applications for lots of examples of how to use the modules and create your own applications.
 
## Troubleshooting 
 

- If you encounter any issues, check the web server error logs for troubleshooting.
- For Apache, check `/var/log/apache2/yore_error.log`.
- For Nginx, check `/var/log/nginx/yore_error.log`.
- Ensure that your database server is running and that the connection details in your configuration file are correct.
- Make sure that the web server has the necessary permissions to read and write to the Yore framework directory.
- If you are using a firewall, ensure that it allows traffic on the port your web server is listening on (usually port 80 for HTTP and port 443 for HTTPS).
- Ensure that the server name matches the domain you are accessing in your web browser.
- Ensure that your SSL certificates are correctly configured.
- If you are using a database, ensure that the database server is running and that the connection details in your configuration file are correct.
- If you are using a mail server, ensure that it is configured correctly and that the web server can connect to it.




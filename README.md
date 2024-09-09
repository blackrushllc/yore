Yore - A Framework for Applications

https://yoreweb.com

#Directory Structure

- /
  - api - (defuncty ..)
  - app
    - Controller.php - This is the main controller for the whole framework, you could extend this
    - Database.php - 
    - Library.php - This is a parent class for the controller with the stuff you shouldn't change
    - Process.php - 
  - modules
    - Basic - A BASIC scripting interpreter for use by other modules
    - Courses - The Yore LMS Framework Application Objects (FAO) (LMSFAO LOL)
    - Database - DB Connectivity package for all Yore modules
    - Debug - Website Debugging tools / Debug mode
    - Games - 
    - Hello - Example Module with lots of comments
    - Logging - Yore site logging package
    - Twilite - A Twilio extension of the BASIC module
    - Users - Yore Users, Login, Register, Roles, Profiles, etc
  - pages
    - _domains - Sites under various domain names
      - domain1.com
        - api (defuncty ..)
        - default
          - views - FYI if a view is not provided, then just "@body()" is assumed
            - page1.fred.php
            - home.fred.php
          - home.json
          - page1.json
        - site1
          - views
            - home.fred.php
            - page1.fred.php
            - page2.fred.php
            - etc.fred.php
          - home.json
          - page1.json
          - page2.json
          - etc.json
        - site2
          - views
            - home.fred.php
            - page1.fred.php
            - page2.fred.php
            - etc.fred.php
          - home.json
          - page1.json
          - page2.json
          - etc.json
        - site3 etc... etc...
      - domain2.com etc... etc...
      - domain3.com etc... etc...
    - default - DEFAULT WEBSITE if no matching "_domains" entry
      - views
        - home.fred.php
      - home.json
    - site1 (i.e. /site1 uses home)
      - views - FYI if a view is not provided, then just "@body()" is assumed
        - home.fred.php (i.e. /site1 uses home)
        - page1.fred.php
        - page2.fred.php
      - home.json (i.e. /site1 uses home)
      - page1.json (i.e. /site1/page1)
      - page2.json (i.e. /site1/page2)
    - site2
      - views
        - home.fred.php
        - page1.fred.php
        - page2.fred.php
      - home.json
      - page1.json
      - page2.json
    - site3 etc... etc...
  - tests
  - vendor - composer packages get installed here (not in repo)
  - web
    - css
    - images
    - js
    - themes
      - domain1
        - css
        - html - contains header, navbar & footer for all pages
        - images
        - js
      - domain2 etc..
      - somecooltheme etc..
      - default etc..
    - index.php - main entry point for everything everything



Be sure that the following lines are in your web host config

```
        <Directory /path/to/web>
            Options Indexes FollowSymLinks MultiViews
            AllowOverride All
            Require all granted
            RewriteEngine on
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ /index.php/$1 [NC,L]
        </Directory>
```

For example, for Apache your sites-available ssl config might look like this: 


```
<IfModule mod_ssl.c>
<VirtualHost *:443>
        ServerName blackrush.us
        <Directory /var/www/yore/web>
            Options Indexes FollowSymLinks MultiViews
            AllowOverride All
            Require all granted
            RewriteEngine on
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ /index.php/$1 [NC,L]
        </Directory>

        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/yore/web
        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined

SSLCertificateFile /etc/letsencrypt/live/erikleeolson.com-0001/fullchain.pem
SSLCertificateKeyFile /etc/letsencrypt/live/erikleeolson.com-0001/privkey.pem
Include /etc/letsencrypt/options-ssl-apache.conf
</VirtualHost>
</IfModule>


```


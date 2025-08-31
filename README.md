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
Copyright (C) 2024, Blackrush LLC, All Rights Reserved
Created by Erik Olson, Tarpon Springs, Florida
For more information, visit BlackrushDrive.com
```


MIT License

Copyright (c) 2025 Erik Lee Olson for Blackrush, LLC

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.




# Purpose

Yore is a web framework for building applications and multi-site domain hosting.

This application is still under development with 8 fundamental pillars in mind:

+ An All-purpose MVC
+ Versatile View Template Engine
+ Tightly integrated Module Engine
+ Server-side Controller Programming Language
+ Apps can be deployed anywhere you can store data
+ Yore apps can be interconnected and remotely instantiate classes
+ Yore has built-in version control and deployment
+ The Yore frameworks are ported to numerous programming languages serving the same content

## Yore is not complete!  Where fundamental pillars fail to provide features, I am currently coding into Custom Modules

((MY LONG TERM GOAL FOR THIS FRAMEWORK IS NOT NOT HAVE TO RELY ON CUSTOM MODULES TO BUILD ALMOST ANY KIND OF APP))

One of my goals with this project is to create a _prototype_ application framework which
can render a complete web site without storing any of the custom MVC code locally.  The web server
(or whatever environment is being used to render the applications) needs only to reference a named
data source to render the entire app on any device.

Additionally, I intend to have complete abstraction between the rendering server and the site contents,
allowing for the rendering server to be platform independent and interpreting the content using layers
written in Php, Go, Python, or even C++ as long as they know how to interpret, execute and render the
MVC content.

The MVC code, consisting of JSON data, HTML views, and a new script language system can be built manually using a directory structure and then compiled to a portable data source, or a web environment can be built to build the site data directly into the data source without the need for intermediate files.

My current goal is to be able to create the local file structure and compile it to SQL, and then decompile the SQL back down to a local file structure for manual development.

My ultimate goals include being to have:
+ Agnostic data sourcing, not just SQL, the compiled site could, for instance be stored in:
  + GitHub
  + FTP/S3 etc
  + Data storage services
  + A Google Sheet (LOL but no really)
  + Caching service or CDN
  + etc
+ A development environment which works off of the aforementioned data sources (I'm currently just manally editing HTML and JSON files in a local file system)
+ The address of the data source would be all the instance of Yore needs to render the website
+ Yore is extended by Modules, so as long as an instance has the modules that may be required by the site data then it will run
+ A custom module would be needed if the developer cannot do something with the standard tools and modules 
+ My long term goal is to be able to build instances of Yore for multiple platforms and in multiple base languages.  For instance, this Php version of the Yore framework could also be converted to Python, C# or Node but would still render the website just the same because the framework functionality would be the same, hence my calling this a "prototype".
+ This way you could build a massive web application that may be using Php today but tommorrow you could have the same website running on a completely different platform with no changes.
+ Also, a native app could be used to read and render an entire Yore website as a mobile app, allowing people to develop interesting mobile apps using their knowlege of Yore

The data source should have a unique identifier like @clownworld or !vampires! although I don't much like the idea of having special characters in it, but it does need to be something in the URL that Yore will instantly recognize as a data source ID.

Then, Yore fetches and caches the data source or just uses the cache if it hasn't changed and presents the website.

This way, any Yore host or Yore mobile app can be called with the data source ID and present the website,

The data source also provides a remote database if needed by the website.

Idea: Any Yore site will also include a module which gives users the ability to generate their own Yore site using the same data source provider, encouraging both Yore site owners and data source providers to make this feature available and promote Yore in general




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


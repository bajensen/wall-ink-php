# Wall Ink PHP Server

## Overview
This project attempts to be a pure PHP server solution for the 
open source display at https://github.com/caedm/wall-ink. 

## Installation Instructions


### Steps
1. Clone/download source code
2. Create MySQL database, schema, and tables
3. Install and configure Apache with PHP
4. Install dependencies by running composer install
5. Create wall ink server configuration
6. Configure wall ink hardware
7. Visit admin page to configure layout and source

### Step 1: Clone/download source code

You will need to either clone this repository or download a ZIP of it. 
Extract or clone to the directory you will use as your web server root. 
In my examples, I will be using `/opt/wink`.  

#### Example on CentOS 7:
```bash
bob$ sudo -i
root$ yum install -y epel-release
root$ yum install -y git wget
root$ mkdir -p /opt/wink
root$ chown bob:bob /opt/wink
root$ exit
bob$ cd /opt/wink
bob$ git clone https://github.com/bajensen/wall-ink-php.git .
```


### Step 2: Create MySQL database, schema, and tables

#### Requirements:
- MySQL >= 5.6
- If you already have a MySQL installation, you must be able to create users and databases.

#### Steps:
- Open your favorite MySQL management console and create a new user/password combination.
- Create a new database (schema)
- Grant access for your new database to your new user
- Execute the SQL found in `docs/schema.sql` to create the proper database tables

#### Example on CentOS 7:

Install MySQL, enable its service, and secure it.
Source for these instructions is https://www.linode.com/docs/databases/mysql/how-to-install-mysql-on-centos-7/

```bash
bob$ wget http://repo.mysql.com/mysql-community-release-el7-5.noarch.rpm
bob$ sudo rpm -ivh mysql-community-release-el7-5.noarch.rpm
bob$ sudo -i
root$ yum install -y mysql-server
root$ systemctl enable --now mysql.service
root$ mysql_secure_installation
```

Create user and database.
```bash
bob$ mysql -u root -p
MySQL> CREATE USER 'wink'@'%' IDENTIFIED BY 'super secret password';
MySQL> CREATE DATABASE wink;
MySQL> GRANT ALL PRIVILEGES ON wink.* TO 'wink'@'%';
MySQL> FLUSH PRIVILEGES;
MySQL> QUIT;
```

Populate the the database with the proper tables.
```bash
bob$ cd /opt/wink
bob$ mysql -u root -p wink < docs/schema.sql
```


### Step 3: Install and configure Apache with PHP
This step depends on what operating system and distribution you are using. 
Please find instructions online for your environment, but make sure to satisfy the 
requirements given below.

#### Requirements:
- PHP >= 5.6 (7.1 or higher recommended)
- PHP Extension: JSON
- PHP Extension: PDO
- PHP Extension: PDO for MySQL
- PHP Extension: ImageMagick (Imagick)
- Apache >= 2.4
- Proper TCP port for HTTP open (usually 80 unless you use a custom port)

#### Example on CentOS 7:
Source for these instructions is https://webtatic.com/packages/php72/
```bash
bob$ sudo -i
root$ yum install -y epel-release
root$ rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
root$ yum install -y httpd mod_php72w php72w-cli php72w-pdo php72w-pecl-imagick
root$ cat > /etc/httpd/conf.d/wink.conf
apache-development.conf
root$ systemctl enable httpd
root$ systemctl start httpd
root$ firewall-cmd --add-service=http --permanent
root$ firewall-cmd --reload
```

Now open a browser and navigate to your server's hostname. 
If everything is installed and configured correctly, you should get a page that says
```text
Autoload file not found. Run composer install.
```

### Step 4: Install dependencies by running composer install
Source for these instructions is https://getcomposer.org/download/
```bash
bob$ cd /opt/wink
bob$ php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
bob$ php -r "if (hash_file('sha384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
bob$ php composer-setup.php
bob$ php -r "unlink('composer-setup.php');"
```

```bash
bob$ php composer.phar install
```

Navigate to your server's hostname in a browser again. You should now see an error like this:
```test
Slim Application Error
The application could not run because of the following error:

Details
Type: PDOException
Message: invalid data source name
File: /opt/wink/src/Wink/DB/PDO.php
Line: 12
```

This mean we are ready for the next step.

### Step 5: Create wall ink server configuration

#### Create Configuration File
Copy the sample development configuration file `config/config.development.example.php` 
to `config/config.development.php` and update the settings to their proper values.

Try refreshing your browser. It should now show a header and blank device table.

Congrats! We are almost there!!!

#### Example on CentOS 7:
```bash
bob$ cd /opt/wink
bob$ cp config/config.development.example.php config/config.development.php
bob$ vi config/config.development.php
```

### Step 6: Configure wall ink hardware

Follow the instructions at https://github.com/caedm/wall-ink/wiki/Admin-mode to configure
the hardware settings.

#### Example Configuration:
```text
Wireless SSID0: MiWiFi 
Password0: MiWiFiPass
Wireless SSID1: MiWiFi2
Password1: MiWiFiPass
Base URL: http://wink.example.com/display
Image Key: hunter2
Debug Mode: Off
```

### Step 7: Visit admin page to configure layout and source
Once the display connects, it will create a record for the display in the database.

In your browser, navigate to your server's address and you should get the admin page. 
Click the device and pick the settings you want to operate with!

Everything should be up and ready to go!!!!!

For more information on developing custom layouts and sources, see `DEVEL.md`.


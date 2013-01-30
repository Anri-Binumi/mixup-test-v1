README
======

Great, you found the instructions!
Now follow these steps:

1. Install Zend Framework 1.12.x
================================

Download Zend Framework from here:
http://framework.zend.com/releases/ZendFramework-1.12.0/ZendFramework-1.12.0-minimal.zip

Unpack the ZendFramework-1.12.0-minimal.zip somewhere OUTSIDE of the project folder.
Adjust the path in "public/index.php" to point to the unpacked Zend Framework library folder (/path/to/ZendFramework-1.12.0-minimal/library).


2. Edit your hosts file
=======================

Add the following line to your hosts file
127.0.0.1       mixup2.com


3. Setting up your VHOST
========================

Here's a sample VHOST configuration you might want to use.

<VirtualHost *:80>

    ServerName mixup2.com

    # Adjust this path !
    DocumentRoot "/Users/anri/dev/mixup-test-v1/public"

    SetEnv APPLICATION_ENV "development"

    # Adjust this path !
    <Directory "/Users/anri/dev/mixup-test-v1/public">

        Options -Indexes MultiViews FollowSymLinks
        DirectoryIndex index.php
        AllowOverride All
        Order allow,deny
        Allow from all

    </Directory>

</VirtualHost>


4. Importing our database
=========================

mysql -u root < sql/create.sql


5. Browse to mixup.com test page
================================

Now point your favorite browser (Chrome obviously) to http://mixup2.com and check out the Test Page for more instructions!



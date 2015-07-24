This project attempts to benchmark the baseline level of responsiveness of various PHP frameworks to discover the overhead involved in using each one.

It not extactly a fork, but it is based on the project here (Paul M. Jones): <https://github.com/pmjones/php-framework-benchmarks>

I am just following the protocol (partly) by pmjones

Notes

> This is answser to the reddit thread **"is it me, or Laravel Lumen is actually pure sham?"**
   <https://www.reddit.com/r/PHP/comments/3dz8hi/is_it_me_or_laravel_lumen_is_actually_pure_sham/>
> the article: http://webspicy.blogspot.fr/2015/07/is-it-me-or-laravel-lumen-is-actually.html
> and to <http://taylorotwell.com/how-lumen-is-benchmarked/>
> 
>  I have been questionned about the results, and usually people when they are challenged don't take time to prove what they say. This time I took it (thanks to @lordofworms at reddit ;-) )

Notes 2:
> I recorded the entire process --> see https://www.youtube.com/watch?v=QA07YEIRN4Q&feature=em-upload_owner
> This is how you can reproduce this from scratch - using an Amazon EC2 machine.


Benchmarking Server Setup
=========================

Hardware and Operating System
-----------------------------

The benchmark is performed on an Amazon EC2 `t1.micro` instance because noone uses `m1.large` instance for a stupid web server. 
I think thta running in low end EC2 tend to highlight the results.  
The operating system is a stock 64-bit Debian 8.1 

Details:
- t1.micro 0.613 EBS only 
- Linux/Unix, Debian 8.1 | 64-bit Amazon Machine Image (AMI) | Updated: 6/24/15

Installation instructions for EC2 are beyond the scope of this project. 
Created using the EC2 web console.


Software Installation - Debian 8.1 
----------------------------------

After the instance comes online, issue the following shell commands to install and configure the necessary packages.

    # become root
    sudo -s

    # initial updates (aptitude is no more installed by default)
    apt-get update
    apt-get upgrade -y
    
    
    #-->apache2, php, curl 
    apt-get install -y \
        libapache2-mod-php5 \
        curl \
        php5 
     
    
    # modify the Apache DocumentRoot for the .htaccess
    sed -i "s/AllowOverride None/AllowOverride All/" /etc/apache2/apache2.conf 
    
    # turn on mod_rewrite
    a2enmod rewrite
    
    # restart apache
    service apache2 restart    

    
Now you can run the benchmarks against a series of framework targets.


Install the frameworks
======================

### install composer
--------------------
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer

### install lumen
--------------------
Got the procedure from the lumen website

    cd /var/www/html
    rm index.html
    
    composer create-project laravel/lumen --prefer-dist
    # here it fail because git ask you to create a personal token on their website.
    # You follow the link,
    # you copy/paste it & the composer install goes to the end.
    
    #route to capture every route that's passed through.
    
    cp /var/www/html/lumen/app/Http/routes.php /var/www/html/lumen/app/Http/routes.old.php
    
    cat <<LONGSTRING  >>/var/www/html/lumen/app/Http/routes.php
    \$app->get('{path:.*}', function(\$path)
    {
          header('Content-Type: '.('application/json').'; '.'charset=utf-8');
          echo json_encode(array('id'=>rand(),'name' => 'mickey', 'state' => \$_SERVER['REQUEST_METHOD']));
            
    });
    LONGSTRING

### Install Slim
--------------------

    mkdir slim3
    cd slim3/
    composer require slim/slim
    
    
    cat <<LONGSTRING  >>/var/www/html/slim3/.htaccess
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [QSA,L]
    LONGSTRING
    
    #will be reachable via **http://<ip>/slim3/api/fake/mickey**
    cat <<LONGSTRING  >>/var/www/html/slim3/index.php
    <?php
    require 'vendor/autoload.php';
    
    \$app = new \Slim\Slim();
    \$app->get('/api/fake/:name', function (\$name) {
        header('Content-Type: '.('application/json').'; '.'charset=utf-8');
         echo json_encode(array('id'=>rand(),'name' => 'mickey', 'state' => \$_SERVER['REQUEST_METHOD']));
    
    });
    \$app->run();
    LONGSTRING
    
### Install Slim
--------------------

Debian GNU/Linux 8.1 (jessie)

PHP 5.6.9-0+deb8u1 (cli) (built: Jun  5 2015 11:03:27)

 7:30 for the setup from scratch


Running the Benchmarks
======================


I took the same ab command as from http://taylorotwell.com/how-lumen-is-benchmarked/.
Put it in an alias to ease the thing.

    alias bench='ab -t 10 -c 10 -k '

now the bench commands will be  

    bench http://<ip>/lumen/public/|grep "Requests per second"

At the end of the benchmark execution, there's no collating - you will just take the output from the console ;-)

Run the bench command several times to be certain to get an real average value.


Benchmarks Logs 2015 07 24
==========================


**LUMEN (without optimisitation)**

    Requests per second:    295.18 [#/sec] (mean)
    Requests per second:    294.84 [#/sec] (mean)
    Requests per second:    291.27 [#/sec] (mean)
    Requests per second:    296.27 [#/sec] (mean)
    Requests per second:    302.16 [#/sec] (mean)

**LUMEN (with optimisitation)** - as per Otwell page  
Yes, composer seems actually slowing it down !

    Requests per second:    268.00 [#/sec] (mean)
    Requests per second:    263.43 [#/sec] (mean)
    Requests per second:    265.45 [#/sec] (mean)
    
this is the selected empirical average value
don't question it, the test is wrong (because the client is in the same box)
;-)

**Slim 3**

    Requests per second:    494.07 [#/sec] (mean)
    Requests per second:    494.91 [#/sec] (mean)
    Requests per second:    497.98 [#/sec] (mean)
    
**So Slim 3 seems 60% to 70% faster**

(We seen a lot of variation during the tests)



Benchmarks Logs 2015 07 24 - Extended
======================================

LARAVEL (veeeery long)

    Requests per second:    43.29 [#/sec] (mean)
    Requests per second:    45.47 [#/sec] (mean)
    
(I am not patient enough to do several tests)
is there any difference if we "optimize"??
    
    Requests per second:    44.75 [#/sec] (mean)
    Requests per second:    42.22 [#/sec] (mean)
    
The 3 frameworks (badly) compared:

    LUMEN   Requests per second:    242.29 [#/sec] (mean)
    SLIM3   Requests per second:    485.59 [#/sec] (mean)
    LARAVEL Requests per second:     43.29 [#/sec] (mean)
 
So it is:
- 1 order of magnitude slim vs laravel
- x2 slim3 vs lumen (slim 2 is faster than slim 3)

that's the conclusion of this part.

Benchmarks Logs 2015 07 24 - Extended Extended
==============================================

**Let's measure the bloat**

Le'ts see the same thing with Raw PHP:

    Requests per second:    5179.26 [#/sec] (mean)
    Requests per second:    5248.06 [#/sec] (mean)

It shows how much we loose with the framework. What is the acceptable loss? you decide.

All commands at the same moment: 

    bench http://<IP>/lumen/public/|grep "Requests per second"
    bench http://<IP>/slim3/api/fake/mickey|grep "Requests per second"
    bench http://<IP>/laravel/public/|grep "Requests per second"
    bench http://<IP>/raw|grep "Requests per second"
    bench http://<IP>/fatfree-composer-app-master/|grep "Requests per second"
    

So this is **"the cost of the bloat"** ;-)

    LUMEN 		Requests per second:    240.53 [#/sec] (mean)
    SLIM3 		Requests per second:    497.75 [#/sec] (mean)
    LARAVEL 5 	Requests per second:    47.27 [#/sec] (mean)
    RAW PHP 	Requests per second:    5248.06 [#/sec] (mean)

    
**Just to play, let's compare with fatfree**

this is a fatfree project that uses composer (not mandatory with F3)
https://github.com/F3Community/fatfree-composer-app

**FATFREE Composer**

    Requests per second:    662.44 [#/sec] (mean)
    Requests per second:    663.94 [#/sec] (mean)
    Requests per second:    682.47 [#/sec] (mean)
    
Let's redo this:

    bench http://<IP>/lumen/public/|grep "Requests per second"
    bench http://<IP>/slim3/api/fake/mickey|grep "Requests per second"
    bench http://<IP>/laravel/public/|grep "Requests per second"
    bench http://<IP>/raw|grep "Requests per second"
    bench http://<IP>/fatfree-composer-app-master/|grep "Requests per second"
    
it should be clear now: 

    LUMEN Requests per second:    254.17 [#/sec] (mean)
    SLIM3 Requests per second:    473.72 [#/sec] (mean)
    LARAVEL 5 Requests per second:    45.08 [#/sec] (mean)
    RAW PHP Requests per second:    4580.21 [#/sec] (mean)
    FATFREE COMPOSER Requests per second:    655.92 [#/sec] (mean)


Benchmarks Logs 2015 07 24 - Extended x 3
=========================================

Before they ask: 
let's fine tune opcache

**Before opcache opt**

Run:
 
    bench http://<IP>/lumen/public/|grep "Requests per second"
    bench http://<IP>/slim3/api/fake/mickey|grep "Requests per second"
    bench http://<IP>/laravel/public/|grep "Requests per second"
    bench http://<IP>/raw|grep "Requests per second"
    bench http://<IP>/fatfree-composer-app-master/|grep "Requests per second"
    
Results:

    Requests per second:    250.88 [#/sec] (mean)
    Requests per second:    493.69 [#/sec] (mean)
    Requests per second:    44.70 [#/sec] (mean)
    Requests per second:    5179.38 [#/sec] (mean)
    Requests per second:    651.75 [#/sec] (mean)
    
**After opcache opt**

    Requests per second:    215.47 [#/sec] (mean)
    Requests per second:    365.61 [#/sec] (mean)
    Requests per second:    1.69 [#/sec] (mean)
    Requests per second:    424.66 [#/sec] (mean)
    Requests per second:    418.82 [#/sec] (mean)
    
(worst!)was it supposed to optimize? ;-)
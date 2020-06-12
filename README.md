# pdf-generator

#required software and its version
APACHE 2.4.37
PHP 7.3.1
SYMFONY 4.4.9
MYSQL 5.7.24 

#SET-UP:
* Clone the project from git repo 
* Change the working directory to pdf_generator
* Configure the virtual host for the project directory (pdf_generator)
* Run the command to update the vendor folder
     composer install
*  clear symfony cashe 
    php bin/console cache:clear --ENV=prod
    php bin/console cache:clear --ENV=dev
* edit .env file to setup database configuration and jwt(add private and public key)
* Create a database called "pdf"
* Run a command to update the db stucture
    php bin/console  doctrine:schema:update --dump-sql
    php bin/console  doctrine:schema:update --force 
* enable the symfony profiler (in dev mode*)
* run a command to update the css and js files
    php bin/console assets:install
* Create a folder pdf  under public/ if not exist
* create a file called "deletefile.log" under var/log/
    
* command to set cron
    php bin/console app:dlete-pdf
#Required symfony bundles

1. sonata-project/admin-bundle
2. dompdf/dompdf
3. lexik/jwt-authentication-bundle
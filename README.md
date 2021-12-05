# Exoplanet-Query
Small project wich running under Symfony.

## About

Since 1992 over 4,000 exoplanets have been discovered outside our solar system. The United States National Aeronautics and Space Administration (NASA) maintains a publicly accessible archive of the data collected on these in comma separated value (CSV) format.

**This realisation is a practical work from [this User story](https://github.com/florinpop17/app-ideas/blob/master/Projects/3-Advanced/NASA-Exoplanet-Query.md).**

[Online demonstration](http://exoplanets.mickael-outhier.fr/).

## How to work on the code

### Download and deploy the sources

**The better way is to use Git, Composer and NPM.**

Use the following command line to get the sources, download the back-end and front-end libraries, set you environment.

```
cd [PATH_TO_YOUR_LOCAL_WWW_FOLDER]
git clone https://github.com/moDevsome/Exoplanet-Query.git
cd [PATH_TO_YOUR_LOCAL_WWW_FOLDER]/Exoplanet-Query
composer install
npm install
php deploy-dev.php
```
### Download and import the local database

1. Go to [this page](https://exoplanetarchive.ipac.caltech.edu/cgi-bin/TblView/nph-tblView?app=ExoTbls&config=PS)
2. Click on "Download table" button, then check "CSV Format" and click on "Download table" (green arrow) button
3. Download the file and past it into the folder : Exoplanet-Query/var/app_files
4. Rename the file to "DB.csv"

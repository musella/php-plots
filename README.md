php-plots
=========

PHP based web index for image displaying.

Original code from Giovanni Petrucciani (@gpetruc).

This project contains a PHP web index script to help visualizing folders with many images.

It is tailored to people working in High Energy Physics that use ROOT (http://root.cern.ch) to produce their plots.

# Setup

1. cd into your web folder

        cd <my-path>
        
1. Clone this repository

        git clone https://github.com/musella/php-plots.git .
        
1. Copy the example/htaccess file into .htaccess and edit its content to suit your needs.
 
        cp -p example/htaccess .htaccess
        $EDITOR .htacces

1. Open the web folder into your browser.

1. Enjoy.


# Features

1. Detect if a file is present with multiple formats.

1. Filter files to be selected with wild-cards or regex.

1. Zoom in/out images with double-click.

1. Rearrange images with drag and drop.

1. Overlay content of .txt version on mouse hover.

# Example

https://musella.web.cern.ch/musella/php-plots

# Troubleshooting
## Broken style
Remember that the ```res``` folder should be copied in the same place as ```index.php```.

## Links not correct, even when changing URL
* With the new EOS websites, both ```https://[user].web.cern.ch/[user]/...``` and ```https://[user].web.cern.ch/...``` will work. However, only the latter should be used for ```php-plots``` to work.
* It seems that the script is not working if placed at the root of the website. Instead, it should be placed into a directory (e.g. ```higgs```, together with ```res``` and ```.htaccess```), and it will apply to all its subdirectories (```https://[user].web.cern.ch/higgs/some/path```).
  * If needed for another directory too (e.g. ```https://[user].web.cern.ch/otherdir/...```), files (```index.php```, ```res```, and ```.htaccess```) need to copied there too (adapting ```.htaccess``` accordingly).

## Identification problems
After the migration of personal and project web pages from AFS to EOS, the old way to restrict access to some pages (using ```.htaccess```, as in ```example/htaccess```) no longer works. Instead, a web interface (https://application-portal.web.cern.ch/) should be used, see also https://auth.docs.cern.ch/user-documentation/saml/shibboleth-migration/#configuration-for-cern-single-sign-on.

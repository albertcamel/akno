#!/bin/bash

SRCDIR=/var/www/vhosts3/vhosts/GRUNT-M2/

while getopts d:t:p option
do
    case "${option}"
    in
        d) DESTDIR=${OPTARG};;
        t) THEMENAME=${OPTARG};;
        p) WATCHPORT=${OPTARG};;
    esac
done

# Commands
mr2='sudo -u www-data /usr/bin/php7.1 /usr/local/bin/n98-magerun2.phar'

# Ask for confirmation
read -p "Do you want to add grunt to the following directory ${DESTDIR} ? (y/n) " answer
case ${answer:0:1} in
    y|Y )
        echo
        echo "Getting grunt from ${SRCDIR}"
        echo "Dir ${DESTDIR}"
        echo "Theme ${THEMENAME}"
        echo "Port ${WATCHPORT}"
        echo "Adding Grunt..."

        cp -rf ${SRCDIR}* ${DESTDIR}

        echo
        echo "Grunt added"

        echo
        echo "Cleaning cache"
        $mr2 cache:clean
    ;;
    * )
        echo "Aborting"
        echo
        exit 0;
    ;;
esac


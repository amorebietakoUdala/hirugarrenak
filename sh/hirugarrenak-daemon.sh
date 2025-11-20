#!/bin/bash

NETFOLDER=/var/www/SF7/hirugarrenak

sudo -u informatika -s `php $NETFOLDER/bin/console messenger:consume async -vv &>> $NETFOLDER/var/log/hirugarrenak-daemon.log`

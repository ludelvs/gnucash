#!/bin/sh

set -su

LOG_STREAM=/tmp/stdout

if ! [ -p $LOG_STREAM ]; then
  if [ -f $LOG_STREAM ]; then rm $LOG_STREAM; fi
  mkfifo $LOG_STREAM
  chmod 666 $LOG_STREAM
fi

/bin/sh -c php-fpm -D | tail -f $LOG_STREAM

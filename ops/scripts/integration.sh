#!/bin/bash

if [ $# -eq 0 ]
  then
    VERSIONS="5.5 5.6 7.0";
  else
    VERSIONS="$1";
fi

function test {
  PHP="php-$1"
  $PHP bin/behat --no-interaction
}

for version in $VERSIONS; do
  printf "\nTesting PHP $version\n"
  test $version
done
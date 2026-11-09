#!/bin/sh

if [ $# -eq 0 ]
  then
    VERSIONS="7.1 7.2 8.4";
  else
    VERSIONS="$1";
fi

test() {
  PHP_CMD="php-$1"
  # Check if php-{version} exists, otherwise use php
  if ! command -v $PHP_CMD > /dev/null 2>&1; then
    PHP_CMD="php"
  fi
  $PHP_CMD bin/behat --no-interaction
}

for version in $VERSIONS; do
  printf "\nTesting PHP $version\n"
  test $version
done
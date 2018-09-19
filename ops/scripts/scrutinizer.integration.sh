#!/bin/bash

phpenv versions

function test {
  phpenv local $1
  echo Current selected version
  phpenv version
  php -v
  php bin/behat --no-interaction
}

for version in 7.1 7.2; do
  echo "Testing PHP $version"
  test $version
done
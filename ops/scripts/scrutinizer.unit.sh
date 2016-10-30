#!/bin/bash

phpenv versions

function test {
  phpenv local $1
  echo Current selected version
  phpenv version
  php -v
  php bin/phpspec run --no-interaction
}

for version in hhvm 5.5.25 5.6.16 7.0.7; do
  echo "Testing PHP $version"
  test $version
done
#!/bin/sh

if [ $# -eq 0 ]
  then
    VERSIONS="7.1 7.2 8.4";
  else
    VERSIONS="$1";
fi

EXIT_CODE=0

test() {
  PHP_CMD="php-$1"
  # Check if php-{version} exists, otherwise use php
  if ! command -v $PHP_CMD > /dev/null 2>&1; then
    PHP_CMD="php"
  fi
  $PHP_CMD bin/phpspec run --no-interaction
  TEST_EXIT=$?
  if [ $TEST_EXIT -ne 0 ] && [ $EXIT_CODE -eq 0 ]; then
    EXIT_CODE=$TEST_EXIT
  fi
}

for version in $VERSIONS; do
  echo "Testing PHP $version"
  test $version
done

exit $EXIT_CODE
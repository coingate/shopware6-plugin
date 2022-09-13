#!/bin/bash
set -e

technicalName="CoinGatePaymentShopware6"
version="$1"

if [ -z "$version" ]; then
    echo "Release version number is missing. Exiting..." 1>&2
    exit 1
fi

SCRIPT_DIR=$(cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd)
RELEASE_DIR="$SCRIPT_DIR/releases"

# Set the git working dir inside our project
export GIT_DIR=$SCRIPT_DIR/../.git

# Create a new tag if it does not exist yet
if [ ! $(git tag -l "$version") ]; then
  git tag "$version"
  git push origin "$version"
fi

# Store releases in release directory
mkdir -p "$RELEASE_DIR"

# Create a zip file out of the latest tag release
git archive "$version" --prefix="$technicalName/" --format=zip --output="$RELEASE_DIR/$technicalName-$version.zip"

echo "$technicalName-$version.zip was created"

#!/bin/sh

technicalName="CoinGatePaymentShopware6"

SCRIPT_DIR=$(cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd)
RELEASE_DIR="$SCRIPT_DIR/releases"

# Store releases in release directory
mkdir -p "$RELEASE_DIR"
# Set the git working dir inside our project
export GIT_DIR=$SCRIPT_DIR/../.git
# Get the latest tag
tag=$(git describe --tags --abbrev=0)
# Create a zip file out of the latest tag release
git archive "$tag" --prefix="$technicalName/" --format=zip --output="$RELEASE_DIR/$technicalName-$tag.zip"

echo "$technicalName-$tag.zip was created"

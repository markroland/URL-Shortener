#!/bin/sh

# Deploy

# Set repository branch to deploy
branch="master"

if [ $1 = "release" ]
then
    branch="release"
fi

# Change to project directory
echo 'Deploy: Changing to application directory'
cd ~/application

# Checkout latest commit from Git
# NOTE: Git post-checkout script will run (.git/hooks/post-checkout)
echo 'Deploy: Checking out code from source repository'
git fetch origin $branch --tags
git checkout --force FETCH_HEAD

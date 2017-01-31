#!/usr/bin/env groovy

// Jenkins build pipeline
node {

    // Checkout code
    stage('Checkout') {

        // Checkout latest commit from the git repository for multibranch pipeline
        checkout scm
    }

    // Build with Phing
    stage('Build') {

        // Check for PHP syntax errors
        sh 'phing phplint'
    }
}
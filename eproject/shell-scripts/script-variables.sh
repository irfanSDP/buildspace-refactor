#!/bin/bash
# Variables for scripts are kept here.

# Directories.
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );
PROJECT_DIRECTORY="$(dirname "$DIR")";
PATH_TO_UNRUN_SEEDS_FILE=app/database/unrun_seeds.txt;
PATH_TO_BACKUP_DIRECTORY=app/storage/database-backups;
SVN_URL='svn://svn.buildspace.my/eproject/trunk';
DEVELOPMENT_ENV_FILE="$PROJECT_DIRECTORY/.env.local.php";
PRODUCTION_ENV_FILE="$PROJECT_DIRECTORY/.env.php";
SAMPLE_ENV_FILE="$PROJECT_DIRECTORY/.env.local.php.sample";

function getEnvVariable {
    php -r '
        $developmentEnvVars = array();
        $productionEnvVars = array();
        if(file_exists("'$DEVELOPMENT_ENV_FILE'")) $developmentEnvVars = require "'$DEVELOPMENT_ENV_FILE'";
        if(file_exists("'$PRODUCTION_ENV_FILE'")) $productionEnvVars = require "'$PRODUCTION_ENV_FILE'";
        print_r($productionEnvVars["'$1'"] ?? $developmentEnvVars["'$1'"] ?? "");
        ';
}

DATABASE_HOST=$(getEnvVariable 'DB_HOST');
DATABASE_NAME=$(getEnvVariable 'DB_DATABASE');
DATABASE_USER=$(getEnvVariable 'DB_USERNAME');

BUILDSPACE_DATABASE_HOST=$(getEnvVariable 'BUILDSPACE_DB_HOST');
BUILDSPACE_DATABASE_NAME=$(getEnvVariable 'BUILDSPACE_DB_DATABASE');
BUILDSPACE_DATABASE_USER=$(getEnvVariable 'BUILDSPACE_DB_USERNAME');
#!/bin/bash
# Alert for missing and obsolete environment variables.

# Directories.
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );
PROJECT_DIRECTORY="$(dirname "$DIR")";

source $DIR/script-variables.sh;
source $DIR/helperFunctions.sh;

function getMissingEnvVariables {

    if [ -f $1 ]; then
        php -r '

        $currentVars = require "'$1'";
        $sampleVars = require "'$SAMPLE_ENV_FILE'";
        $missing = [];
        foreach($sampleVars as $key => $variable)
        {
            if(!array_key_exists($key, $currentVars)) $missing[] = $key;
        }
        (count($missing) > 0) ? print_r($missing) : print_r("");
        ';
    fi

}

function getObsoleteEnvVariables {

    if [ -f $1 ]; then
        php -r '
        $excludedFromChecking = [
            "disable_licensing",
        ];
        $currentVars = require "'$1'";
        $sampleVars = require "'$SAMPLE_ENV_FILE'";
        $obsolete = [];
        foreach($currentVars as $key => $variable)
        {
            if(in_array($key, $excludedFromChecking)) continue;
            if(!array_key_exists($key, $sampleVars)) $obsolete[] = $key;
        }
        (count($obsolete) > 0) ? print_r($obsolete) : print_r("");
        ';
    fi

}

print lightBlue 'Checking environment variables.';

declare -a env_files=("$DEVELOPMENT_ENV_FILE" "$PRODUCTION_ENV_FILE")

UPDATE_REQUIRED=false;

for env_file in "${env_files[@]}"
do
    MISSING_ENV_VARS=$(getMissingEnvVariables $env_file);
    OBSOLETE_ENV_VARS=$(getObsoleteEnvVariables $env_file);

    if [ ! -z "$MISSING_ENV_VARS" ]; then
        print red "Missing environment variables (Please add them!) [file: $env_file]";
        print red "$MISSING_ENV_VARS";
        UPDATE_REQUIRED=true;
    fi;
    if [ ! -z "$OBSOLETE_ENV_VARS" ]; then
        print yellow "Obsolete environment variables [file: $env_file]";
        print yellow "$OBSOLETE_ENV_VARS";
        UPDATE_REQUIRED=true;
    fi;

done

if [ "$UPDATE_REQUIRED" = false ] ; then
    print green "Environment variables are up to date.";
fi
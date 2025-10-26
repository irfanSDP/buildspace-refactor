#!/bin/bash
# Runs unrun seeds.

# Directories.
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );
source $DIR/script-variables.sh;
PARENT_DIRECTORY="$(dirname "$DIR")";
UNRUN_SEEDS_FILE=$PARENT_DIRECTORY/$PATH_TO_UNRUN_SEEDS_FILE;

function runSeeds {

    # Todo: add a seed log file that keeps track of run seeds.
    # Run the seeds.
    cat $UNRUN_SEEDS_FILE | awk '{printf "php artisan db:seed --class="$0" --force;"}' | bash;

    # Remove the seed file.
    rm $UNRUN_SEEDS_FILE;

    exit;

}
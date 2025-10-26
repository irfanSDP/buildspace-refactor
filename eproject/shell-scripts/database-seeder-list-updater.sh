#!/bin/bash
# Updates the list of unrun seeds.

# Directories.
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );
source $DIR/script-variables.sh;
PARENT_DIRECTORY="$(dirname "$DIR")";
UNRUN_SEEDS_FILE=$PARENT_DIRECTORY/$PATH_TO_UNRUN_SEEDS_FILE;

source $DIR/svn-operations.sh;

function updateSeedList {

    currentRevision=$(getRevisionVersion);
    latestRevision=$(getLatestRevisionVersion);

    touch $UNRUN_SEEDS_FILE;
    chmod 777 $UNRUN_SEEDS_FILE;
    
    # Order of seeder classes has been modified to remove the duplicate ones.
    # Lines containing older existing classes may have been modified,
    # so this ignores classes that have been both added and removed (the seeder classes on lines with both '+' and '-').
    svn -r $currentRevision:$latestRevision diff $PARENT_DIRECTORY/app/database/seeds/DatabaseSeeder.php | grep '^+.*\|^-.*' | grep '$this->call' | awk -F"\'" '{printf $2"\n"}' | sort | uniq -u > $UNRUN_SEEDS_FILE;

    # Rewrite the list based on the order of seeder classes in the latest revision.
    SEEDS=`cat $UNRUN_SEEDS_FILE | awk '{ printf t $0} { t="\\\|"}'`;
    svn -r $currentRevision:$latestRevision diff $PARENT_DIRECTORY/app/database/seeds/DatabaseSeeder.php | grep '^+.*' | grep '$this->call' | grep "$SEEDS" | awk -F"\'" '{printf $2"\n"}' > $UNRUN_SEEDS_FILE;

}

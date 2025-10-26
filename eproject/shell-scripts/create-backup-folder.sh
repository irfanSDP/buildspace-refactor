#!/bin/bash
# Creates a backup folder.

# Directories.
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );

source $DIR/helperFunctions.sh;
source $DIR/file-operations.sh;

function readyBackupFolder {

    BACKUP_DIRECTORY=$1;

    print lightBlue 'Readying backup folder.'

    if [[ $(folderExists $BACKUP_DIRECTORY) != 1 ]]; then
        print yellow 'Backup folder does not exist.';

        print lightBlue "Creating backup folder ($BACKUP_DIRECTORY).";
        mkdir "$BACKUP_DIRECTORY";

        # Modify user permissions
        #sudo chmod -R 777 $BACKUP_DIRECTORY;

        print green "Backup folder successfully created.";
    fi

    print green 'Backup folder ready.'
}
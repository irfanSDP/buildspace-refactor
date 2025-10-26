#!/bin/bash
# File and folder operations.

function fileExists {

    FILE_PATH=$1;

    if [ -f "$FILE_PATH" ]; then
        echo 1;
        return;
    fi

    echo 0;
    return;
}

function folderExists {

    FOLDER_DIRECTORY=$1;

    if [ -d "$FOLDER_DIRECTORY" ]; then
        echo 1;
        return;
    fi

    echo 0;
    return;
}
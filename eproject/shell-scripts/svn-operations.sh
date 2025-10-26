#!/bin/bash
# Svn operations.

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );
source $DIR/script-variables.sh;

function getRevisionVersion {

    svn_revision=`svn info | grep "Revision" | awk '{print $2}'`;

    echo $svn_revision;

}

function getLatestRevisionVersion {

    svn_revision=`svn info $SVN_URL | grep "Revision" | awk '{print $2}'`;

    echo $svn_revision;

}
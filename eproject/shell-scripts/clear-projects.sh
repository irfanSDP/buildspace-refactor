#!/bin/bash
# Truncates the project table, all project-related tables and all log tables

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );
source $DIR/helperFunctions.sh;

echo -n "Database > ";
read db_name;

echo -n "User > ";
read db_username;

# Abort if variables are empty
if [ -z "$db_name" ] || [ -z "$db_username" ]; then

    print yellow 'Variables not set';
    exit 1;

fi

$DIR/backup-database.sh $db_name $db_username;

if [[ $? = 0 ]]; then
    print green 'Successfully backed up the database.';
else
    print red 'Failed to back up database.';
    print lightBlue 'Aborting all other processes.';
    exit 1;
fi

psql $db_name -U$db_username -f $DIR/sql/clear-projects.sql;

exit $?;

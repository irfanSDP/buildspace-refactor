#!/bin/bash
# Creates a database backup.
# Exits with status 0 if database is backed up successfully.

# Directories.
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );
source $DIR/script-variables.sh;
PARENT_DIRECTORY="$(dirname "$DIR")";
BACKUP_DIRECTORY=$PARENT_DIRECTORY/$PATH_TO_BACKUP_DIRECTORY;

source $DIR/helperFunctions.sh;
source $DIR/create-backup-folder.sh;
source $DIR/svn-operations.sh;

function getFormattedDate {

    formatted_date=`date +"%Y-%m-%d"`;

    echo $formatted_date;

}

function getFormattedTime {

    formatted_date=`date +"%H%M%S"`;

    echo $formatted_date;

}

function generateDumpFileName {

    backup_file_name="$db_name.v$(getRevisionVersion).$(getFormattedDate).$(getFormattedTime).sql";

    echo $backup_file_name;

}

# Checks if database with the given database name exists
# by parsing the list of databases in postgres and returning the number of databases has a matching name.
function databaseExists {

    db_exists=`psql -U $db_username -h $db_host -lqt | cut -d \| -f 1 | grep -w $db_name | wc -l`;

    if [ $db_exists -ge 1 ]; then

        echo 1;
        return;

    fi

    echo 0;
    return;

}

function dumpData {

    print lightBlue "Dumping data from database \"$db_name\"";

    pg_dump $db_name > $BACKUP_DIRECTORY/$(generateDumpFileName) -U$db_username -h $db_host;

}

# Default host.
db_host=127.0.0.1;

while [ "$1" != "" ]; do
    case $1 in
        -n | --name )   db_name=$2
                        shift
                        ;;
        -u | --user )   db_username=$2
                        shift
                        ;;
        -h | --host )   db_host=$2
                        shift
                        ;;
        * )             usage
                        exit 1
    esac
    shift
done

if [ -z "$db_name" ] ; then

    echo -n "Database > ";
    read db_name;

fi

if [ -z "$db_username" ] ; then

    echo -n "Database User > ";
    read db_username;

fi

if [[ $(databaseExists) = 1 ]]; then

    readyBackupFolder $BACKUP_DIRECTORY;

    dumpData;

    exit $?;

else

    print yellow "Database \"$db_name\" (host:\"$db_host\") does not exist.";

    exit 1;

fi

exit 1;
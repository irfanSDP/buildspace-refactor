#!/bin/bash
# Updates E-project and clears the storage files.

# Todo:Update the update script before running anything else.

# Directories.
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );
PARENT_DIRECTORY="$(dirname "$DIR")";

source $DIR/helperFunctions.sh;
source $DIR/database-seeder-list-updater.sh;
source $DIR/database-seeder-runner.sh;
source $DIR/script-variables.sh;

backup=0;

while [ "$1" != "" ]; do
    case $1 in
        -nb | --no-backup )      backup=1
                                ;;
        * )                     usage
                                exit 1
    esac
    shift
done

if [ $backup -eq 0 ];
then
    print purple "Backing up database '$DATABASE_NAME' as user '$DATABASE_USER'";
    $DIR/backup-database.sh -n $DATABASE_NAME -u $DATABASE_USER -h $DATABASE_HOST;
    if [[ $? = 0 ]]; then
        print green "Successfully backed up the database \"$DATABASE_NAME\".";
    else
        print red "Failed to back up database \"$DATABASE_NAME\".";
        print lightBlue 'Aborting all other processes.';
        exit 1;
    fi

    print purple "Backing up database '$BUILDSPACE_DATABASE_NAME' as user '$BUILDSPACE_DATABASE_USER'";
    $DIR/backup-database.sh -n $BUILDSPACE_DATABASE_NAME -u $BUILDSPACE_DATABASE_USER -h $BUILDSPACE_DATABASE_HOST;
    if [[ $? = 0 ]]; then
        print green "Successfully backed up the database \"$BUILDSPACE_DATABASE_NAME\".";
    else
        print red "Failed to back up database \"$BUILDSPACE_DATABASE_NAME\".";
        print lightBlue 'Aborting all other processes.';
        exit 1;
    fi
fi

# Usual update commands.
php artisan down;

# make sure the shell scripts are up to date.
print lightBlue 'Updating the scripts.'
svn up shell-scripts;

print lightBlue 'Updating Seed list.';
$(updateSeedList);

print lightBlue 'Updating Codebase.';
svn up;

print lightBlue 'Updating Composer.';
sudo composer self-update;

print lightBlue 'Updating Dependencies based on composer.lock.';
composer install;

print lightBlue 'Migrating Database.';
php artisan migrate;

print lightBlue 'Clearing caches.';
php artisan cache:clear;
php artisan clear-compiled;
composer dump-autoload -o;

print lightBlue 'Running Seeds.';
$(runSeeds);

print lightBlue 'Restarting services.';
sudo /etc/init.d/php`php -r 'echo (PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION);'`-fpm restart;
sudo /etc/init.d/nginx restart

# Manually remove all storage files.
print lightBlue 'Removing storage files.';
sudo rm $PARENT_DIRECTORY/app/storage/views/*;
sudo rm $PARENT_DIRECTORY/app/storage/sessions/*;

# Finished.
print green 'Update complete.';

php artisan up;

source $DIR/env-var-check.sh;

print lightBlue 'You can check environment variables with shell-scripts/env-var-check.sh';

#!/bin/sh

APP_ENV="$1"

function runMigration()
{
    local CONFIG="$1"
    local MANAGER="$2"

    local STATUS="$(bin/console doctrine:migrations:status --env=${APP_ENV} --configuration=${CONFIG} --em=${MANAGER})"

    case "${STATUS}" in
        *"Already at latest version"*)
            # Do nothing
            ;;
        *)
            echo "RUNNING: doctrine:migrations:migrate next --env=${APP_ENV} --configuration=${CONFIG} --em=${MANAGER} --no-interaction --allow-no-migration --all-or-nothing"
            bin/console doctrine:migrations:migrate next --env=${APP_ENV} --configuration=${CONFIG} --em=${MANAGER} --no-interaction --allow-no-migration --all-or-nothing
            ;;
    esac
}

cd /srv/app

runMigration "vendor/acme/checkout/src/Infrastructure/Persistence/Migrations/config/migrations.yaml" "checkout"

# durham-civil-rights-map

Durham Civil Rights Map, a project of the Pauli Murray Project.

## Remote hosting

Dev, Test and Live are on Pantheon. Use `terminus` for running `drush config-import` after you push to dev/test/live.

## Local install

1. Clone the repo locally and set up your vhosts file to point to path/to/repo with whatever URL.
2. Create a blank PHP file in `docroot/sites/default/settings.local.php` and add the proper code to specify your local DB config
3. Making sure you're using drush 8.x (http://docs.drush.org/en/master/install/), run `drush sql-create`.
4. Download the DB from the dev site (ssh in, `drush sql-dump --gzip --result-file`), and install it locally.
5. Run `drush config-import` to make sure that config is synced

## Sass

From the theme directory (docroot/themes/mappy), run `bundle exec compass compile` to compile the Sass files into styles.css

## Workflow

Locally, any time you update any configuration related change in the database, you will want to run `drush config-export` to export the configuration changes to code.

Immediately after importing a DB locally to do some development, it's always a good idea to run `drush config-export` to see if the production DB has any changes that were done via the UI.

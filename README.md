# durham-civil-rights-map
Durham Civil Rights Map, a project of the Pauli Murray Project

## Local install

1. Clone the repo locally and set up your vhosts file to point to path/to/repo/docroot with whatever URL.
2. Create a blank PHP file in `docroot/sites/default/settings.local.php` and add the proper code to specify your local DB config
3. Making sure you're using drush 8.x (http://docs.drush.org/en/master/install/), run `drush sql-create`.
4. Download the DB from the dev site (ssh in, `drush sql-dump --gzip --result-file`), and install it locally.
5. Run `drush config-import staging` to make sure that config is synced

## Working with config changes

Always after pulling new changes from the repo, run `drush config-import staging` to sync your config with code (this is like reverting features for D7).

After you make changes to your local site, `drush config-export staging` will export the current local config into code. It seems that git does not always play well with yaml files, so sometimes merge conflicts
may arise which make the yaml invalid. Because of that it is recommended to test run `drush config-import staging` after each merge operation.

See more about how to manage configuration with Drupal 8 and a git workflow here: http://nuvole.org/blog/2014/aug/20/git-workflow-managing-drupal-8-configuration


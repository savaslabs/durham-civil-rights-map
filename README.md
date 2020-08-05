# durham-civil-rights-map

Durham Civil Rights Map, a project of the Pauli Murray Project.

## Local install (Docksal)

1. Install Docksal (https://docksal.io/)
1. Verify you have access to the Pantheon project.
1. Clone this repo to your workspace.
1. Create a `.docksal/docksal-local.env` file with the following line:
    ``````
    SECRET_TERMINUS_TOKEN="my-super-secret-token"
1. Run `fin init`
1. Currently, the files aren't pulling correctly from Pantheon, and I haven't diagnosed it yet, so you will need to download
a backed up copyof the site files directory and copy it into `sites/default/files`.

## Local install

1. Clone the repo locally and set up your vhosts file to point to path/to/repo with whatever URL.
2. Create a blank PHP file in `docroot/sites/default/settings.local.php` and add the proper code to specify your local DB config
3. Making sure you're using drush 8.x (http://docs.drush.org/en/master/install/), run `drush sql-create`.
4. Download the DB from the dev site (ssh in, `drush sql-dump --gzip --result-file`), and install it locally.
5. Run `drush config-import` to make sure that config is synced

## Sass

From the theme directory (`themes/mappy`), run `bundle exec compass compile` to compile the Sass files into `styles.css`.

You'll need to commit the `styles.css` file since Pantheon doesn't support running `compass compile`.

## Dev Workflow

### Local development

1. Use the Pantheon UI or `terminus` (the Pantheon CLI) to obtain the latest DB from `live`.
2. After importing the DB, run `drush config-export`. You should not see any changes. If you do, you should commit them and push them back to `master`.
3. {local development on whatever feature you're working on}
4. Run `drush config-export`. If you've made any changes to any configuration, you should see some YAML files. Make sure you commit those as part of your pull request.

### Pushing to Pantheon

1. Push your branch to `dev` on Pantheon, then to `test` and finally to `live`.
2. Once your code is in `live`, from your local machine, run `terminus drush config-import --site=durham-civil-rights-map --env=dev` (or `env=test` or `env=live`) — this imports your file-based configuration to the database. Don't skip this step!

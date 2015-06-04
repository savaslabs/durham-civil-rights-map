from __future__ import with_statement
from fabric.api import abort, run, cd, env, local, settings
from fabric.contrib.console import confirm

env.code_type = ''
env.forward_agent = True


def dev():
    env.user = 'www'
    env.code_dir = '/home/www/pmp.savasdev.com/docroot'
    env.hosts = 'savasdev.com'


def deploy_code():

    def check_if_code_exists():
        """ Check if a branch or tag exists in the user's local repo. """
        # Check if the branch exists
        with settings(warn_only=True):
            if local('git show-ref --heads --tags %s' % env.code).failed:
                return False
            else:
                return True

    def get_checked_out_branch_or_tag():
        return run('git name-rev --name-only HEAD')

    # Deploy the code
    with cd(env.code_dir):
        if not check_if_code_exists():
            abort('The "%s" %s does not exist in your repo!'
                  % (env.code, env.code_type))
        if not confirm('Current checked out %s is "%s", proceed with \
deploying "%s"?' % (env.code_type, get_checked_out_branch_or_tag(), env.code)):
            abort('Canceled deployment')
        run('git fetch')
        run('git fetch --tags')
        run('git checkout %s' % env.code)
        if env.code_type == 'branch':
            run('git pull origin %s' % env.code)


def pre_deploy(branch, tag):
    if (not branch and not tag) or (branch and tag):
        abort('You must specify a branch *or* tag to deploy, e.g. `fab stage \
deploy:branch=develop` or `fab prod deploy:tag=1.1.0`.')
    if branch:
        env.code_type = 'branch'
        env.code = branch
    if tag:
        env.code_type = 'tag'
        env.code = tag
    if (confirm('Backup database?')):
        with cd(env.code_dir):
            run('../vendor/bin/drush -r %s sql-dump --result-file --gzip' % env.code_dir)


def post_deploy():
    with cd(env.code_dir):
        # Prompt to import config
        if confirm('Import configuration from code?'):
            run('../vendor/bin/drush -r %s config-import staging' % env.code_dir)

        # Run update hooks. `updb` clears caches, hence the `else` below.
        if confirm('Run `../vendor/bin/drush updb`?'):
            run('../vendor/bin/drush -r %s updb' % env.code_dir)
        else:
            if confirm('Clear all caches?'):
                run('../vendor/bin/drush -r %s cr' % env.code_dir)


def deploy(branch='', tag=''):
    pre_deploy(branch, tag)
    deploy_code()
    post_deploy()


def status():
    with cd(env.code_dir):
        checked_out_code = run('git name-rev --name-only HEAD')
        local('echo "Checked out code on %s: %s"' % (
            env.hosts, checked_out_code))
        run('git status')
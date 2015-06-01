
HEAD to HEAD
------------

This module provides upgrade paths for schema changes in Drupal 8 until the
HEAD-HEAD upgrade path is officially supported.

In order to use this module, you will need to create a custom module that calls
the appropriate head2head_[issue number]() functions needed for your specific
installation.


Beta to Beta
--------------

Beta2Beta is a helper module that depends on head2head. It can only be used if
your website only uses official beta releases. It cannot be used if your website
is using Drupal HEAD from git.

The beta2beta module uses the head2head upgrade functions in its .install file
to provide upgrades through Drupal's normal update.php page.

If you install and use beta2beta, you should not uninstall it or beta2beta
will forget which updates have been completed the next time you install it.

However, just because this module is easier to use than head2head, in no way
should it be seen as an endorsement of running a real website on Drupal beta.
There are no guarantees that you won't lose all your data.

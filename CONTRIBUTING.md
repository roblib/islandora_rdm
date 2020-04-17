## Drupal Configuration Files

Features was used to create some of the exported configurations, but
can be over-zealous when you let it choose the files to include when
exporting using the Features UI.

Instead, for most changes which will be confined to a limited set of configuration
objects, those items should be exported manually using Drupal's
Admin -> Development -> Configuration Management -> Export -> Single Item
tool.

Features is still perfectly happy to re-import such changes via <code>drush fim</code>.


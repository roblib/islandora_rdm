## Drupal Configuration Files

Features was used to create some of the exported configurations, but
can be over-zealous when you let it choose teh files to include when
exporting using the Features UI.

Instead, for most chagnes which will be confinec to a limited set of configuration
objects, those items should be exported manually using Drupal's
Admin -> Development -> Configuration Management -> Export -> Single Item
tool.

Features is still perfectly happy to re-import such chagnes via <code>drush fim</code>.

## Module Structure

When deciding where to put a newly-exported config item, use the following guide:

1. islandora_rdm - Core utilities and classes taht any module should expect to be tehre.
1. islandora_rdm_types - Fields and entity types to express Datasets and Funding Objects.
This may not include Views and BLocks displaying such content, since those may rely on
other modules.
1. islandora_rdm_media_types - Media types, which are clones of the core Islandora 8
media types but which make use of the islandora_multifile_media ability to
include multiple file fields in one media object.
1. islandora_rdm_data_manaagement_plan - All functionality unique to Data Management Plan
and its fields and content types. This depends on islandora_rdm_types as it uses
some of the shared field types between the two, but the core types are not aware of
DMP-specific types or fields.
1. islandora_rdm_site - The out-ermost layer of functionality such as Blocks, layouts
and Views that may assume all other modules are enabled. Views taht include differnet
media types, or DMPs as well as core types, can go here.

This organization has come aobut as an attempt to avoid circular dependencies among
the above modules.

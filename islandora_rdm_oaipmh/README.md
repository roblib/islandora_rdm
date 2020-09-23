# Islandora OAI-PMH Endpoint (RDM Fork)

This submodule, edited for RDM, was originally packaged as part of Islandora Defaults. Changes have been documented in **bold** below. 

This submodule leverages the [rest\_oai\_pmh module](https://www.drupal.org/project/rest_oai_pmh)
to provide an OAI-PMH endpoint to allow harvesting repository content.

By default the OAI-PMH endpoint is located at 'yoursite.example/oai/request'
and includes a set identified as 'oai\_pmh:all\_repository\_items' which can be
further configured by editing the provided OAI-PMH view. The provided set 
includes all items using the **RDM-specific 'RDM Dataset'** content type that _don't_ use 
the 'Collection' model. Additional sets can be created by making views with the
Entity Reference view display mode and enabling them on the rest\_oai\_pmh
configuration page: `/admin/config/services/rest/oai-pmh`. The module can use
any view using an Entity Reference view display mode, they do not need to be
part of the provided OAI-PMH view, it is simply available as a convenience.

The rest\_oai\_pmh module indexes set membership, so repository items may not appear
immediately in their respective sets. Indexing will happen automatically during
cron runs but can be triggered manually at `/admin/config/services/rest/oai-pmh/queue`.

This module uses the Dublin Core metadata defined by
the **RDM Dataset content model's RDF mapping. See Islandora documentation for the [repository item content model's RDF mapping](http://islandora-claw.github.io/CLAW/user-documentation/content_types/#update-create-an-rdf-mapping).**
However, the RDF mapping does not include support for islandora\_default's use
of the linked agent field. Including agent links in the OAI-PMH metadata
currently requires updating the RDF mapping to include a Dublin Core predicate
for that field or any other additional fields. Alternatively, the rest\_oai\_pmh module 
also supports defining mappings with the
[metatag module](https://www.drupal.org/project/metatag) or creating a custom
metadata profile using the Twig templating system.

## Configuration

** NOTE THIS DOES NOT WORK AS IS** 
1. Install rest\_oai\_pmh (e.g. `composer require drupal/rest\_oai\_pmh`).
1. Enable this module (e.g. `drush en -y islandora\_oaipmh`).
1. Trigger the OAI-PMH indexer: Click the button found on the page at `http://localhost:8000/admin/config/services/rest/oai-pmh/queue` (or wait for cron)
1. Give anonymous users the "Access GET on OAI-PMH resource" permission.

Your OAI-PMH Endpoint should now be ready. e.g. `http://localhost:8000/oai/request?verb=ListRecords&metadataPrefix=oai\_dc`

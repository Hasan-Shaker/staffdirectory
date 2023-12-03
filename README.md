# Staffdirectory

## Suggested configuration

- Create a storage folder for your groups and persons
  - Edit its Page TSconfig to have:

     ```
     mod.web_list.allowedNewTables (
         fe_users,
         tx_staffdirectory_domain_model_organization
     )

     mod.web_list.hideTables := addToList(tx_staffdirectory_domain_model_member)

     # This makes adding new fe_users much easier, adapt to your needs!
     TCAdefaults.fe_users {
         usergroup = 1
         username = nologin-
         password = __invalid__
         country = CH
         tx_extbase_type = tx_staffdirectory
     }
     ```

## Routing configuration

Nice URL can be configured by editing your site configuration (stored in
file `config/sites/<site>/config.yaml`):

```
routeEnhancers:
  Staffdirectory:
    type: Extbase
    limitToPages:
      - <detail-page-of-a-person>
      - <detail-page-of-an-organization>
    extension: Staffdirectory
    plugin: Plugin
    routes:
      -
        routePath: '/p/{person-name}'
        _controller: 'Plugin::person'
        _arguments:
          person-name: person
      -
        routePath: '/o/{organization-name}'
        _controller: 'Plugin::organization'
        _arguments:
          organization-name: organization
    aspects:
      person-name:
        type: PersistedAliasMapper
        tableName: fe_users
        routeFieldName: path_segment
      organization-name:
        type: PersistedAliasMapper
        tableName: tx_staffdirectory_domain_model_organization
        routeFieldName: path_segment
```

Note: you may omit the `limitToPages` configuration but are advised to keep it.

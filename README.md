# con4gis_api_bundle
With this bundle you can use all con4gis 3.5.x extensions with Contao >= 4.2.x

## Add route to symfony
Before you can start using the the con4gis extensions, please be sure you've added the new route to app/config/routing.yml
You have to add the new route before the contao routes.

```
Con4gisApiBundle:
    resource: "@Con4gisApiBundle/Resources/config/routing.yml"

ContaoInstallationBundle:
    resource: "@ContaoInstallationBundle/Resources/config/routing.yml"

ContaoCoreBundle:
    resource: "@ContaoCoreBundle/Resources/config/routing.yml"
```


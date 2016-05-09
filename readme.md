# Mediasite API

REST-API for UNINETT Mediasite som muliggjør uthenting av informasjon knyttet til tjenesten og dens abonnenter.

Eneste kildepunkt akkurat nå er lagringsstatistikk fra en UNINETT DB som er populert døgnlig via mediasite-disk-stats (se "Avhengigheter").

Kan vurdere å plugge inn eCampus Kind API (https://github.com/skrodal/ecampus-kind-api), men gitt at Kind 2.0 er i farta er det lurt å avvente.

## Dataporten Scopes

** Public Scope (Dataporten `basic`) **
** Org Scope (Dataporten `org`) **
** Superadmin Scope (Dataporten `admin`) **

Sjekk implementerte ruter i index.php.

## Avhengigheter

- Dataporten
- Alto Router
- Mediasite DiskStats (https://github.com/skrodal/mediasite-disk-stats)
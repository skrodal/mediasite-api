# Mediasite API #

_This API, which is tailor-made for UNINETT AS, uses in-house developed APIs/clients pertaining to specific use-cases. As it does **not** access Sonic Foundry's official Mediasite API, its re-usability is limited._

REST-API for UNINETT Mediasite som muliggjør uthenting av informasjon knyttet til tjenesten og dens abonnenter.

Eneste kildepunkt akkurat nå er lagringsstatistikk fra en UNINETT DB som er populert døgnlig via mediasite-disk-stats (se "Avhengigheter").

(Altså gått BORT fra å bruke eCampus Kind API (https://github.com/skrodal/ecampus-kind-api), gitt at Kind 2.0 er i farta).

## Scopes ##
 
* Public Scope (Dataporten `basic`)
* Org Scope (Dataporten `org`)
* Superadmin Scope (Dataporten `admin`)

APIet må registreres i Dataporten. En klient's tilgang til ruter styres av tillatte scopes (satt i Dataporten og definert i HTTP_X_DATAPORTEN_SCOPES).

Sjekk implementerte ruter i index.php.

## Klient ##

APIet er primært utviklet for bruk i MediasiteAdmin: https://github.com/skrodal/mediasite-admin

## Avhengigheter ##

- Dataporten
- Alto Router
- Mediasite DiskStats (https://github.com/skrodal/mediasite-disk-stats)

### Annet ###

- Caching er lagt til (APC) - kan slåes av i config

Utviklet av Simon Skrødal
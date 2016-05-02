# Mediasite API

REST-API for UNINETT Mediasite som muliggjør uthenting av informasjon knyttet til tjenesten og dens abonnenter/brukere.

Henter bl.a. ut lagringsstatistikk fra en UNINETT DB som er populert døgnlig via mediasite-disk-stats (se service). 

## Status

02.05.2016: 

`INCOMPLETE — WORK IN PROGRESS!` 

Har satt sammen det som kreves av klasser, men ikke satt opp funksjonalitet for uthenting fra DB. API er registrert i Dataporten. 

TODO: 

1. Hente ut lagringsinfo fra DB
2. Kutt ut bruk av ecampus-kind-api (da KIND fases ut ila. året). Bruk heller Dataporten sine ad-hoc grupper for tilgangsstyring. 
3. Implementer ruter og plugg disse inn i MediasiteAdmin (client - også registrert i Dataporten).

## Scopes

** Public Scope (Dataporten `basic`) **

- Service info (version, workers, queue)

** Org Scope (Dataporten `org`) **

- /org/ (presentations, users, user, employees, students)

** Superadmin Scope (Dataporten `admin`) **

- Alt over samt /global/, /dev/

## Avhengigheter

- Dataporten
- Alto Router
# Dutch Laravel Foundation: GraphQL voorbeeld project
Dit project heeft als doel de basis te bieden die nodig is voor het bouwen van een GraphQL API in Laravel.

Het project maakt gebruikt van de volgende pakketjes om de GraphQL functionaliteiten te introduceren:
- [nuwave/lighthouse](https://github.com/nuwave/lighthouse)
- [mll-lab/laravel-graphiql](https://github.com/mll-lab/laravel-graphiql)

## Setup
Om snel aan de slag te gaan kan het volgende commando gedraaid worden in de terminal:

```shell
make setup
```

Dit commando start twee docker containers op:
- **app**: Een combinatie van PHP, PHP-FPM en nginx
- **postgres**: Een PostgreSQL database

## Playground
De interactieve GraphQL playground kan benaderd worden via http://localhost:8000/graphiql. 

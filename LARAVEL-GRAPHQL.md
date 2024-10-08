# GraphQL met Laravel en Lighthouse

In de wereld van moderne webontwikkeling zijn efficiënte en flexibele API's van cruciaal belang.
Bij [Endeavour](https://endeavour.nl) maken we daarom gebruik van GraphQL, een krachtige query-taal ontwikkeld door Facebook.
[GraphQL](https://graphql.org/) biedt een scala aan voordelen voor zowel onze ontwikkelaars als onze klanten en is daardoor
niet meer weg te denken uit onze gereedschapskist.

Met GraphQL hebben we een belangrijke stap gezet op het gebied van **standaardisatie**, waardoor onze codebases
en de communicatie tussen frontend en backend voorspelbaar is geworden. Keer op keer het wiel opnieuw uitvinden is
niet meer nodig, waardoor we onze tooling geoptimaliseerd hebben en we kunnen focussen op het ontwikkelen van de 
wensen van de klant.

De query-taal maakt het mogelijk voor applicaties om te dicteren welke data het exact van de API wil ontvangen. Dat zorgt 
ervoor dat er geen onnodige data over de lijn gestuurd wordt en biedt onze frontend ontwikkelaars de **flexibiliteit** die 
nodig is om **efficient** nieuwe componenten te ontwikkelen, zonder aanpassingen aan de API.

Met [Lighthouse](https://lighthouse-php.com/) is het opzetten van een GraphQL API in Laravel kinderlijk eenvoudig!
Het open-source pakket geeft ons het raamwerk waarmee we gemakkelijk onze GraphQL API kunnen opzetten. 
Het is de missende schakel die verzoeken naar de API interpreteert en navigeert naar de juiste stukken code binnen onze 
Laravel applicatie.

Ik neem je mee in de installatie en configuratie en laat je zien hoe je snel en gemakkelijk een API opzet! 

## Installeren en configureren

Mocht je deze stap over willen slaan en direct aan de slag willen met het maken van queries en mutations, clone 
dan [deze repository](https://github.com/dennis-koster/dlf-graphql-example) en volg de instructies uit de readme.

We beginnen met het opzetten van een nieuw Laravel project en de installatie van Lighthouse.

```shell
composer create-project laravel/laravel dlf-graphql-example
cd dlf-graphql-example
composer require nuwave/lighthouse
```

Vervolgens publiceren we het `schema.graphql` bestand in het mapje `graphql`. In dit bestand definiëren we al onze 
queries en mutations, vergelijkbaar met een route bestand, zoals je die van Laravel kent.

```shell
php artisan vendor:publish --tag=lighthouse-schema
```

We helpen onze IDE een handje om de Lighthouse-specifieke syntax te begrijpen, door het genereren van een
`_lighthouse_ide_helper.php` bestand.

```shell
php artisan lighthouse:ide-helper
```

Met de bekende HTTP tools, zoals Postman, kun je communiceren met je GraphQL API, maar voor het gemak installeren we een 
pakketje dat een interactieve playground beschikbaar stelt binnen ons project. Deze is standaard te bereiken
op `http://<APP_URL>/graphiql`.

```shell
composer require mll-lab/laravel-graphiql --dev
```

## GraphQL concepten
Voordat we beginnen is het belangrijk om een aantal basisconcepten van GraphQL en Lighthouse uit te leggen. Een GraphQL 
API bestaat feitelijk maar uit één endpoint, standaard is dat `/graphql`. Elke request naar de API gebruikt de `POST`
methode, waarbij de request body de volgende JSON-structuur heeft:

```json
{
    "query": "...",
    "operationName": "...",
    "variables": { "myVariable": "someValue", ... }
}
```

Een GraphQL API geeft altijd een response met de volgende JSON-structuur, waarbij altijd een van de twee attributen
aanwezig moet zijn:
```json
{
  "data": { ... },
  "errors": [ ... ]
}
```

### Types
In GraphQL zijn types een fundamenteel concept dat bepaalt welke soorten gegevens beschikbaar zijn in de API en hoe deze 
gegevens zijn gestructureerd. Elk GraphQL-schema is opgebouwd uit een set van deze types, die aangeven welke velden 
beschikbaar zijn en welk type waarde elk veld teruggeeft.

Er zijn verschillende soorten types in GraphQL:

1. **Scalar types**: Dit zijn de basistypes, zoals `Int`, `Float`, `String`, `Boolean`, en `ID`.
2. **Object types**: Deze representeren complexe gegevens en bestaan uit velden die elk een specifiek type hebben. Bijvoorbeeld een `User` type met velden zoals `name` (van type `String`) en `age` (van type `Int`).
3. **Query en Mutation types**: Dit zijn de toegangspunten voor het ophalen en wijzigen van gegevens in een GraphQL API. Een `Query` type wordt gebruikt voor het opvragen van gegevens, terwijl een `Mutation` type bedoeld is voor het aanpassen van gegevens.
4. **Input types**: Deze worden gebruikt om gegevens in te voeren bij mutations. Ze lijken op object types, maar worden specifiek gebruikt om invoerparameters te definiëren.

Types in GraphQL zorgen ervoor dat de API voorspelbaar en goed gedocumenteerd is, omdat elke query exact moet voldoen aan het type-schema dat is gedefinieerd.

### Schema
Alle typedefinities bij elkaar noemen we het GraphQL Schema. Het is de blauwdruk van de API en beschrijft de structuur 
en functionaliteiten van de API. In Lighthouse bouwen we het schema op in het `graphql/schema.grapqhl` bestand dat we
bij de installatie gegenereerd hebben. Neem gerust een kijkje voordat we verder gaan!

### Directives
[Directives](https://lighthouse-php.com/6/the-basics/directives.html#directives) zijn binnen Lighthouse de primaire 
manier om functionaliteiten aan onze GraphQL API toe te voegen. Ze zijn gemakkelijk te herkennen, omdat ze altijd 
beginnen met een `@`. Directives kunnen op verschillende plekken in het schema worden toegepast.

## Data ophalen uit de GraphQL API
Alright, het stukje theorie hebben we gehad, we zijn klaar om onze eerste API call maken! Een basisinstallatie Laravel 
biedt alvast een `User` model en Lighthouse komt, out of the box, met twee queries om gebruikers op te halen.

* `users`: Om een gepagineerde lijst aan gebruikers op te halen
* `user`: Om een enkele gebruiker op te halen

```graphql
type Query {
    "Find a single user by an identifying attribute."
    user(
      "Search by primary key."
      id: ID @eq @rules(apply: ["prohibits:email", "required_without:email"])

      "Search by email address."
      email: String @eq @rules(apply: ["prohibits:id", "required_without:id", "email"])
    ): User @find

    "List multiple users."
    users(
      "Filters by name. Accepts SQL LIKE wildcards `%` and `_`."
      name: String @where(operator: "like")
    ): [User!]! @paginate(defaultCount: 10)
}
```

### Gepagineerde data
De [`@paginate`](https://lighthouse-php.com/6/api-reference/directives.html#paginate) directive zorgt ervoor dat 
resultaten gepagineerd worden teruggegeven. Op die manier blijft de response body klein en wordt de backend niet
onnodig belast.

Open de GraphQL Playground, standaard beschikbaar op `/graphiql`, en voer de volgende query uit.
```graphql
query {
    users(first: 5, page: 1) {
        data {
            id
            name
            email
        }
        paginatorInfo {
            total
            perPage
            lastPage
        }
    }
}
```

![Users query](/docs/users-query.gif)

Laten we dat ontleden! Allereerst geven we aan dat we een `query` willen uitvoeren. Met andere woorden; we willen data **ophalen**.
```graphql
query {
```

Vervolgens geven we aan dat we maximaal **5** resultaten van de **eerste pagina** uit de `users` query willen ontvangen.

```graphql
users(first: 5, page: 1) {
```

In het `data` veld geven we aan welke attributen van de user we terug willen krijgen.
```graphql
data {
    id
    name
    email
}
``` 

Tot slot vragen we paginatie informatie op, zodat we weten hoeveel resultaten er in totaal zijn en het aantal pagina's.
```graphql
paginatorInfo {
    total
    perPage
    lastPage
}
```
### Meerdere queries in één request

Een van de grootste voordelen van GraphQL is de mogelijkheid om resultaten [van meerdere queries](https://graphql.org/#single-request) in één HTTP request op te vragen. Een applicatie kan daardoor met één request naar de API alle data opvragen die het nodig heeft.

Met het onderstaande voorbeeld voeren we twee queries uit met 1 request:
* We vragen van de eerste 10 users alleen de `id` op en de `name`.
* Van de user met id `1` vragen we gedetailleerde informatie op.

```graphql
query {
    users(first: 10, page: 1) {
        data {
            id
            name
        }
    }

    user(id: "1") {
        id
        name
        email
        created_at
        updated_at
    }
}
```

![Users and user detail query](/docs/users-and-user-detail-query.gif)

## Data aanmaken via GraphQL API
We weten nu hoe we data ophalen, maar hoe creëren we data via de API? In GraphQL doen we dit middels een zogenaamde [mutation](https://graphql.org/learn/queries/#mutations). We gaan een mutation maken waarmee we een gebruiker kunnen aanmaken.

Open het `schema.graphql` bestand in de `graphql` map en plak daarin de volgende code:

```graphql
type Mutation {
    register(input: RegisterInput! @spread): User! @create
}

input RegisterInput {
    name: String!
    email: String! @rules(apply: ["email", "unique:users,email"])
    password: String! @rules(apply: ["min:8"])
}
```
Open nu weer de GraphQL playground en voer de volgende mutation uit:

```graphql
mutation {
    register(input: {
        name: "Dennis"
        email: "dennis@example.com"
        password: "secret123"
    }) {
        id
        name
        email
        created_at
        updated_at
    }
}
```

![Create user mutation](/docs/create-user-mutation.gif)

Met een paar regels code hebben we een mutation aangemaakt, waarmee we nieuwe gebruikers kunnen registreren. Laten we weer ontleden wat we precies gedaan hebben.

Allereerst geven we aan dat we een **mutation** willen definiëren.

```graphql
type Mutation {
```

Vervolgens hebben we de definitie van de mutation, waar we gebruik maken van een combinatie van directives van Lighthouse.

```graphql
register(input: RegisterInput! @spread): User! @create
```

Er gebeuren hier een aantal dingen:
1. We geven de mutation de naam `register`.
2. We geven aan dat de `input` van de mutation van het type `RegisterInput` moet zijn.
3. Met de [`@spread` directive](https://lighthouse-php.com/6/api-reference/directives.html#spread) slaan we de input plat, vergelijkbaar met de [Arr::flatten() helper functie](https://laravel.com/docs/11.x/helpers#method-array-flatten) van Laravel.
4. De mutation moet een `User` teruggeven.
5. De [`@create` directive](https://lighthouse-php.com/6/api-reference/directives.html#create) zorgt ervoor dat met de gegeven input een nieuw `User` model wordt aangemaakt in de database.

Als laatste definiëren we de `RegisterInput`.

```graphql
input RegisterInput {
    name: String!
    email: String! @rules(apply: ["email", "unique:users,email"])
    password: String! @rules(apply: ["min:8"])
}
```
Dit spreekt redelijk voor zich, maar het is goed om te benoemen dat [alle beschikbare validatieregels van Laravel](https://laravel.com/docs/11.x/validation#available-validation-rules) hier gebruikt kunnen worden om de input te valideren.

## Geavanceerde use cases
Met de standaard directives komen we zoals je ziet een heel eind, zonder ook maar een regel PHP-code te schrijven. 
Logischerwijs dekken deze echter lang niet alle denkbare scenarios en is het ook mogelijk om je eigen logica voor het
afhandelen van queries en mutations te schrijven.

### Maatwerk mutation
Stel je voor dat er een beheerpaneel is voor admins. Via dit paneel moet het mogelijk zijn om het wachtwoord van een 
gebruiker te resetten. We willen zelf het nieuwe wachtwoord kunnen specificeren, maar als deze niet wordt meegegeven 
willen we dat de API zelf een willekeurig wachtwoord genereert.

Zoals voorheen openen we het `schema.graphql` bestand en vervolgens voegen we de volgende code toe:

```graphql
extend type Mutation {
    resetUserPassword(input: ResetUserPasswordInput! @spread): String! @field(resolver: "App\\GraphQL\\Mutations\\ResetUserPassword")
}

input ResetUserPasswordInput {
    id: ID! @rules(apply: ["exists:users,id"])
    password: String @rules(apply: ["min:8"])
}
```

De meeste zaken van bovenstaande code hebben we bij de basisvoorbeelden behandeld, maar de [`@field` directive](https://lighthouse-php.com/6/api-reference/directives.html#field) is nieuw.

```graphql
@field(resolver: "App\\GraphQL\\Mutations\\ResetUserPassword")
```

Met de `@field` directive geven we aan welke PHP class verantwoordelijk is voor het afhandelen van de logica van deze 
mutation. We verwijzen naar een class die nog niet bestaat, dus laten we die creëren.

`app/GraphQL/Mutations/ResetUserPassword.php`:
```php
<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetUserPassword
{
    public function __invoke($_, array $args): string
    {
        $user     = User::findOrFail($args['id']);
        $password = $args['password'] ?? Str::random(8);

        $user->update([
            'password' => Hash::make($password),
        ]);

        // Logic for sending an email to the user here

        return "Wachtwoord is gereset naar {$password}.";
    }
}
```

Voer vervolgens de volgende API call uit in de GraphQL playground en kijk wat er gebeurt!

```graphql
mutation {
    resetUserPassword(input: {
        id: 1
        password: "testing123"
    })
}
```

![Reset user password](/docs/reset-password-mutation.gif)

> Een dergelijke API call wil je normaliter beveiligen met solide authenticate en autorisatie. 

### Maatwerk query
Een query hoeft niet altijd iets uit een database terug te geven. Het kan bijvoorbeeld nuttig zijn om een query te 
hebben die het versienummer van de API teruggeeft, die wordt uitgelezen uit het `composer.json` bestand.

We doen dat op een vergelijkbare manier als de maatwerk mutation, door gebruik te maken van de `@field` directive.

Voeg de volgende query definitie toe aan `schema.graphql`.

```graphql
extend type Query {
    apiVersion: String! @field(resolver: "App\\GraphQL\\Queries\\ApiVersion")
}
```
Maak vervolgens de class aan die verantwoordelijk is voor het afhandelen van de query logica.

`app/GraphQL/Queries/ApiVersion.php`:
```php
<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

class ApiVersion
{
    public function __invoke($_, array $args): string
    {
        $composerContents   = file_get_contents(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'composer.json');
        $composerAttributes = json_decode($composerContents, true);

        return $composerAttributes['version'] ?? 'onbekend';
    }
}
```
Zorg ervoor dat je `composer.json` bestand een `version` gedefinieerd heeft en probeer de query uit te voeren.

```graphql
query {
    apiVersion 
}
```
### Queries uitbreiden
We weten nu hoe we gebruik kunnen maken van de standaard directives van Lighthouse en hoe we volledig maatwerk queries 
en mutations kunnen schrijven. Soms doen de standaard directives echter *bijna* wat je wil, maar wil je de uitgevoerde 
query kunnen beïnvloeden. Ik laat je zien hoe!

Laten we de `users` query weer als voorbeeld pakken. We willen de pagination behouden, maar we willen alleen gebruikers 
terugkrijgen die na een opgegeven datum zijn aangemaakt.

Open het `schema.graphql` bestand en pas de `users` query aan als volgt:
```graphql
users(
  "Filters by name. Accepts SQL LIKE wildcards `%` and `_`."
  name: String @where(operator: "like")

  "Filters by created_at."
  createdAfter: DateTime
): [User!]! @paginate(defaultCount: 10, builder: "App\\GraphQL\\Builders\\UsersBuilder")
```

Maak daarna de [custom builder class](https://lighthouse-php.com/6/api-reference/directives.html#custom-builder) aan.

`app/GraphQL/Builders/UsersBuilder.php`:
```php
<?php

declare(strict_types=1);

namespace App\GraphQL\Builders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UsersBuilder
{
    public function __invoke($_, array $args): Builder
    {
        $builder = User::query();

        if (isset($args['createdAfter'])) {
            $builder->where('created_at', '>=', $args['createdAfter']);
        }

        return $builder;
    }
}
```
De query accepteert nu, naast de argumenten voor de paginering, ook het `createdAfter` argument. Het argument wordt 
uitgelezen in de builder class, die de database query uitbreidt en vervolgens de eloquent query instantie teruggeeft.

```graphql
query {
    users(first: 10, page: 1, createdAfter: "2024-09-18 11:10:00") {
        data {
            id
            name
            created_at
        }
    }
}
```

### Authenticatie en autorisatie
Bij het uitlezen en aanpassen van gebruikers, zoals in de voorbeelden hierboven, is een solide authenticatie en
autorisatie flow onmisbaar. Lighthouse biedt daarvoor een oplossing middels de [@guard](https://lighthouse-php.com/6/api-reference/directives.html#guard)
en de [@can](https://lighthouse-php.com/6/api-reference/directives.html#can) directives, die gebruik maken van Laravel's
guards en policies. Een uitstekende plugin is [joselfonseca/lighthouse-graphql-passport-auth](https://github.com/joselfonseca/lighthouse-graphql-passport-auth),
wanneer je gebruik wilt maken van [Laravel Passport](https://laravel.com/docs/11.x/passport) en ook met [Laravel Sanctum](https://laravel.com/docs/11.x/sanctum#main-content)
kun je snel aan de slag dankzij [daniel-de-wit/lighthouse-sanctum](https://github.com/daniel-de-wit/lighthouse-sanctum).

### De diepte in
Een GraphQL API opzetten is met Lighthouse een fluitje van een cent. De behandelde scenarios geven je hopelijk een goede 
basis om mee te starten, maar Lighthouse is nog vele malen uitgebreider dan dit. De [documentatie](https://lighthouse-php.com/6/getting-started/installation.html) 
is een goed startpunt wanneer je verder de diepte in wil. Er zijn tevens [tal van plugins](https://lighthouse-php.com/resources/#plugins) 
die de standaardfunctionaliteiten van Lighthouse uitbreiden.

Voor vragen die niet in de documentatie behandeld zijn kun je altijd een bericht plaatsen op de [`Discussions`](https://github.com/nuwave/lighthouse/discussions) sectie van Lighthouse's
github pagina. Voel je tevens vrij om mij een berichtje te sturen op [LinkedIn](https://www.linkedin.com/in/dennis-koster-688b7b48/) als je ergens niet uitkomt!

### Over de auteur
Dit artikel werd geschreven door [Dennis Koster](https://www.linkedin.com/in/dennis-koster-688b7b48/), Lead Developer bij [Endeavour](https://endeavour.nl) en bestuurslid bij de Dutch Laravel Foundation. Endeavour is een van onze founding partners en expert op het gebied van GraphQL.  

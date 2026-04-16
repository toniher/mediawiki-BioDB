# BioDB

Extension for mapping database tables or structures directly into a wiki.

Used in Biological Database projects. Based on the
[External Data extension](https://www.mediawiki.org/wiki/Extension:External_Data).

## Requirements

- MediaWiki >= 1.43
- PHP >= 7.4
- Semantic MediaWiki >= 3.0 (optional — only needed for `#BioDB_store_table` and `#BioDB_fstore_table`)

## Installation

1. Download or clone the repository into your MediaWiki `extensions/` directory:

   ```bash
   cd /path/to/mediawiki/extensions
   git clone https://github.com/your-org/mediawiki-BioDB BioDB
   ```

2. Add the following to the **bottom** of your `LocalSettings.php`, configuring at
   minimum `$wgBioDB` and `$wgBioDBExpose` **before** calling `wfLoadExtension`:

   ```php
   // Default database connection used by BioDB (can be any external MySQL/MariaDB DB)
   $wgBioDB = [
       'server'      => 'localhost',
       'type'        => 'mysql',
       'name'        => 'my_bio_database',
       'username'    => 'db_user',
       'password'    => 'db_password',
       'flags'       => 0,
       'tableprefix' => '',
   ];

   // Define named query sets to expose in the wiki
   $wgBioDBExpose = [
       'gene' => [
           // Optional: override the default connection for this query set
           'db' => [
               'server'      => 'localhost',
               'type'        => 'mysql',
               'name'        => 'gene_db',
               'username'    => 'gene_user',
               'password'    => 'gene_password',
               'flags'       => 0,
               'tableprefix' => '',
           ],
           // SQL query; use #P1, #P2, … as positional placeholders
           'query' => "SELECT gene_alias, gene_name, chromosome
                       FROM coordinates
                       WHERE gene_alias = '#P1'",
           // Map column names to Semantic MediaWiki property names (optional)
           'propmap' => [
               'gene_alias' => 'Has Alias',
               'gene_name'  => 'Has Name',
               'chromosome' => 'Is in Chromosome',
           ],
           // Restrict API access to these user groups (omit to allow all)
           'api' => ['sysop'],
       ],
   ];

   // Set false to disable the /api.php?action=BioDB endpoint entirely
   $wgBioDBApi = true;

   // Set true when $wgBioDB is keyed by database alias (multi-DB mode)
   $wgBioDBmultiple = false;

   wfLoadExtension( 'BioDB' );
   ```

3. Run MediaWiki's update script (only needed if you add database tables in future):

   ```bash
   php maintenance/update.php
   ```

### Multi-database mode

Set `$wgBioDBmultiple = true` and key `$wgBioDB` by an alias name. Query sets
then reference the alias with `alias:setname` syntax:

```php
$wgBioDBmultiple = true;
$wgBioDB = [
    'main' => [
        'server' => 'db1.example.org', 'type' => 'mysql',
        'name' => 'maindb', 'username' => 'u', 'password' => 'p',
        'flags' => 0, 'tableprefix' => '',
    ],
    'extra' => [
        'server' => 'db2.example.org', 'type' => 'mysql',
        'name' => 'extradb', 'username' => 'u2', 'password' => 'p2',
        'flags' => 0, 'tableprefix' => '',
    ],
];
```

## Parser functions

All parser functions are available in wiki pages after the extension is loaded.

### `#BioDB` — load data into memory

```
{{#BioDB: set_name | param1, param2, … }}
```

Runs the query defined under `set_name` in `$wgBioDBExpose`, substituting `#P1`,
`#P2`, … with the supplied comma-separated parameters. Results are stored in
memory for use by the retrieval functions below on the same page.

Call `{{#BioDB_clear:}}` at the top of each page (or between calls) to reset
stored results.

---

### `#BioDB_value` — retrieve a single field

```
{{#BioDB_value: field_name | default_value | formatted_output }}
```

Returns the value(s) of `field_name` from the loaded results, joined by `*`.
`default_value` is returned when no value is found. In `formatted_output`,
`#P1` is replaced with the retrieved value.

---

### `#BioDB_count` — count non-empty values

```
{{#BioDB_count: field_name }}
```

Returns the number of rows where `field_name` is non-empty.

---

### `#BioDB_exists` — conditional on presence

```
{{#BioDB_exists: field_name | value_if_found | value_if_not_found }}
```

---

### `#BioDB_table` — iterate over results

```
{{#BioDB_table: row template expression using {{{field_name}}} }}
```

Loops over all loaded rows and substitutes `{{{field_name}}}` placeholders.
Supports modifiers after `@` in the placeholder name:

| Modifier | Example | Effect |
|---|---|---|
| `prefix=NS` | `{{{field@prefix=Gene}}}` | Prepends `Gene:value` |
| `template=Tpl` | `{{{field@template=GeneLink}}}` | Wraps value in `{{Tpl|value}}` |
| `scientific=N` | `{{{field@scientific=3}}}` | Formats small floats in scientific notation with N-digit threshold |

---

### `#BioDB_table_template` — render rows via a wiki template

```
{{#BioDB_table_template: TemplateName | header=HeaderTemplate | footer=FooterTemplate | html }}
```

Fetches `TemplateName` from the wiki, fills its `{{{field|default}}}` parameters
from each loaded row, then concatenates the results. Optional `header=` and
`footer=` templates wrap the output. Pass `html` to emit raw HTML.

---

### `#BioDB_store_table` — store results as SMW subobjects

```
{{#BioDB_store_table: MainProperty | CustomProp={{{set.col}}} | … }}
```

Requires Semantic MediaWiki. For each loaded row, creates an SMW subobject
with properties derived from `$wgBioDBExpose[set][propmap]` or from explicit
`CustomProp={{{set.col}}}` mappings.

---

### `#BioDB_fstore_table` — flexible SMW storage

```
{{#BioDB_fstore_table: MainProperty | {{{set.col}}}=SMW Property | … }}
```

Like `#BioDB_store_table` but uses the left-hand side of each assignment as the
key expression (supports `{{{…}}}` placeholders within the key itself).

---

### `#BioDB_clear` — reset stored data

```
{{#BioDB_clear:}}
```

Clears all data loaded by `#BioDB` on the current page. Always call this before
loading new data to avoid stale results from a previous call.

---

## REST / Action API

When `$wgBioDBApi = true`, query sets are accessible via the MediaWiki Action API:

```
GET /api.php?action=BioDB&query=gene&param=BRCA1&format=json
```

| Parameter | Required | Description |
|---|---|---|
| `query` | yes | Name of the query set (key in `$wgBioDBExpose`) |
| `param` | no | Comma-separated positional parameters (`#P1`, `#P2`, …) |
| `table` | no | Return results in row/column table format |
| `fileformat` | no | Set to `csv` (with `table=1`) to download as a file |
| `sep` | no | Column separator for CSV (default: tab) |
| `typesolve` | no | Auto-cast numeric strings to `int`/`float` |

Access control is configured per query set via the `api` key in `$wgBioDBExpose`
(list of allowed user groups). Omitting `api` uses the value of `$wgBioDBApi`.

## TODO

* Include pagination (e.g., via skip/limit or via localStorage)
* Allow multiple queries at once
* Add exception handling and show errors
* Allow specific connections per set
* Be careful with — if empty statements → only allow 'empty' strings
* Replace CSV download hack in API with a proper MediaWiki response format

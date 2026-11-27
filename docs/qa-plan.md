# QA plan (part of D4)

Due: Thursday, November 12, 2026.
QA plan + QA report together are 3%; no sub-split is published in the outline.

## Scope / requirements / risk coverage

QA covers the existing user, group, channel, and message API; collection controls; JSON/XML/YAML representations; authentication and authorization where implemented; moderation composition; database integrity; provider failures; structured errors; and browser/HTTP consumption.

Highest-risk areas are upstream availability, provider quotas, malformed provider responses, message persistence, channel adult gating, and verifying database state in addition to HTTP status.

## Environment, versions and synthetic fixture

- Candidate: commit `07bfafd359cecd13ad8ba09364dae4adaaecf05e` on the `checlist-item3` branch.
- Runtime: PHP 8.5.13 CLI, PHPUnit 9.6.39.
- Database: MySQL/MariaDB `cliques_db`, UTF-8 multibyte, with `PDO_MYSQL`.
- Web: WAMP/Apache or `composer start`.
- External provider: API Ninjas Profanity Filter.
- Secrets: `API_NINJA_API_KEY` in an untracked `.env`; never include the key in evidence.
- Synthetic data: `docs/cliques_dummy_values.sql`.

Baseline database state is zero rows across users, groups, channels, memberships, and messages.

## Target release and reset/reproduction commands

Before QA, record `git rev-parse HEAD`, PHP/PHPUnit versions, database row counts, and the test date.

Reset the database fixture:

```sql
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE messages;
TRUNCATE TABLE memberships;
TRUNCATE TABLE channels;
TRUNCATE TABLE groups;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;
```

Load positive fixture data:

```bash
mysql -u root -p cliques_db < docs/cliques_dummy_values.sql
```

Return to the empty fixture after positive collection/moderation setup before mutation or provider-failure testing.

Run automated checks:

```powershell
php -d error_reporting=8191 vendor/bin/phpunit --testdox
```

Run the PHP server:

```powershell
composer start
```

## Roles, owners and execution procedure

- Tester: named in each report entry.
- Reviewer/peer: to be named during class peer QA.
- Implementation owner: Alan/teammate responsible for fixes and test-double boundaries.
- Procedure: reset fixture, execute the case, capture HTTP status/body and database state, classify pass/fail/not run, record evidence, and retest any linked defect.

## Unit tests

- Normalized moderation result values.
- Missing API key fails closed with HTTP 503 in the provider adapter.
- Clean, profane, adult-gated, and oversized message behavior.
- Provider 429, 502, and 503 exceptions do not persist messages.

## Database and HTTP integration tests

- POST/GET/PUT/DELETE resource workflows.
- Verify message, channel, and moderation rows against HTTP responses.
- Verify foreign-key/duplicate behavior and zero-row mutation failures.
- Verify rejected mutations leave database state unchanged.

## Representation and contract tests

- Collection envelopes contain `items`, `page`, and `links`.
- JSON success/error contracts match OpenAPI.
- Repeat success and provider failures with `Accept: application/json`, `application/xml`, and `application/yaml`.
- Provider fields are translated to the application's moderation contract.

## Authentication and authorization matrix

- Missing credentials: 401 or agreed status.
- Invalid credentials: 401.
- Wrong role: 403.
- Cross-user update/delete: deny.
- Allowed owner operation: succeed.
- Authorization cases must verify no database mutation on denial.

## Collection and concurrency tests

- Pagination with page/limit boundaries.
- Invalid collection parameters return 400.
- Filtering and sorting return only matching rows.
- Duplicate or scarce-resource mutations leave consistent database state.
- Concurrent mutations are out of scope unless the course release includes a concurrency demonstration.

## Upstream failure and consumer tests

- Live success: provider returns clean/profane result.
- Provider unavailable/timeout: no row; 503.
- Quota/rate limit: no row; 429.
- Malformed provider JSON or invalid fields: no row; 502.
- Test doubles must be labeled simulated evidence.
- JavaScript/PHP consumer must display or expose success and provider-failure outcomes without exposing the API key.

## Logging/redaction/error recovery tests

- API keys and passwords do not appear in logs or responses.
- Request ID and status are logged.
- Provider failures return structured problem responses.
- Provider outage leaves message state unchanged.

## Manual usability checks

- POST and GET workflows are usable through Postman/browser.
- Existing client pages continue to load.
- Group/channel/message lists remain readable at empty and populated states.
- Error feedback is visible and not raw provider data.

## Case format

Each case should record: ID, requirement, preconditions, input, action, expected HTTP, expected state, actual result, pass/fail/not run, evidence location, and defect link.

## Defect triage and retest procedure

- Severity: block, major, minor, or cosmetic.
- Record reproduction on the exact SHA and fixture.
- Link an issue or dated defect entry.
- Fix, retest original case, and preserve the original failed evidence.
- Re-run the complete relevant suite if shared code changed.

## Known gaps and rationale

- Peer QA occurs in class and will not be fabricated here.
- Live provider success/quota/timeout evidence remains pending Postman execution.
- XML/YAML manual evidence remains pending final candidate testing.
- Authentication/authorization evidence depends on the agreed teammate implementation.

## References and AI-use disclosure

- `docs/511va-build3-checklist.md`
- `docs/release-candidate.md`
- `docs/Cliques.yaml`
- `docs/cliques_db.sql`
- `docs/cliques_db_sql.txt`
- `docs/cliques_dummy_values.sql`
- AI assistance was used to organize test plans and evidence; test results and manual observations must be independently verified.

© Pravish Sainath 2026

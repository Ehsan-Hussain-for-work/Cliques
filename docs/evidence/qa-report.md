# QA report (part of D4)

Due: Friday, November 27, 2026.

## Exact tested build / SHA / environment / fixture / dates

- Tested build: `07bfafd359cecd13ad8ba09364dae4adaaecf05e`.
- Repository branch: `checlist-item3`.
- Report date: 2026-11-26.
- PHP: 8.5.13 CLI.
- PHPUnit: 9.6.39.
- Database: MySQL/MariaDB `cliques_db`.
- Initial automated evidence was recorded on a later branch/working tree and must not be represented as passing evidence for `07bfafd` until rerun at that exact SHA.
- Fixture: empty database at baseline; dummy fixture used only for positive manual cases where documented.

## Executed scope versus the QA plan

Automated evidence currently exists for user collection shape and moderation unit/action behavior. Manual Postman, live provider, XML/YAML, authorization, database-row, peer, and full candidate execution remain pending or not yet evidenced.

## Results

| Case ID         | Expected                                                               | Actual                                      | Pass/fail/not run                    | Evidence                                                | Defect |
| --------------- | ---------------------------------------------------------------------- | ------------------------------------------- | ------------------------------------ | ------------------------------------------------------- | ------ |
| QA-AUTO-LIST    | GET `/users` returns `items`, `page`, and `links`                      | Automated test passes on the current branch | Pass, candidate verification pending | `tests/Application/Actions/User/ListUserActionTest.php` | None   |
| QA-AUTO-CLEAN   | Clean moderation result is unchanged                                   | Automated test passes on the current branch | Pass, candidate verification pending | `tests/Application/Services/TextModerationTest.php`     | None   |
| QA-AUTO-PROFANE | Profane result stores original and censored forms                      | Automated test passes on the current branch | Pass, candidate verification pending | `tests/Application/Services/TextModerationTest.php`     | None   |
| QA-AUTO-ADULT   | Adult channel bypasses provider                                        | Automated test passes on the current branch | Pass, candidate verification pending | `tests/Application/Services/TextModerationTest.php`     | None   |
| QA-AUTO-429     | Quota failure returns 429 and does not persist                         | Automated test passes on the current branch | Pass, candidate verification pending | `tests/Application/Services/TextModerationTest.php`     | None   |
| QA-AUTO-502     | Malformed provider response returns 502 and does not persist           | Automated test passes on the current branch | Pass, candidate verification pending | `tests/Application/Services/TextModerationTest.php`     | None   |
| QA-AUTO-503     | Provider outage/configuration failure returns 503 and does not persist | Automated test passes on the current branch | Pass, candidate verification pending | `tests/Application/Services/TextModerationTest.php`     | None   |
| QA-MANUAL-LIVE  | Live provider success/failure behavior                                 | Not run/recorded yet                        | Not run                              | Pending Postman export                                  | N/A    |
| QA-MANUAL-DB    | Database rows match HTTP outcomes                                      | Not run/recorded yet                        | Not run                              | Pending SQL capture                                     | N/A    |
| QA-XML-YAML     | JSON/XML/YAML contracts agree                                          | Not run/recorded yet                        | Not run                              | Pending HTTP captures                                   | N/A    |
| QA-AUTH         | Missing/wrong/cross-user authorization cases                           | Implementation/peer evidence pending        | Not run                              | N/A                                                     | N/A    |
| QA-Peer         | Peer executes and reports findings                                     | No peer sign-off recorded                   | Not run                              | Pending class session                                   | N/A    |

## Defects, corrective actions and retests

| Defect | Reproduction                                     | Impact                                                      | Fix SHA                                               | Retest case/result                              | Remaining risk                            |
| ------ | ------------------------------------------------ | ----------------------------------------------------------- | ----------------------------------------------------- | ----------------------------------------------- | ----------------------------------------- |
| QA-001 | PHPUnit initially could not load `mbstring`      | Automated tests could not start                             | Local PHP INI; no repository fix SHA                  | phpunit now executes                            | PHP environment must reproduce `mbstring` |
| QA-002 | Stale Slim/user tests referenced removed classes | Full suite could not produce trustworthy results            | Test cleanup on personal branch; no candidate SHA yet | 10 tests, 36 assertions on branch working state | Recheck exact candidate SHA               |
| QA-003 | Manual POST lacked parsed JSON                   | HTTP 400 evidence; not a moderation success/failure finding | N/A                                                   | Not rerun                                       | User must send valid JSON body            |

## Input/output validation across formats

Automated tests currently validate PHP objects and JSON action payloads. JSON/XML/YAML network negotiation is not complete in this report. No unrun format result is converted to pass.

## Security, state integrity and failure recovery findings

- Provider key must remain outside source control and JavaScript.
- Provider/transport failure must not persist a message.
- Adult-gated channels bypass moderation in the implemented unit/action policy.
- Database-row verification remains pending for Postman and relational integration testing.
- Authorization findings remain pending teammate implementation/evidence.

## Peer QA feedback

Not yet available. Peer reviewer, date, findings, and sign-off must be added after class peer QA.

## Unexecuted cases and limits

- Live API Ninjas success/failure calls.
- Provider timeout/quota behavior against the real service.
- XML and YAML manual responses.
- Authentication/authorization matrix.
- Final database row and rollback checks.
- Peer QA sign-off.
- Exact candidate rerun at `07bfafd359cecd13ad8ba09364dae4adaaecf05e`.

## Reasoned readiness assessment

Automated moderation unit/action coverage and stale-test cleanup provide useful evidence, but this report does not justify a complete-ready claim because the candidate SHA and manual/peer evidence are incomplete.

## References / licences / AI-use disclosure

- `docs/511va-build3-checklist.md`
- `docs/qa-plan.md`
- `docs/release-candidate.md`
- `docs/Cliques.yaml`
- `docs/evidence/validation.md`
- API Ninjas terms and provider response behavior must be reviewed by the team.
- AI assistance was used to organize tests and reports; every result must be verified against the exact candidate and database state.

Do not convert an unrun case into a pass or erase the original failed result.

© Pravish Sainath 2026

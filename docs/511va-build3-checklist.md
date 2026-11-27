# Build 3 — recommended evidence checklist

Due: 2026-11-19 (Week 13).
Implementation: 23% shared across all three builds; no per-build split published.
This checklist is recommended organization, not a new grading rule or submission.

## Complete integration, security and a QA-ready candidate

Security labs occur in Week 11. The QA plan is due November 12, and November 19 is a QA session. Finish the remaining representation, composition and robustness work alongside security; do not replace the earlier implementation with a disconnected prototype.

## Evidence checklist

- [ ] Complete the planned scope: Finish all agreed operations, collection controls, middleware, representations and client workflows, including explicitly tracked Build 2 remainders. Keep the original GET and pages working. Resolve placeholders and report any unmet requirement honestly.

- [ ] Authentication and authorization: Implement the agreed identity mechanism, protected operations, roles and object-level permissions. Test missing/invalid credentials, wrong roles and access to another user’s resource. Store passwords only with appropriate password hashing when applicable.

- [ ] Full data-exchange contract: Complete the agreed JSON/XML/YAML input/output and negotiation matrix, validation, statuses, structured errors and safe logging. Verify actual messages against OpenAPI rather than marking unimplemented formats complete.

- [ ] Integrity and recovery: Check duplicates, relevant scarce-resource/concurrent mutations, transactions, rollback and retry behavior. Verify database rows as well as HTTP responses on the actual relational stack.

- [ ] Reliable composition and consumers: Complete the composite services and practical pages/clients. Show meaningful success, timeout, malformed-upstream and quota/failure handling; clearly label fixtures and test doubles.

- [ ] QA-ready evidence: Execute the QA plan, preserve manual/automated evidence, link defects and prepare peer testing. Further corrections and retests belong in the November 27 QA report.

- [ ] Reproducible release candidate: Identify the exact commit; document installation, configuration, schema/migrations, seed, run and test commands. Prepare deployment readiness and finish final deployment validation/documentation for delivery.

## Suggested demonstration

1. Recreate the candidate from the documented commit and synthetic database.
2. Show a complete authorized workflow, then a denied cross-user operation.
3. Demonstrate collection controls, the full agreed representation matrix and the composite service.
4. Exercise a rejected mutation or upstream failure and verify the resulting state and safe logs.
5. Run the QA tests and record peer findings against the submitted snapshot.

## Scope boundary

Build 3 completes the planned service but does not end corrective work. Use the following week for fixes, retests and the QA report, demonstrate on December 2–4, and submit the final report and user guide on December 10. Front-end polish must not displace functional correctness.

Record repository URL, exact SHA, actual reviewer and test evidence in the build manifest.

© Pravish Sainath 2026

# Cliques

### Description
A web service meant to create chat groups, assign roles, log chat messages, and integrate the chats into other media. Supporting filtering for users, for user-defined groups, and for specific messages. Allowing input operations to create groups, to send messages and to delete messages. Designed for tech-savvy users and/or private groups seeking a flexible messaging API. Solving the problem of having to download an application to access chat messages, because the API allows developers to integrate a seamless means of data transer.

### Team members
- Ehsan Hussain
- Alan Telemishev

### Tech Stack
- phpMyAdmin
- php
- GitHub
- intellij idea

### API Documentation
[Read our YAML documentation here](https://github.com/Ehsan-Hussain-for-work/Cliques/blob/main/docs/Cliques.yaml)

### Response representation

Default representation for all endpoints is `application/json`.
Unsupported `Accept` values return `406 Not Acceptable` with a JSON error body.
Error responses (`4xx`, `5xx`) are always `application/problem+json`, regardless of `Accept`.
Request bodies are `application/json` on all endpoints. XML/YAML request
bodies are **not** supported; see Known gaps.
Evidence: `docs/evidence/negotiation/` (curl outputs for JSON, XML, YAML,
and `406` cases against both `/users` endpoints).

#### Known gaps (deferred, not implemented)
- XML and YAML **request** bodies — response-only for now.
- `/groups`, `/channels`, `/messages` return bare arrays; `/users` uses
  `{items, page, links}`. Envelope unification deferred.
- HATEOAS `links` present on `/users` only.
- `creation_date` is a MySQL `DATETIME` string, not RFC 3339.
- camelCase / snake_case naming is mixed across resources.
  
### Validation

- Requests must use Content-Type: application/json. Other types return 400.

### Error Responses
Regardless of the `Accept` header (even if `Accept: application/xml` is sent), all `4xx` and `5xx` error responses are returned as `application/problem+json`. 

The error payload uses an RFC 7807 compliant schema. cf our `ErrorResponse` OpenAPI schema:
{
    "type": "about:blank",
    "title": "",
    "status": 406,
    "detail": "No acceptable success format.",
    "instance": "/cliques-lia/channels",
    "requestId": "15734abdaecd2c2c8b28c52bdca9646c"
}

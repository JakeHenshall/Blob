# Overview

## What the app does

ClientHub is a simple internal tool for tracking work for clients. It models
three core things:

- **Clients** — the companies/people you do work for
- **Projects** — pieces of work belonging to a client
- **Tasks** — the specific things to do inside a project

Each user has their own set of clients. Projects and tasks live under those
clients, so one user can't see or edit another user's data.

The UI is a small multi-page Blade app with a dashboard, list views with
search/filter + pagination, and clean create/edit forms.

## Who it is for

This repo is a reference build for a **junior Laravel developer**. The goal is
to demonstrate that the author can:

- use Laravel the way the docs recommend,
- build working CRUD around related models,
- validate input using Form Requests,
- wire up auth with Breeze,
- write basic feature tests,
- and explain the choices they made.

It is intentionally not an attempt to look senior — there are no policies,
action classes, queues, services, or API layer. Those belong in the mid/senior
versions.

## Why this app as a learning project

A client/project/task tracker is a great learning project because it:

1. Has three related models with `hasMany` / `belongsTo` relationships.
2. Requires real validation (dates, enums, ownership).
3. Has natural read/list/filter/detail/write/update/delete flows — full CRUD.
4. Needs authentication and per-user data scoping.
5. Is small enough to stay simple, but real enough to feel production-shaped.

In other words, it touches almost every part of the framework a junior needs
to be comfortable with.

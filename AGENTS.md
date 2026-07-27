# Regagro — Agentic Development Workflow

This repository is a **demo stand** for an agent-driven development workflow. Work
flows through numbered stages, each living in its own folder. An artifact only
advances to the next stage once the previous one is complete.

## Workflow stages

```
0-vibes/ → 1-business-tasks/ → 2-tasks/ → 3-specs/ → 4-design/ → 5-tasks/
 idea/prd    observe & plan      backlog     specs      design    implementation
                                                                      │
   ┌──────────────────────────────────────────────────────────────────┘
   ▼
6-results/ → 7-eval/ → 8-security-check/ → 9-deploy/ → 10-observation/
  built       quality      security         release      watch → (loops back to 1)
```

The pipeline is a **loop**: `10-observation/` feeds signals back into
`1-business-tasks/observation/` to start the next cycle.

### 0. Vibes — `0-vibes/`
The earliest, pre-planning stage: raw ideas, inspiration, and loose direction —
the **vibe**, before anything is structured. Contains:

- `0-vibes/prd/` — Product Requirements Documents: the first structured
  artifact that turns a vibe into stated product intent, ready to hand off to
  business tasks.

### 1. Business tasks source — `1-business-tasks/`
Where work originates: **observation and planning**. Raw business needs,
research notes, stakeholder input, opportunities, and high-level plans. This is
the "why" — problems worth solving, before they are broken down into concrete
work. Split into two halves:

- `1-business-tasks/observation/` — signals from the running system, users, and
  ops, triaged by severity: `errors/`, `warnings/`, `infos/`.
- `1-business-tasks/planning/` — high-level plans, roadmaps, and priorities
  shaped from what observation surfaces.

### 2. Tasks to do — `2-tasks/`
The actionable **backlog**. Business tasks from stage 1 are decomposed here into
discrete, deliverable tasks with clear acceptance criteria. This is the "what" —
scoped units of work ready to be picked up.

### 3. Specs — `3-specs/`
Detailed **specifications** for tasks. Each spec expands a task into concrete
requirements: behavior, data, edge cases, API contracts, and acceptance tests.
This is the "how it should work" — the source of truth an implementation is
validated against. Organized by concern:

- `3-specs/actors/` — who/what interacts with the system (roles, personas,
  external systems).
- `3-specs/entities/` — domain objects and their data (fields, relationships,
  invariants).
- `3-specs/events/` — things that happen (domain events, triggers, state
  transitions, side effects).
- `3-specs/modules/` — functional units that compose actors, entities, and
  events into behavior.

### 4. Design — `4-design/`
**Design components** produced from specs. Organized by target:

- `4-design/figma/` — Figma source of truth. The linked Figma file (see
  `4-design/figma/CLAUDE.md`) is the design origin; components are exported /
  synced from here.
- `4-design/react/` — React component implementations.
- `4-design/vue/` — Vue component implementations.

**Rule: every component must have a Storybook story.** Each component in
`react/` and `vue/` ships with its own Storybook (`*.stories.*`) so it can be
viewed, tested, and reviewed in isolation.

### 5. Tasks — `5-tasks/`
Concrete **implementation/build tasks** derived from the specs and designs. This
is where validated design becomes engineering work ready to be built. Each task
links back to its spec (`3-specs/…`) and/or design (`4-design/…`).

### 6. Results — `6-results/`
The **output of implementation**: what was actually built. Delivered artifacts,
code, and outcomes, each traceable back to the task that produced it.

### 7. Eval — `7-eval/`
**Evaluation** of results against the specs — the quality gate. Test results,
acceptance-criteria checks, and pass/fail verdicts.

### 8. Security check — `8-security-check/`
**Security review** before shipping — the security gate. Vulnerabilities,
secrets, dependency risks, and access concerns caught here, not in production.

### 9. Deploy — `9-deploy/`
**Release** of the security-cleared results to their target environment.
Deployment configs, release notes, rollout/rollback plans.

### 10. Observation — `10-observation/`
**Watching the deployed system** in production, triaged by severity (`errors/`,
`warnings/`, `infos/`). Closes the loop: signals feed back into
`1-business-tasks/observation/` to start the next cycle.

## Conventions

- Stage folders are numbered to reflect the direction of flow.
- Do not skip stages — a spec references a task, a design references a spec.
- Keep each artifact traceable to its upstream source (link back by id/name).
